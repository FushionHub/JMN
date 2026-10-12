<?php
declare(strict_types=1);

/**
 * Secure Contact / Reservation Mailer Processor
 * Jamachi Maurice Mauricennadi Portfolio Platform
 *
 * Pipeline:
 *   1  Method guard ......................... POST only
 *   2  Honeypot ............................. silent accept, discard
 *   3  CSRF .................................. session token, hash_equals
 *   4  Rate limiting ......................... session + per-IP file buckets
 *   5  Input coercion ......................... arrays/scalars never reach trim()
 *   6  Validation / normalisation ............ including reservation slots
 *   7  MySQL insert ......................... prepared statements only
 *   8  Disk audit log ........................ storage/logs/inquiries.log
 *   9  Dual dispatch ......................... owner inbox + sender ack
 *  10  CSRF rotation + response .............. JSON or safe internal redirect
 *
 * Header-injection defence: every value that lands in a mail header passes
 * through sanitize_header_value(), which strips CR/LF and all control bytes.
 * The envelope From address is derived from SITE_DOMAIN, never from the
 * attacker-controlled Host header.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/email_templates.php';

// ---------------------------------------------------------------------------
// Helpers local to this endpoint
// ---------------------------------------------------------------------------

/** Coerce any request value to a trimmed scalar string. */
function jmn_str(mixed $value, string $fallback = ''): string
{
    if (is_array($value) || is_object($value)) {
        return $fallback;
    }
    if ($value === null || $value === false) {
        return $fallback;
    }
    return (string)$value;
}

/** Accept only a same-site relative path as a post-submit redirect target. */
function jmn_safe_return(?string $candidate, string $fallback): string
{
    if ($candidate === null) {
        return $fallback;
    }
    $candidate = str_replace(["\r", "\n", "\t", "\0"], '', $candidate);
    if ($candidate === '') {
        return $fallback;
    }
    if (preg_match('#^(https?:)?//#i', $candidate) === 1) {
        return $fallback;
    }
    if (str_contains($candidate, '..') || str_contains($candidate, '\\')) {
        return $fallback;
    }
    return $candidate;
}

/** Attach a contact_status flag to an internal URL (keeping its hash). */
function jmn_with_status(string $url, string $status): string
{
    $hash = '';
    if (str_contains($url, '#')) {
        [$url, $hashPart] = explode('#', $url, 2);
        $hash = '#' . $hashPart;
    }
    $url = preg_replace('/([?&])contact_status=[^&]*/', '$1', $url) ?? $url;
    $url = rtrim($url, '?&');
    $sep = str_contains($url, '?') ? '&' : '?';
    return $url . $sep . 'contact_status=' . rawurlencode($status) . $hash;
}

/** Emit a JSON or redirect response and stop. */
function jmn_respond(bool $isAjax, array $payload, int $status, string $redirect = ''): void
{
    if ($isAjax) {
        json_response($payload, $status);
    }
    if ($status >= 400 && $redirect === '') {
        header('Content-Type: text/plain; charset=UTF-8');
        http_response_code($status);
        echo (string)($payload['message'] ?? 'Request failed.');
        exit;
    }
    if (!headers_sent()) {
        http_response_code($status);
        header('Location: ' . $redirect);
    }
    exit;
}

// ---------------------------------------------------------------------------
// 1. Method guard
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    if (str_contains((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')) {
        json_response(['success' => false, 'message' => 'Method Not Allowed'], 405);
    }
    echo '405 Method Not Allowed';
    exit;
}

// Detect AJAX / JSON client
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['CONTENT_TYPE']) && str_contains((string)$_SERVER['CONTENT_TYPE'], 'application/json'))
    || (isset($_SERVER['HTTP_ACCEPT']) && str_contains((string)$_SERVER['HTTP_ACCEPT'], 'application/json'));

// Read payload (JSON body or classic form POST)
$rawInput = (string)file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);
$data     = is_array($jsonData) ? $jsonData : $_POST;
if (!is_array($data)) {
    $data = [];
}

$returnCandidate = jmn_str($data['return_to'] ?? '');
if ($returnCandidate === '') {
    $returnCandidate = jmn_str($_SERVER['HTTP_REFERER'] ?? '');
}
$returnTo     = jmn_safe_return($returnCandidate, url('index.php?contact_status=success#contact'));
$failRedirect = jmn_with_status(jmn_safe_return($returnCandidate, url('index.php?#contact')), 'validation_error');

// ---------------------------------------------------------------------------
// 2. Honeypot - bots that fill the hidden field get a fake success
// ---------------------------------------------------------------------------
$honeypot = jmn_str($data['website_hp'] ?? $data['website_url_check'] ?? '');
if ($honeypot !== '') {
    jmn_respond($isAjax, [
        'success' => true,
        'message' => 'Thank you! Your message has been received.',
    ], 200, $returnTo);
}

// ---------------------------------------------------------------------------
// 3. CSRF token validation
// ---------------------------------------------------------------------------
$submittedToken = $data['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!is_string($submittedToken) || !verify_csrf_token($submittedToken)) {
    jmn_respond($isAjax, [
        'success' => false,
        'message' => 'Your security token has expired. Please refresh the page and submit again.',
    ], 419, '');
}

// ---------------------------------------------------------------------------
// 4. Rate limiting - session bucket + shared per-IP bucket
// ---------------------------------------------------------------------------
if (!check_rate_limit('contact_form', 5)) {
    jmn_respond($isAjax, [
        'success' => false,
        'message' => 'Submission rate limit reached. Please wait a few seconds before trying again.',
    ], 429, '');
}
if (!check_ip_rate_limit('contact_form', 6, 600)) {
    jmn_respond($isAjax, [
        'success' => false,
        'message' => 'Too many submissions from your network. Please try again in a few minutes.',
    ], 429, '');
}

// ---------------------------------------------------------------------------
// 5. Coerce + sanitise inputs
// ---------------------------------------------------------------------------
$name    = sanitize_text(jmn_str($data['Name'] ?? $data['name'] ?? ''));
$email   = sanitize_email(jmn_str($data['Email'] ?? $data['email'] ?? ''));
$phone   = sanitize_text(jmn_str($data['phone'] ?? $data['Phone'] ?? ''));
$company = sanitize_text(jmn_str($data['company'] ?? $data['Company'] ?? ''));

// The engagement select carries the real scope; the hidden `service` field is
// the fallback used by the blog / case-study inline forms.
$service = sanitize_text(jmn_str($data['offer_method'] ?? ''));
if ($service === '') {
    $service = sanitize_text(jmn_str($data['service'] ?? ''));
}
if ($service === '') {
    $service = 'General Architectural Consultation';
}

$budget  = sanitize_text(jmn_str($data['budget'] ?? $data['text'] ?? ''));
if ($budget === '') {
    $budget = 'Flexible / Project Dependent';
}
$message = sanitize_text(jmn_str($data['your-comment'] ?? $data['message'] ?? ''));

$inquiryType = sanitize_text(jmn_str($data['inquiry_type'] ?? 'contact'));
if (!in_array($inquiryType, ['contact', 'reservation'], true)) {
    $inquiryType = 'contact';
}

// --- Reservation slot -----------------------------------------------------
$reservedAt   = '';
$reservedZone = '';
$errors       = [];

if ($inquiryType === 'reservation') {
    $dateRaw = sanitize_text(jmn_str($data['reserved_date'] ?? ''));
    $timeRaw = sanitize_text(jmn_str($data['reserved_time'] ?? ''));
    $zoneRaw = sanitize_text(jmn_str($data['reserved_zone'] ?? ''));

    $dateObj = DateTimeImmutable::createFromFormat('Y-m-d', $dateRaw);
    $dateOk  = $dateRaw !== '' && $dateObj !== false && $dateObj->format('Y-m-d') === $dateRaw;
    $timeOk  = preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $timeRaw) === 1;

    if (!$dateOk) {
        $errors['reserved_date'] = 'Please choose a valid consultation date.';
    } elseif ($dateObj->setTime(0, 0, 0) < new DateTimeImmutable('today')) {
        $errors['reserved_date'] = 'The consultation date cannot be in the past.';
    }
    if (!$timeOk) {
        $errors['reserved_time'] = 'Please choose a valid consultation time.';
    }

    if ($zoneRaw !== '' && strlen($zoneRaw) > 64) {
        $zoneRaw = substr($zoneRaw, 0, 64);
    }
    $reservedZone = $zoneRaw;

    if (!$errors) {
        $slot = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i',
            $dateRaw . ' ' . $timeRaw,
            new DateTimeZone('Africa/Lagos')
        );
        if ($slot instanceof DateTimeImmutable) {
            $reservedAt = $slot->format('Y-m-d H:i:s');
        }
    }
} elseif ($phone !== '' && preg_match('/^[0-9+\s().\-]{6,30}$/', $phone) !== 1) {
    $errors['phone'] = 'Please provide a valid phone number or leave it blank.';
}

// --- Core validation ------------------------------------------------------
if (mb_strlen($name) < 2) {
    $errors['name'] = 'Please provide your full name (at least 2 characters).';
}
if (mb_strlen($name) > 150) {
    $errors['name'] = 'Please keep your name under 150 characters.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please provide a valid email address.';
}
if (mb_strlen($message) < 5) {
    $errors['message'] = 'Please describe what you need (at least 5 characters).';
}
if (mb_strlen($message) > 8000) {
    $errors['message'] = 'Your message is too long (maximum 8,000 characters).';
}

if ($errors) {
    jmn_respond($isAjax, [
        'success' => false,
        'message' => 'Validation failed. Please correct the highlighted errors.',
        'errors'  => $errors,
    ], 422, $failRedirect);
}

// ---------------------------------------------------------------------------
// 6. Capture client metadata
// ---------------------------------------------------------------------------
$clientIp   = client_ip();
$userAgent  = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'), 0, 300);
$timestamp  = date('Y-m-d H:i:s');
$inquiryId  = 0;
$dbSaved    = false;

// ---------------------------------------------------------------------------
// 7. MySQL insert (prepared statement, no string interpolation)
// ---------------------------------------------------------------------------
$db = get_db();
if ($db !== null) {
    try {
        $stmt = $db->prepare("
            INSERT INTO `contact_inquiries` (
                `name`, `email`, `phone`, `company`, `service`, `budget`, `message`,
                `inquiry_type`, `reserved_at`, `reserved_zone`,
                `ip_address`, `user_agent`, `status`, `created_at`
            ) VALUES (
                :name, :email, :phone, :company, :service, :budget, :message,
                :inquiry_type, :reserved_at, :reserved_zone,
                :ip, :ua, 'new', NOW()
            )
        ");
        $stmt->execute([
            ':name'          => mb_substr($name, 0, 150),
            ':email'         => mb_substr($email, 0, 150),
            ':phone'         => $phone !== '' ? mb_substr($phone, 0, 30) : null,
            ':company'       => $company !== '' ? mb_substr($company, 0, 150) : null,
            ':service'       => mb_substr($service, 0, 150),
            ':budget'        => mb_substr($budget, 0, 100),
            ':message'       => $message,
            ':inquiry_type'  => $inquiryType,
            ':reserved_at'   => $reservedAt !== '' ? $reservedAt : null,
            ':reserved_zone' => $reservedZone !== '' ? mb_substr($reservedZone, 0, 64) : null,
            ':ip'            => mb_substr($clientIp, 0, 45),
            ':ua'            => $userAgent,
        ]);
        $inquiryId = (int)$db->lastInsertId();
        $dbSaved   = $inquiryId > 0;
    } catch (Throwable $e) {
        error_log('Database error saving contact inquiry: ' . $e->getMessage());
    }
}

// ---------------------------------------------------------------------------
// 8. Disk audit log (append-only, masked IP)
// ---------------------------------------------------------------------------
$logDir = ROOT_PATH . '/storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
$logEntry = sprintf(
    "[%s] #%d | type=%s | from=%s <%s> | phone=%s | company=%s | scope=%s | budget=%s | slot=%s %s | ip=%s\n",
    $timestamp,
    $inquiryId,
    $inquiryType,
    $name,
    $email,
    $phone !== '' ? $phone : '-',
    $company !== '' ? $company : '-',
    $service,
    $budget,
    $reservedAt !== '' ? $reservedAt : '-',
    $reservedZone,
    $clientIp
);
@file_put_contents($logDir . '/inquiries.log', $logEntry, FILE_APPEND | LOCK_EX);

// ---------------------------------------------------------------------------
// 9. Dual dispatch - owner inbox AND sender acknowledgement
// ---------------------------------------------------------------------------
$payload = [
    'id'            => $inquiryId,
    'name'          => $name,
    'email'         => $email,
    'phone'         => $phone,
    'company'       => $company,
    'service'       => $service,
    'budget'        => $budget,
    'message'       => $message,
    'ip'            => $clientIp,
    'created_at'    => $timestamp,
    'inquiry_type'  => $inquiryType,
    'reserved_at'   => $reservedAt,
    'reserved_zone' => $reservedZone,
];

$adminHtml  = jmn_build_admin_email($payload);
$clientHtml = jmn_build_client_email($payload);

// Envelope sender is derived from the configured site domain, never Host.
$fromDomain   = preg_replace('/[^A-Za-z0-9.\-]/', '', (string)SITE_DOMAIN) ?: 'jmn.morexweb.com';
$fromAddress  = 'no-reply@' . $fromDomain;
$brandName    = sanitize_header_value(APP_NAME);

// Header-safe copies of user input (CR/LF and control bytes removed)
$hdrName  = sanitize_header_value($name);
$hdrEmail = sanitize_email($email);
if ($hdrEmail === '' || !filter_var($hdrEmail, FILTER_VALIDATE_EMAIL)) {
    $hdrEmail = $fromAddress;
}

$isReservation = $inquiryType === 'reservation' && $reservedAt !== '';
$subjectKind   = $isReservation ? 'Reservation' : 'Inquiry';

$ownerSubject = sanitize_header_value(
    "{$subjectKind} #{$inquiryId} from {$name} — {$service}"
);

$ownerHeaders  = "MIME-Version: 1.0\r\n";
$ownerHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
$ownerHeaders .= "From: {$brandName} <{$fromAddress}>\r\n";
$ownerHeaders .= "Reply-To: {$hdrName} <{$hdrEmail}>\r\n";
$ownerHeaders .= "X-Inquiry-ID: {$inquiryId}\r\n";
$ownerHeaders .= "X-Inquiry-Type: {$inquiryType}\r\n";
$ownerHeaders .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$ownerRecipients = array_values(array_unique(array_filter([
    (string)PROFILE_CORP_EMAIL,
    (string)PROFILE_EMAIL,
], static fn (string $v): bool => $v !== '')));

$mailSent = 0;
foreach ($ownerRecipients as $recipient) {
    if (@mail($recipient, $ownerSubject, $adminHtml, $ownerHeaders)) {
        $mailSent++;
    } else {
        error_log("mailer.php: mail() failed for owner recipient {$recipient} (inquiry #{$inquiryId})");
    }
}

$clientSubject = sanitize_header_value(
    "{$subjectKind} received: {$service} — " . PROFILE_NAME
);

$clientHeaders  = "MIME-Version: 1.0\r\n";
$clientHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
$clientHeaders .= "From: " . sanitize_header_value(PROFILE_NAME) . " <{$fromAddress}>\r\n";
$clientHeaders .= "Reply-To: " . sanitize_header_value(PROFILE_CORP_EMAIL) . "\r\n";
$clientHeaders .= "List-Unsubscribe: <" . url('contact.php') . ">\r\n";
$clientHeaders .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$clientDelivered = false;
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    if (@mail($email, $clientSubject, $clientHtml, $clientHeaders)) {
        $clientDelivered = true;
    } else {
        error_log("mailer.php: mail() failed for sender acknowledgement {$email} (inquiry #{$inquiryId})");
    }
}

// ---------------------------------------------------------------------------
// 10. Rotate CSRF token, then answer
// ---------------------------------------------------------------------------
unset($_SESSION['csrf_token']);
$newCsrfToken = generate_csrf_token();

jmn_respond($isAjax, [
    'success'        => true,
    'message'        => $isReservation
        ? 'Your consultation slot has been reserved. A confirmation has been emailed to you and to ' . PROFILE_CORP_EMAIL . '. Expect a reply within 24 hours.'
        : 'Your message has been delivered. A confirmation has been emailed to you and to ' . PROFILE_CORP_EMAIL . '. Expect a reply within 24 hours.',
    'inquiry_id'     => $inquiryId,
    'inquiry_type'   => $inquiryType,
    'reserved_at'    => $reservedAt,
    'db_saved'       => $dbSaved,
    'owner_mails'    => $mailSent,
    'client_mail'    => $clientDelivered,
    'new_csrf_token' => $newCsrfToken,
], 200, $returnTo);
