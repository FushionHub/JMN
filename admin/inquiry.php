<?php
declare(strict_types=1);

/**
 * Admin Inquiry Detail — view a submission, change status, reply by email.
 *
 * Reply flow: the admin composes a message, the server wraps it in the
 * site-branded HTML template, sends it with mail() to the inquiry's address,
 * then records the reply on the row (status=replied, replied_at, reply_message,
 * replied_by). Delivery failures are logged, never surfaced as data loss —
 * the row is updated either way so the audit trail stays honest.
 */

require_once __DIR__ . '/includes/auth.php';
admin_require_auth();
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/email_templates.php';

$adminUser = admin_current_user();
$db = get_db();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0 || $db === null) {
    $_SESSION['flash_err'] = 'Inquiry not found.';
    header('Location: inquiries.php');
    exit;
}

// ---------------------------------------------------------------------------
// Handle POST actions (status change / reply)
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !verify_csrf_token($token)) {
        $_SESSION['flash_err'] = 'Your security token has expired. Please try again.';
        header('Location: inquiry.php?id=' . $id);
        exit;
    }
    if (!check_rate_limit('admin_inquiry', 2)) {
        $_SESSION['flash_err'] = 'Please wait a moment before acting again.';
        header('Location: inquiry.php?id=' . $id);
        exit;
    }

    $action = is_string($_POST['action'] ?? '') ? (string)$_POST['action'] : '';

    try {
        $stmt = $db->prepare('SELECT * FROM `contact_inquiries` WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $inquiry = $stmt->fetch();
    } catch (Throwable $e) {
        error_log('Inquiry load failed: ' . $e->getMessage());
        $inquiry = false;
    }

    if (!is_array($inquiry)) {
        $_SESSION['flash_err'] = 'Inquiry not found.';
        header('Location: inquiries.php');
        exit;
    }

    if ($action === 'status') {
        $newStatus = is_string($_POST['status'] ?? '') ? (string)$_POST['status'] : '';
        if (in_array($newStatus, ['new', 'read', 'replied', 'archived'], true)) {
            $db->prepare('UPDATE `contact_inquiries` SET status = ? WHERE id = ?')->execute([$newStatus, $id]);
            $_SESSION['flash_ok'] = 'Status updated to "' . $newStatus . '".';
        } else {
            $_SESSION['flash_err'] = 'Invalid status value.';
        }
        rotate_csrf_token();
        header('Location: inquiry.php?id=' . $id);
        exit;
    }

    if ($action === 'reply') {
        $replyBody = is_string($_POST['reply_message'] ?? '') ? trim((string)$_POST['reply_message']) : '';
        if (mb_strlen($replyBody) < 10) {
            $_SESSION['flash_err'] = 'Please write a reply of at least 10 characters.';
            rotate_csrf_token();
            header('Location: inquiry.php?id=' . $id);
            exit;
        }
        if (mb_strlen($replyBody) > 20000) {
            $_SESSION['flash_err'] = 'The reply is too long (max 20,000 characters).';
            rotate_csrf_token();
            header('Location: inquiry.php?id=' . $id);
            exit;
        }

        $toEmail = sanitize_email((string)$inquiry['email']);
        if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_err'] = 'This inquiry has a malformed email address; the reply could not be sent.';
            rotate_csrf_token();
            header('Location: inquiry.php?id=' . $id);
            exit;
        }

        // Build the branded reply email.
        $replyHtml = jmn_build_reply_email([
            'to_name'    => (string)$inquiry['name'],
            'subject'    => (string)($inquiry['service'] ?? 'your inquiry'),
            'body'       => $replyBody,
            'inquiry_id' => $id,
        ]);

        $fromDomain  = preg_replace('/[^A-Za-z0-9.\-]/', '', (string)SITE_DOMAIN) ?: 'jmn.morexweb.com';
        $fromAddress = 'no-reply@' . $fromDomain;
        $brandName   = sanitize_header_value(APP_NAME);
        $senderName  = sanitize_header_value($adminUser['display_name'] ?? PROFILE_NAME);
        $senderEmail = sanitize_email((string)($adminUser['email'] ?? PROFILE_CORP_EMAIL));
        if ($senderEmail === '') { $senderEmail = PROFILE_CORP_EMAIL; }

        $subject = sanitize_header_value('Re: ' . ($inquiry['service'] ?? 'your inquiry') . ' — ' . PROFILE_NAME);

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$senderName} <{$fromAddress}>\r\n";
        $headers .= "Reply-To: {$senderName} <{$senderEmail}>\r\n";
        $headers .= "X-Inquiry-ID: {$id}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        $sent = @mail($toEmail, $subject, $replyHtml, $headers);
        if (!$sent) {
            error_log("admin/inquiry.php: reply mail() failed for {$toEmail} (inquiry #{$id})");
        }

        // Record the reply regardless of transport success so the audit trail
        // reflects what the admin actually did; surface delivery status.
        $replyBy = (string)($adminUser['username'] ?? 'admin');
        $stmt = $db->prepare("
            UPDATE `contact_inquiries`
            SET `status` = 'replied', `replied_at` = NOW(),
                `reply_message` = :rm, `replied_by` = :rb
            WHERE `id` = :id
        ");
        $stmt->execute([':rm' => $replyBody, ':rb' => mb_substr($replyBy, 0, 100), ':id' => $id]);

        // Disk audit trail.
        $logDir = ROOT_PATH . '/storage/logs';
        @file_put_contents(
            $logDir . '/inquiries.log',
            sprintf("[%s] ADMIN-REPLY #%d | to=%s | by=%s | delivered=%s\n", date('c'), $id, $toEmail, $replyBy, $sent ? 'yes' : 'no'),
            FILE_APPEND | LOCK_EX
        );

        $_SESSION['flash_ok'] = $sent
            ? 'Reply sent to ' . $toEmail . ' and recorded on the inquiry.'
            : 'Reply recorded on the inquiry, but the mail transport reported a failure (check storage/logs/php-error.log). The message is saved here for manual follow-up.';
        rotate_csrf_token();
        header('Location: inquiry.php?id=' . $id);
        exit;
    }

    header('Location: inquiry.php?id=' . $id);
    exit;
}

// ---------------------------------------------------------------------------
// Load the inquiry for display
// ---------------------------------------------------------------------------
try {
    $stmt = $db->prepare('SELECT * FROM `contact_inquiries` WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $inquiry = $stmt->fetch();
} catch (Throwable $e) {
    error_log('Inquiry load failed: ' . $e->getMessage());
    $inquiry = false;
}

if (!is_array($inquiry)) {
    $_SESSION['flash_err'] = 'Inquiry not found.';
    header('Location: inquiries.php');
    exit;
}

// Auto-mark unread as read on first open (but never override replied).
if ($inquiry['status'] === 'new') {
    try {
        $db->prepare("UPDATE `contact_inquiries` SET status = 'read' WHERE id = ? AND status = 'new'")->execute([$id]);
        $inquiry['status'] = 'read';
    } catch (Throwable $e) {
        error_log('Auto-read update failed: ' . $e->getMessage());
    }
}

$csrf = generate_csrf_token();

// Suggested reply template.
$suggested = "Hello " . $inquiry['name'] . ",\n\n"
    . "Thank you for reaching out regarding \"" . ($inquiry['service'] ?? 'your project') . "\".\n\n"
    . "I have reviewed your brief and would be happy to discuss the next steps. "
    . "Could we schedule a brief call at your convenience?\n\n"
    . "Best regards,\n" . PROFILE_NAME . "\n" . PROFILE_TITLE;

$adminTitle = 'Inquiry #' . $id;
$adminNav   = 'inquiries';
require_once __DIR__ . '/includes/layout-header.php';
?>

<div class="admin-filters">
    <a class="admin-chip" href="inquiries.php"><i class="ri-arrow-left-s-line"></i> Back to inquiries</a>
    <span class="admin-chip is-active">Inquiry #<?= $id ?></span>
</div>

<div class="admin-detail-grid">
    <!-- Left: message + reply composer -->
    <div>
        <div class="admin-card">
            <div class="admin-card-head">
                <h2>Message from <?= e($inquiry['name']) ?></h2>
                <span class="admin-badge admin-badge--<?= e($inquiry['inquiry_type'] ?? 'contact') ?>"><?= e($inquiry['inquiry_type'] ?? 'contact') ?></span>
            </div>
            <div class="admin-card-body">
                <div class="admin-message-block"><?= e($inquiry['message']) ?></div>

                <?php if (!empty($inquiry['reply_message'])): ?>
                    <div class="admin-reply-block">
                        <strong><i class="ri-mail-send-line"></i> Your reply<?= $inquiry['replied_by'] ? ' — ' . e($inquiry['replied_by']) : '' ?><?= $inquiry['replied_at'] ? ' (' . e(date('M d, Y H:i', strtotime((string)$inquiry['replied_at']))) . ')' : '' ?></strong>
                        <?= e($inquiry['reply_message']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reply composer -->
        <div class="admin-card">
            <div class="admin-card-head">
                <h2><i class="ri-mail-send-line"></i> Reply by email</h2>
            </div>
            <div class="admin-card-body">
                <form method="post" action="inquiry.php" id="replyForm">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="action" value="reply">

                    <div class="admin-field">
                        <label for="reply_message">Reply message</label>
                        <textarea class="admin-textarea" id="reply_message" name="reply_message" rows="10" required><?= e($suggested) ?></textarea>
                        <div class="admin-hint">
                            Sent to <strong><?= e($inquiry['email']) ?></strong> via the site mailer.
                            The message is also saved on this inquiry for the audit trail.
                        </div>
                    </div>

                    <div class="admin-form-actions">
                        <button class="admin-btn admin-btn--primary" type="submit">
                            <i class="ri-send-plane-fill"></i> Send reply
                        </button>
                        <a class="admin-btn admin-btn--secondary" href="mailto:<?= e($inquiry['email']) ?>?subject=<?= e(rawurlencode('Re: ' . ($inquiry['service'] ?? 'your inquiry'))) ?>">
                            <i class="ri-mail-line"></i> Open in mail client
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right: metadata + status actions -->
    <div>
        <div class="admin-card">
            <div class="admin-card-head"><h2>Details</h2></div>
            <div class="admin-card-body">
                <ul class="admin-meta-list">
                    <li><span class="k">Name</span><span class="v"><?= e($inquiry['name']) ?></span></li>
                    <li><span class="k">Email</span><span class="v"><a href="mailto:<?= e($inquiry['email']) ?>"><?= e($inquiry['email']) ?></a></span></li>
                    <?php if (!empty($inquiry['phone'])): ?>
                        <li><span class="k">Phone</span><span class="v"><?= e($inquiry['phone']) ?></span></li>
                    <?php endif; ?>
                    <?php if (!empty($inquiry['company'])): ?>
                        <li><span class="k">Company</span><span class="v"><?= e($inquiry['company']) ?></span></li>
                    <?php endif; ?>
                    <li><span class="k">Type</span><span class="v"><?= e($inquiry['inquiry_type'] ?? 'contact') ?></span></li>
                    <li><span class="k">Scope</span><span class="v"><?= e($inquiry['service'] ?? '—') ?></span></li>
                    <li><span class="k">Budget</span><span class="v"><?= e($inquiry['budget'] ?? '—') ?></span></li>
                    <?php if (!empty($inquiry['reserved_at'])): ?>
                        <li><span class="k">Slot</span><span class="v"><?= e(date('M d, Y H:i', strtotime((string)$inquiry['reserved_at']))) ?></span></li>
                        <li><span class="k">Timezone</span><span class="v"><?= e($inquiry['reserved_zone'] ?? 'Africa/Lagos') ?></span></li>
                    <?php endif; ?>
                    <li><span class="k">Received</span><span class="v"><?= e(date('M d, Y H:i', strtotime((string)$inquiry['created_at']))) ?></span></li>
                    <?php if (!empty($inquiry['ip_address'])): ?>
                        <li><span class="k">IP</span><span class="v"><?= e($inquiry['ip_address']) ?></span></li>
                    <?php endif; ?>
                    <?php if (!empty($inquiry['replied_at'])): ?>
                        <li><span class="k">Replied</span><span class="v"><?= e(date('M d, Y H:i', strtotime((string)$inquiry['replied_at']))) ?></span></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Status actions -->
        <div class="admin-card">
            <div class="admin-card-head"><h2>Status</h2></div>
            <div class="admin-card-body">
                <form method="post" action="inquiry.php">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="action" value="status">
                    <div class="admin-field">
                        <select class="admin-select" name="status">
                            <?php foreach (['new', 'read', 'replied', 'archived'] as $s): ?>
                                <option value="<?= $s ?>" <?= $inquiry['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="admin-form-actions">
                        <button class="admin-btn admin-btn--secondary" type="submit"><i class="ri-save-line"></i> Update status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>
