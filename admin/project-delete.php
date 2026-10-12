<?php
declare(strict_types=1);

/**
 * Admin Project Delete — POST-only deletion of a single project.
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
    header('Location: projects.php');
    exit;
}
if (!check_rate_limit('admin_project_delete', 2)) {
    $_SESSION['flash_err'] = 'Please wait a moment before deleting again.';
    header('Location: projects.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash_err'] = 'Invalid project id.';
    header('Location: projects.php');
    exit;
}

$db = get_db();
if ($db === null) {
    $_SESSION['flash_err'] = 'Database unavailable.';
    header('Location: projects.php');
    exit;
}

try {
    $stmt = $db->prepare('SELECT `id`, `slug`, `title` FROM `projects` WHERE `id` = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!is_array($row)) {
        $_SESSION['flash_err'] = 'Project not found.';
        header('Location: projects.php');
        exit;
    }

    $stmt = $db->prepare('DELETE FROM `projects` WHERE `id` = ?');
    $stmt->execute([$id]);

    error_log('admin/project-delete.php: deleted project #' . $id . ' (' . $row['slug'] . ')');
    $_SESSION['flash_ok'] = 'Deleted “' . $row['title'] . '”.';
    cache_clear(); // content changed — drop cached lists so the public site updates immediately
    rotate_csrf_token();
} catch (Throwable $e) {
    error_log('Project delete failed: ' . $e->getMessage());
    $_SESSION['flash_err'] = 'Database error while deleting: ' . $e->getMessage();
}

header('Location: projects.php');
exit;
