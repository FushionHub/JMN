<?php
declare(strict_types=1);

/**
 * Admin Login — JMN Back Office
 */

require_once __DIR__ . '/includes/auth.php';
admin_redirect_if_logged_in();

// Never let the login surface be indexed.
if (!headers_sent()) {
    header_remove('X-Robots-Tag');
    header('X-Robots-Tag: noindex, nofollow, noarchive');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_rate_limit('admin_login', 2)) {
        $error = 'Too many attempts. Please wait a moment and try again.';
    } else {
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !verify_csrf_token($token)) {
            $error = 'Your security token has expired. Please try again.';
        } else {
            $username = is_string($_POST['username'] ?? '') ? trim((string)$_POST['username']) : '';
            $password = is_string($_POST['password'] ?? '') ? (string)$_POST['password'] : '';
            $result   = admin_attempt_login($username, $password);
            if ($result['ok']) {
                rotate_csrf_token();
                header('Location: index.php');
                exit;
            }
            $error = $result['error'];
            rotate_csrf_token();
        }
    }
}

$csrf = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Login | JMN Admin</title>
    <?php $adminCssVer = file_exists(__DIR__ . '/assets/admin.css') ? (string)filemtime(__DIR__ . '/assets/admin.css') : '1.0'; ?>
    <link rel="stylesheet" href="<?= e(url('admin/assets/admin.css')) ?>?v=<?= e($adminCssVer) ?>">
</head>
<body class="admin-body">
<div class="admin-login-wrap">
    <div class="admin-login-card">
        <div class="admin-login-brand">
            <span class="mark">JMN</span>
            <span class="text">Admin</span>
        </div>
        <h1>Sign in</h1>
        <p class="sub">Back office for inquiries &amp; content</p>

        <?php if ($error !== ''): ?>
            <div class="admin-login-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

            <div class="admin-field">
                <label for="username">Username</label>
                <input class="admin-input" type="text" id="username" name="username"
                       value="<?= e($_POST['username'] ?? '') ?>" required autofocus>
            </div>

            <div class="admin-field">
                <label for="password">Password</label>
                <input class="admin-input" type="password" id="password" name="password" required>
            </div>

            <div class="admin-form-actions">
                <button class="admin-btn admin-btn--primary" type="submit" style="width:100%;justify-content:center;">
                    <i class="ri-login-box-line"></i> Sign in
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
