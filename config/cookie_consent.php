<?php
declare(strict_types=1);

/**
 * Real-Time Cookie Consent Storage Handler
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12, GDPR & NDPR Compliance.
 *
 * Only three categories exist because only three categories exist:
 *   necessary   - sessions, CSRF, security headers (always on)
 *   analytics   - visitor_traffic rows (gates config/tracker.php)
 *   preferences - theme / layout persistence in the browser
 *
 * There is no advertising, retargeting or third-party tag on this site, so
 * there is deliberately no "marketing" category. The endpoint is protected by
 * a CSRF token and a per-IP rate limit.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method Not Allowed'], 405);
}

if (!check_ip_rate_limit('cookie_consent', 12, 300)) {
    json_response(['success' => false, 'message' => 'Too many requests. Please try again shortly.'], 429);
}

// Read JSON or POST input
$rawInput  = (string)file_get_contents('php://input');
$json      = json_decode($rawInput, true);
$data      = is_array($json) ? $json : $_POST;

// CSRF - a third party must not be able to rewrite a visitor's choice.
$csrfToken = $data['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
if (!verify_csrf_token(is_string($csrfToken) ? $csrfToken : null)) {
    json_response(['success' => false, 'message' => 'Security token expired. Please reload the page and try again.'], 419);
}

$consentType = sanitize_text($data['consent_type'] ?? 'all');
if (!in_array($consentType, ['all', 'necessary', 'custom', 'declined'], true)) {
    $consentType = 'all';
}

$essential = 1; // Always enabled for security and core navigation

$analyticsFlag   = filter_var($data['analytics_enabled'] ?? 0, FILTER_VALIDATE_BOOLEAN);
$preferencesFlag = filter_var($data['preferences_enabled'] ?? 0, FILTER_VALIDATE_BOOLEAN);

$analytics   = ($consentType === 'all' || ($consentType === 'custom' && $analyticsFlag)) && $consentType !== 'declined' && $consentType !== 'necessary' ? 1 : 0;
$preferences = ($consentType === 'all' || ($consentType === 'custom' && $preferencesFlag)) && $consentType !== 'declined' && $consentType !== 'necessary' ? 1 : 0;

if ($consentType === 'necessary' || $consentType === 'declined') {
    $analytics   = 0;
    $preferences = 0;
}

// No marketing / retargeting technology exists on this site.
$marketing = 0;

// Server-side record (authoritative for this session, never sent to the browser)
$_SESSION['jmn_consent'] = [
    'consent_type' => $consentType,
    'essential'    => 1,
    'analytics'    => $analytics,
    'preferences'  => $preferences,
    'marketing'    => 0,
    'timestamp'    => time(),
];

// Client IP / UA are only recorded when the visitor opted into analytics,
// otherwise the consent audit row would itself be tracking data.
$ip        = $analytics ? client_ip() : '0.0.0.0';
$userAgent = $analytics ? ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') : 'Withheld';

// Store in MySQL database
$db      = get_db();
$savedDb = false;

if ($db !== null) {
    try {
        $stmt = $db->prepare("
            INSERT INTO `cookie_consents` (
                `ip_address`, `user_agent`, `consent_type`,
                `essential_enabled`, `analytics_enabled`, `marketing_enabled`, `preferences_enabled`,
                `consented_at`
            ) VALUES (
                :ip, :ua, :type,
                :essential, :analytics, :marketing, :preferences,
                NOW()
            )
        ");

        $stmt->execute([
            ':ip'          => substr($ip, 0, 45),
            ':ua'          => substr($userAgent, 0, 500),
            ':type'        => $consentType,
            ':essential'   => $essential,
            ':analytics'   => $analytics,
            ':marketing'   => $marketing,
            ':preferences' => $preferences,
        ]);
        $savedDb = true;
    } catch (Throwable $e) {
        error_log('Failed to save cookie consent to database: ' . $e->getMessage());
    }
}

// Set browser cookie (1 year expiry) - mirrors the session record
$cookiePayload = json_encode([
    'consent_type' => $consentType,
    'type'         => $consentType,
    'essential'    => $essential,
    'analytics'    => $analytics,
    'preferences'  => $preferences,
    'marketing'    => $marketing,
    'timestamp'    => time(),
]);

setcookie('jmn_cookie_consent', (string)$cookiePayload, [
    'expires'  => time() + (365 * 24 * 60 * 60),
    'path'     => '/',
    'domain'   => '',
    'secure'   => is_https_request(),
    'httponly' => false, // Read by the UI to remember which switches to show
    'samesite' => 'Lax',
]);

json_response([
    'success'  => true,
    'message'  => 'Cookie preferences updated and recorded successfully.',
    'db_saved' => $savedDb,
    'consent'  => [
        'type'        => $consentType,
        'essential'   => (bool)$essential,
        'analytics'   => (bool)$analytics,
        'preferences' => (bool)$preferences,
        'marketing'   => false,
    ],
]);
