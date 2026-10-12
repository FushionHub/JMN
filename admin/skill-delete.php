<?php
declare(strict_types=1);

/**
 * Admin Skill Delete — POST-only deletion of a single skills-matrix entry.
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
    header('Location: skills.php');
    exit;
}
if (!check_rate_limit('admin_skill_delete', 2)) {
    $_SESSION['flash_err'] = 'Please wait a moment before deleting again.';
    header('Location: skills.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash_err'] = 'Invalid skill id.';
    header('Location: skills.php');
    exit;
}

$db = get_db();
if ($db === null) {
    $_SESSION['flash_err'] = 'Database unavailable.';
    header('Location: skills.php');
    exit;
}

try {
    $stmt = $db->prepare('SELECT `id`, `category` FROM `skills` WHERE `id` = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!is_array($row)) {
        $_SESSION['flash_err'] = 'Skill not found.';
        header('Location: skills.php');
        exit;
    }

    $stmt = $db->prepare('DELETE FROM `skills` WHERE `id` = ?');
    $stmt->execute([$id]);

    error_log('admin/skill-delete.php: deleted skill #' . $id . ' (' . $row['category'] . ')');
    $_SESSION['flash_ok'] = 'Deleted “' . $row['category'] . '”.';
    cache_clear(); // content changed — drop cached lists so the public site updates immediately
    rotate_csrf_token();
} catch (Throwable $e) {
    error_log('Skill delete failed: ' . $e->getMessage());
    $_SESSION['flash_err'] = 'Database error while deleting: ' . $e->getMessage();
}

header('Location: skills.php');
exit;
