<?php
declare(strict_types=1);

/**
 * Admin Blog Delete — POST-only deletion of a single post.
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
    header('Location: blog.php');
    exit;
}
if (!check_rate_limit('admin_blog_delete', 2)) {
    $_SESSION['flash_err'] = 'Please wait a moment before deleting again.';
    header('Location: blog.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash_err'] = 'Invalid post id.';
    header('Location: blog.php');
    exit;
}

$db = get_db();
if ($db === null) {
    $_SESSION['flash_err'] = 'Database unavailable.';
    header('Location: blog.php');
    exit;
}

try {
    $stmt = $db->prepare('SELECT `slug`, `title` FROM `blogs` WHERE `id` = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!is_array($row)) {
        $_SESSION['flash_err'] = 'Post not found.';
        header('Location: blog.php');
        exit;
    }

    $db->prepare('DELETE FROM `blogs` WHERE `id` = ?')->execute([$id]);

    $_SESSION['flash_ok'] = 'Deleted "' . $row['title'] . '".';
    cache_clear(); // content changed — drop cached lists so the public site updates immediately
    error_log('admin/blog-delete.php: deleted post #' . $id . ' (' . $row['slug'] . ')');
} catch (Throwable $e) {
    error_log('Blog delete failed: ' . $e->getMessage());
    $_SESSION['flash_err'] = 'Could not delete the post.';
}

rotate_csrf_token();
header('Location: blog.php');
exit;
