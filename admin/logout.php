<?php
declare(strict_types=1);

/**
 * Admin Logout — destroys the session and returns to the login page.
 */

require_once __DIR__ . '/includes/auth.php';

admin_logout();

// Fresh session for the redirect so the login CSRF token is clean.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Location: login.php');
exit;
