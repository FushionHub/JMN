<?php
declare(strict_types=1);

/**
 * Admin Testimonial Delete — POST-only deletion of a single testimonial.
 */

require_once __DIR__ . '/includes/auth.php';
admin_require_auth();
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/cache.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo '405 Method Not Allowed';
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!is_string($token) || !verify_csrf_token($token)) {
    $_SESSION['flash_err'] = 'Your security token has expired. Please try again.';
    header('Location: testimonials.php');
    exit;
}
if (!check_rate_limit('admin_testimonial_delete', 2)) {
    $_SESSION['flash_err'] = 'Please wait a moment before deleting again.';
    header('Location: testimonials.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash_err'] = 'Invalid testimonial id.';
    header('Location: testimonials.php');
    exit;
}

$db = get_db();
if ($db === null) {
    $_SESSION['flash_err'] = 'Database unavailable.';
    header('Location: testimonials.php');
    exit;
}

try {
    $stmt = $db->prepare('SELECT `id`, `name` FROM `testimonials` WHERE `id` = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!is_array($row)) {
        $_SESSION['flash_err'] = 'Testimonial not found.';
        header('Location: testimonials.php');
        exit;
    }

    $stmt = $db->prepare('DELETE FROM `testimonials` WHERE `id` = ?');
    $stmt->execute([$id]);

    error_log('admin/testimonial-delete.php: deleted testimonial #' . $id . ' (' . $row['name'] . ')');
    $_SESSION['flash_ok'] = 'Deleted testimonial from “' . $row['name'] . '”.';
    cache_clear(); // content changed — drop cached lists so the public site updates immediately
    rotate_csrf_token();
} catch (Throwable $e) {
    error_log('Testimonial delete failed: ' . $e->getMessage());
    $_SESSION['flash_err'] = 'Database error while deleting: ' . $e->getMessage();
}

header('Location: testimonials.php');
exit;
