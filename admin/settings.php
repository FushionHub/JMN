<?php
declare(strict_types=1);

/**
 * Admin Settings — change the admin password.
 */

require_once __DIR__ . '/includes/auth.php';
admin_require_auth();

$adminUser = admin_current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !verify_csrf_token($token)) {
        $_SESSION['flash_err'] = 'Your security token has expired. Please try again.';
        header('Location: settings.php');
        exit;
    }
    if (!check_rate_limit('admin_settings', 3)) {
        $_SESSION['flash_err'] = 'Please wait a moment before trying again.';
        header('Location: settings.php');
        exit;
    }

    $current = is_string($_POST['current_password'] ?? '') ? (string)$_POST['current_password'] : '';
    $new     = is_string($_POST['new_password'] ?? '') ? (string)$_POST['new_password'] : '';
    $confirm = is_string($_POST['confirm_password'] ?? '') ? (string)$_POST['confirm_password'] : '';

    if ($new !== $confirm) {
        $_SESSION['flash_err'] = 'The new passwords do not match.';
    } else {
        $result = admin_change_password((int)$adminUser['id'], $current, $new);
        if ($result['ok']) {
            $_SESSION['flash_ok'] = 'Password updated.';
            rotate_csrf_token();
            header('Location: settings.php');
            exit;
        }
        $_SESSION['flash_err'] = $result['error'];
    }
    rotate_csrf_token();
    header('Location: settings.php');
    exit;
}

$csrf = generate_csrf_token();
$adminTitle = 'Settings';
$adminNav   = 'settings';
require_once __DIR__ . '/includes/layout-header.php';
?>

<div class="admin-detail-grid">
    <div class="admin-card">
        <div class="admin-card-head"><h2>Change password</h2></div>
        <div class="admin-card-body">
            <form method="post" action="settings.php">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

                <div class="admin-field">
                    <label for="current_password">Current password</label>
                    <input class="admin-input" type="password" id="current_password" name="current_password" required autocomplete="current-password">
                </div>

                <div class="admin-grid-2">
                    <div class="admin-field">
                        <label for="new_password">New password</label>
                        <input class="admin-input" type="password" id="new_password" name="new_password" required minlength="10" autocomplete="new-password">
                        <div class="admin-hint">At least 10 characters.</div>
                    </div>
                    <div class="admin-field">
                        <label for="confirm_password">Confirm new password</label>
                        <input class="admin-input" type="password" id="confirm_password" name="confirm_password" required minlength="10" autocomplete="new-password">
                    </div>
                </div>

                <div class="admin-form-actions">
                    <button class="admin-btn admin-btn--primary" type="submit">
                        <i class="ri-lock-password-line"></i> Update password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-head"><h2>Account</h2></div>
        <div class="admin-card-body">
            <ul class="admin-meta-list">
                <li><span class="k">Username</span><span class="v"><?= e($adminUser['username'] ?? '') ?></span></li>
                <li><span class="k">Display name</span><span class="v"><?= e($adminUser['display_name'] ?? '') ?></span></li>
                <li><span class="k">Email</span><span class="v"><?= e($adminUser['email'] ?? '') ?></span></li>
                <li><span class="k">Role</span><span class="v"><?= e($adminUser['role'] ?? '') ?></span></li>
                <li><span class="k">Last login</span><span class="v"><?= !empty($adminUser['last_login_at']) ? e(date('M d, Y H:i', strtotime((string)$adminUser['last_login_at']))) : '—' ?></span></li>
            </ul>
            <div class="admin-hint" style="margin-top:14px;">
                Credentials are provisioned from the <code>ADMIN_USERNAME</code> /
                <code>ADMIN_PASSWORD</code> environment variables on first run.
                Changing the password here updates the database hash only; the
                environment variables are not touched.
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>
