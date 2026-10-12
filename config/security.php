<?php
declare(strict_types=1);

/**
 * Security & Sanitization Engine
 * Jamachi Maurice Mauricennadi Portfolio Platform
 *
 * Covers: session hardening, HTTP security headers (CSP/HSTS/CORP),
 * CSRF tokens, output escaping, input sanitisation, session + IP rate
 * limiting, consent-aware tracking gates and host-header validation.
 */

// ---------------------------------------------------------------------------
// Runtime error policy - never leak paths/stack traces to visitors
// ---------------------------------------------------------------------------
$debugEnabled = (getenv('APP_ENV') ?: 'production') === 'development';
ini_set('display_errors', $debugEnabled ? '1' : '0');
ini_set('display_startup_errors', $debugEnabled ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__)) . '/storage/logs/php-error.log');
@mkdir((defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__)) . '/storage/logs', 0755, true);

// ---------------------------------------------------------------------------
// Secure session bootstrap
// ---------------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = is_https_request();

    session_name('JMNSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();

    // Mitigate session fixation: rotate the id exactly once per session.
    if (!isset($_SESSION['_session_booted'])) {
        session_regenerate_id(true);
        $_SESSION['_session_booted'] = 1;
    }
}

// ---------------------------------------------------------------------------
// Request helpers
// ---------------------------------------------------------------------------
function is_https_request(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443)
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

/**
 * Resolve the real client IP. Cloudflare -> reverse proxy -> socket.
 * The value is only ever used for throttling/analytics, never trusted
 * for authorisation decisions.
 */
function client_ip(): string
{
    $raw = $_SERVER['HTTP_CF_CONNECTING_IP']
        ?? (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR'])[0] : null)
        ?? $_SERVER['HTTP_X_REAL_IP']
        ?? $_SERVER['REMOTE_ADDR']
        ?? '0.0.0.0';

    $ip = trim((string)$raw);
    if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
        return '0.0.0.0';
    }
    return $ip;
}

/**
 * Guard against Host-header poisoning of BASE_URL derived links/emails.
 * Only the configured site domain, localhost and loopback names pass.
 */
function is_safe_host(string $host): bool
{
    $host = strtolower(trim($host));
    if ($host === '' || strlen($host) > 253) {
        return false;
    }
    if (preg_match('/^[a-z0-9\.\-\[\]:]+$/', $host) !== 1) {
        return false;
    }

    $allowed = ['localhost', '127.0.0.1', '::1'];
    if (defined('SITE_DOMAIN') && SITE_DOMAIN !== '') {
        $allowed[] = strtolower((string)SITE_DOMAIN);
        $allowed[] = 'www.' . strtolower((string)SITE_DOMAIN);
    }
    if (defined('PROFILE_PORTFOLIO_URL')) {
        $hostOnly = parse_url((string)PROFILE_PORTFOLIO_URL, PHP_URL_HOST);
        if (is_string($hostOnly) && $hostOnly !== '') {
            $allowed[] = strtolower($hostOnly);
        }
    }

    $bare = preg_replace('/:\d+$/', '', $host) ?? $host;
    foreach ($allowed as $candidate) {
        if ($bare === $candidate) {
            return true;
        }
    }
    return false;
}

// ---------------------------------------------------------------------------
// HTTP security headers
// ---------------------------------------------------------------------------
function send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 0');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=(), browsing-topics=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Cross-Origin-Resource-Policy: same-origin');

    if (is_https_request()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }

    // Content-Security-Policy.
    // 'unsafe-inline' / 'unsafe-eval' remain ONLY because the Tailwind browser
    // runtime compiles utility classes at runtime. Removing them breaks the UI,
    // so the rest of the policy is locked down hard instead.
    //
    // Google Translate is the one third-party origin on this site. It is NOT
    // referenced by any tag in the markup - assets/js/main.js injects the
    // script only after a visitor picks a language in the tools sidebar, so a
    // page that never uses translation never talks to Google.
    //
    // The widget needs FOUR origins, all of which it loads for itself once the
    // script boots. Missing any one of them silently breaks it:
    //   translate.googleapis.com  - element.js + the translation XHR endpoint
    //   www.gstatic.com           - the widget's own stylesheet and logo
    //   www.google.com            - a 1x1 beacon gif
    //   translate.google.com
    //   *.translate.goog          - the frame the widget renders into
    //
    // TinyMCE 8 (admin blog editor only) loads its core, skins, icons and
    // themes from cdn.tiny.cloud. It renders its editing surface and dialogs
    // inside about:blank iframes, hence the about:blank frame-src entry.
    // The public site never contacts this origin.
    $csp = implode('; ', [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://translate.googleapis.com https://cdn.tiny.cloud",
        "style-src 'self' 'unsafe-inline' https://translate.googleapis.com https://www.gstatic.com https://cdn.tiny.cloud",
        "img-src 'self' data: blob: https://www.gstatic.com https://fonts.gstatic.com https://www.google.com https://cdn.tiny.cloud",
        "font-src 'self' data: https://www.gstatic.com https://cdn.tiny.cloud",
        "connect-src 'self' https://translate.googleapis.com https://cdn.tiny.cloud",
        "media-src 'self' https://cdn.tiny.cloud",
        "object-src 'none'",
        "frame-src 'self' about:blank https://translate.google.com https://*.translate.goog",
        "child-src 'self' about:blank https://translate.google.com https://*.translate.goog",
        "worker-src 'self' blob:",
        "manifest-src 'self'",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'self'",
        "upgrade-insecure-requests",
    ]);
    // Sent once, enforced. A duplicate Report-Only copy of the same policy
    // only doubles the console noise without adding any protection.
    header("Content-Security-Policy: {$csp}");
    header('X-Robots-Tag: index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1');
}

send_security_headers();

// ---------------------------------------------------------------------------
// CSRF
// ---------------------------------------------------------------------------
function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function rotate_csrf_token(): string
{
    unset($_SESSION['csrf_token']);
    return generate_csrf_token();
}

function verify_csrf_token(?string $token): bool
{
    if (empty($token) || empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// ---------------------------------------------------------------------------
// Output escaping / input sanitisation
// ---------------------------------------------------------------------------
function e(?string $string): string
{
    return htmlspecialchars((string)($string ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sanitize_text(?string $data): string
{
    if ($data === null) {
        return '';
    }
    $trimmed = trim($data);
    // Strip null bytes and control characters while preserving \n and \r\n
    $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $trimmed) ?? '';
    return $clean;
}

/**
 * Sanitise a value that will be placed inside an RFC 5322 mail header.
 * Removes every character that could start a new header line.
 */
function sanitize_header_value(?string $value): string
{
    $v = (string)($value ?? '');
    $v = preg_replace('/[\r\n]+/', ' ', $v) ?? '';
    $v = preg_replace('/[\x00-\x1F\x7F]/', '', $v) ?? '';
    return trim($v);
}

function sanitize_email(?string $email): string
{
    if ($email === null) {
        return '';
    }
    $clean = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    return filter_var($clean, FILTER_VALIDATE_EMAIL) ? (string)$clean : '';
}

/** Normalise an arbitrary string into a URL slug. */
function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

// ---------------------------------------------------------------------------
// Rate limiting
// ---------------------------------------------------------------------------
/**
 * Session-scoped throttle (fast, defeats casual double submits).
 */
function check_rate_limit(string $action = 'contact_form', int $minIntervalSeconds = 5): bool
{
    $key  = 'rate_limit_' . preg_replace('/[^a-z0-9_]/i', '', $action);
    $now  = time();
    $last = (int)($_SESSION[$key] ?? 0);

    if ($last > 0 && ($now - $last) < $minIntervalSeconds) {
        return false;
    }
    $_SESSION[$key] = $now;
    return true;
}

/**
 * IP-scoped, file-backed throttle.
 * Survives cookie-less bots and private browsing windows.
 *
 * @return bool true when the request is allowed
 */
function check_ip_rate_limit(string $action, int $maxRequests = 8, int $windowSeconds = 300): bool
{
    $root = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__);
    $dir  = $root . '/storage/ratelimit';

    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        return true; // storage unavailable -> fail open, never brick the site
    }

    $bucket = preg_replace('/[^a-z0-9_\-]/i', '', $action) ?: 'default';
    $file   = $dir . '/' . sha1($bucket . '|' . client_ip()) . '.json';
    $now    = time();

    $fh = @fopen($file, 'c+');
    if ($fh === false) {
        return true;
    }

    $allowed = true;
    try {
        if (flock($fh, LOCK_EX)) {
            $raw  = (string)stream_get_contents($fh);
            $data = json_decode($raw, true);
            $start = (int)($data['start'] ?? $now);
            $count = (int)($data['count'] ?? 0);

            if (($now - $start) >= $windowSeconds) {
                $start = $now;
                $count = 0;
            }

            $count++;
            $allowed = $count <= $maxRequests;

            ftruncate($fh, 0);
            rewind($fh);
            fwrite($fh, json_encode(['start' => $start, 'count' => $count]));
            fflush($fh);
            flock($fh, LOCK_UN);

            // Opportunistic clean-up of stale buckets (1-in-100 requests)
            if (random_int(1, 100) === 1) {
                foreach ((array)glob($dir . '/*.json') ?: [] as $stale) {
                    $mtime = (int)@filemtime($stale);
                    if ($mtime > 0 && ($now - $mtime) > 86400) {
                        @unlink($stale);
                    }
                }
            }
        }
    } finally {
        fclose($fh);
    }

    return $allowed;
}

// ---------------------------------------------------------------------------
// Consent-aware processing gate
// ---------------------------------------------------------------------------
/**
 * Returns the recorded consent decision, if any.
 *
 * Two mirrors are kept in sync by config/cookie_consent.php:
 *   - $_SESSION['jmn_consent']  : authoritative for the current session
 *   - cookie jmn_cookie_consent : 1-year mirror so a returning visitor does
 *     not have to be asked again.
 *
 * @return array<string,mixed>|null
 */
function consent_record(): ?array
{
    static $cached = false;
    if ($cached !== false) {
        return $cached;
    }
    $cached = null;

    $sessionValue = $_SESSION['jmn_consent'] ?? null;
    if (is_array($sessionValue)) {
        $cached = $sessionValue;
        return $cached;
    }

    $cookieValue = (string)($_COOKIE['jmn_cookie_consent'] ?? '');
    if ($cookieValue !== '') {
        $decoded = json_decode($cookieValue, true);
        if (is_array($decoded)) {
            $cached = $decoded;
            return $cached;
        }
    }

    return null;
}

/**
 * Returns true when the visitor has granted the given consent category.
 *
 * Categories: analytics | preferences   (necessary is always implied)
 */
function consent_allows(string $category): bool
{
    if ($category === 'necessary') {
        return true;
    }

    $record = consent_record();
    if (!is_array($record)) {
        return false; // no recorded decision -> treat as refused
    }

    $type = (string)($record['consent_type'] ?? ($record['type'] ?? 'necessary'));
    if ($type === 'all') {
        return true;
    }
    if ($type === 'declined' || $type === 'necessary') {
        return false;
    }
    if ($type === 'custom') {
        $flag = $record[$category] ?? ($record[$category . '_enabled'] ?? 0);
        return !empty($flag);
    }
    return false;
}

// ---------------------------------------------------------------------------
// JSON endpoint helper
// ---------------------------------------------------------------------------
function json_response(array $payload, int $status = 200): void
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('X-Content-Type-Options: nosniff');
    }
    echo json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
    exit;
}
