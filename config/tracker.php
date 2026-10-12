<?php
declare(strict_types=1);

/**
 * Real-Time Visitor Traffic Tracker
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12, Performance-First Logging.
 *
 * Privacy by design: rows are only written when the visitor has granted the
 * "Analytics" consent category. Without that consent no IP address, user
 * agent or session id is ever persisted. Necessary-only sessions are silently
 * skipped, and a successful row is throttled to once per session per path so
 * a browsing session cannot inflate the table.
 */

require_once __DIR__ . '/db.php';

function track_visitor_traffic(): void
{
    // Skip command-line executions
    if (php_sapi_name() === 'cli') {
        return;
    }

    // Consent gate - analytics data requires an explicit opt-in.
    if (!function_exists('consent_allows') || !consent_allows('analytics')) {
        return;
    }

    $uri = $_SERVER['REQUEST_URI'] ?? '/';

    // Skip static assets to keep traffic analytics clean
    $extension = strtolower(pathinfo(parse_url($uri, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
    if (in_array($extension, ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico', 'woff', 'woff2', 'ttf', 'map', 'pdf'])) {
        return;
    }

    $db = get_db();
    if ($db === null) {
        return;
    }

    // Per-session de-duplication: one row per path per session.
    $sessionKey = 'traffic_logged_paths';
    if (empty($_SESSION[$sessionKey]) || !is_array($_SESSION[$sessionKey])) {
        $_SESSION[$sessionKey] = [];
    }
    $pathKey = sha1($uri);
    if (isset($_SESSION[$sessionKey][$pathKey])) {
        return;
    }
    // Hard cap so a long session cannot grow the array without bound.
    if (count($_SESSION[$sessionKey]) >= 200) {
        return;
    }

    try {
        $ip         = client_ip();
        $userAgent  = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $referrer   = $_SERVER['HTTP_REFERER'] ?? null;
        $sessionId  = session_id() ?: null;

        // Simple device detection
        $deviceType = 'desktop';
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $userAgent)) {
            $deviceType = 'tablet';
        } elseif (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $userAgent)) {
            $deviceType = 'mobile';
        }

        $stmt = $db->prepare("
            INSERT INTO `visitor_traffic` (`ip_address`, `user_agent`, `page_url`, `referrer`, `device_type`, `session_id`, `visited_at`)
            VALUES (:ip, :ua, :url, :ref, :device, :session_id, NOW())
        ");

        $ok = $stmt->execute([
            ':ip'         => substr($ip, 0, 45),
            ':ua'         => substr($userAgent, 0, 500),
            ':url'        => substr($uri, 0, 500),
            ':ref'        => $referrer ? substr($referrer, 0, 500) : null,
            ':device'     => $deviceType,
            ':session_id' => $sessionId,
        ]);

        if ($ok) {
            $_SESSION[$sessionKey][$pathKey] = time();
        }
    } catch (Throwable $e) {
        // Silently log to avoid interrupting the user response
        error_log('Visitor tracking error: ' . $e->getMessage());
    }
}
