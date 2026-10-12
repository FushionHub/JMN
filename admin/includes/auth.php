<?php
declare(strict_types=1);

/**
 * Admin Authentication & Session Guard
 * Jamachi Maurice Mauricennadi Portfolio Platform — Back Office
 *
 * Session-based auth for the single-owner admin. Relies on the hardened
 * session bootstrap in config/security.php (JMNSESSID, strict mode,
 * HttpOnly, SameSite=Lax) and the CSRF helpers it exposes.
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/db.php';

// ---------------------------------------------------------------------------
// Session helpers
// ---------------------------------------------------------------------------

/** True when an authenticated admin session exists. */
function admin_is_logged_in(): bool
{
    return !empty($_SESSION['admin_user_id']) && !empty($_SESSION['admin_username']);
}

/** Fetch the currently authenticated admin row (or null). */
function admin_current_user(): ?array
{
    if (!admin_is_logged_in()) {
        return null;
    }
    static $cached = false;
    if ($cached !== false) {
        return $cached;
    }
    $cached = null;

    $db = get_db();
    if ($db === null) {
        return null;
    }
    try {
        $stmt = $db->prepare('SELECT id, username, display_name, email, role, last_login_at FROM `admin_users` WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$_SESSION['admin_user_id']]);
        $row = $stmt->fetch();
        if (is_array($row)) {
            $cached = $row;
        }
    } catch (Throwable $e) {
        error_log('admin_current_user failed: ' . $e->getMessage());
    }
    return $cached;
}

/** Redirect the browser to the login page and stop. */
function admin_redirect_login(): never
{
    header('Location: login.php');
    exit;
}

/**
 * Guard every authenticated admin page. Call at the top of each page,
 * before any output.
 */
function admin_require_auth(): void
{
    if (!admin_is_logged_in()) {
        admin_redirect_login();
    }
}

/** Redirect to the dashboard when already logged in (used by login.php). */
function admin_redirect_if_logged_in(): void
{
    if (admin_is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

// ---------------------------------------------------------------------------
// Login / logout
// ---------------------------------------------------------------------------

/**
 * Attempt to authenticate. Returns [ok: bool, error: string].
 * On success the session is regenerated (fixation mitigation) and the
 * admin's last_login_at timestamp is written.
 *
 * @return array{ok: bool, error: string}
 */
function admin_attempt_login(string $username, string $password): array
{
    $username = trim($username);
    if ($username === '' || $password === '') {
        return ['ok' => false, 'error' => 'Please enter both username and password.'];
    }

    $db = get_db();
    if ($db === null) {
        return ['ok' => false, 'error' => 'Database unavailable. Please try again later.'];
    }

    try {
        $stmt = $db->prepare('SELECT id, username, password_hash, display_name, role FROM `admin_users` WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
    } catch (Throwable $e) {
        error_log('admin_attempt_login query failed: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Authentication unavailable. Please try again later.'];
    }

    // Constant-time-ish: always run password_verify so the response time
    // does not reveal whether the username exists.
    $hash = is_array($row) ? (string)$row['password_hash'] : '$2y$10$invalidinvalidinvalidinvalidinvalidinvalidinvalidinvalidinu';
    $valid = password_verify($password, $hash) && is_array($row);

    if (!$valid) {
        // Light per-IP throttle on failed attempts (separate bucket from
        // the session-scoped one so a fresh session cannot bypass it).
        check_ip_rate_limit('admin_login_fail', 10, 900);
        return ['ok' => false, 'error' => 'Invalid username or password.'];
    }

    // Session fixation mitigation: new id, then mark authenticated.
    session_regenerate_id(true);
    $_SESSION['admin_user_id']     = (int)$row['id'];
    $_SESSION['admin_username']    = (string)$row['username'];
    $_SESSION['admin_role']        = (string)$row['role'];
    $_SESSION['admin_display']     = (string)$row['display_name'];
    $_SESSION['_session_booted']   = 1;

    try {
        $db->prepare('UPDATE `admin_users` SET last_login_at = NOW() WHERE id = ?')->execute([(int)$row['id']]);
    } catch (Throwable $e) {
        error_log('admin_attempt_login last_login_at update failed: ' . $e->getMessage());
    }

    return ['ok' => true, 'error' => ''];
}

/** Destroy the admin session and send the browser to the login page. */
function admin_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool)$p['secure'], (bool)$p['httponly']);
    }
    session_destroy();
}

// ---------------------------------------------------------------------------
// Password management
// ---------------------------------------------------------------------------

/**
 * Change the authenticated admin's password.
 *
 * @return array{ok: bool, error: string}
 */
function admin_change_password(int $userId, string $current, string $new): array
{
    $db = get_db();
    if ($db === null) {
        return ['ok' => false, 'error' => 'Database unavailable.'];
    }
    if (mb_strlen($new) < 10) {
        return ['ok' => false, 'error' => 'The new password must be at least 10 characters.'];
    }

    try {
        $stmt = $db->prepare('SELECT password_hash FROM `admin_users` WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $hash = (string)$stmt->fetchColumn();
        if ($hash === '' || !password_verify($current, $hash)) {
            return ['ok' => false, 'error' => 'Current password is incorrect.'];
        }
        $newHash = password_hash($new, PASSWORD_BCRYPT);
        $db->prepare('UPDATE `admin_users` SET password_hash = ? WHERE id = ?')->execute([$newHash, $userId]);
    } catch (Throwable $e) {
        error_log('admin_change_password failed: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Could not update the password.'];
    }

    return ['ok' => true, 'error' => ''];
}
