<?php
declare(strict_types=1);

/**
 * Admin Blog — list of all posts (published + drafts) with quick actions.
 */

require_once __DIR__ . '/includes/auth.php';
admin_require_auth();
require_once dirname(__DIR__) . '/config/db.php';

$adminTitle = 'Blog';
$adminNav   = 'blog';

$statusFilter = isset($_GET['status']) ? preg_replace('/[^a-z]/', '', (string)$_GET['status']) : '';
if (!in_array($statusFilter, ['published', 'draft'], true)) {
    $statusFilter = '';
}

$db = get_db();
$posts = [];
$counts = ['published' => 0, 'draft' => 0];

if ($db !== null) {
    try {
        $rows = $db->query("SELECT status, COUNT(*) c FROM `blogs` GROUP BY status")->fetchAll();
        foreach ($rows as $r) {
            $counts[(string)$r['status']] = (int)$r['c'];
        }

        $whereSql = $statusFilter !== '' ? "WHERE `status` = :status" : '';
        $stmt = $db->prepare("
            SELECT id, slug, title, subtitle, category, read_time, image,
                   status, published_at, updated_at
            FROM `blogs` {$whereSql}
            ORDER BY `updated_at` DESC
        ");
        if ($statusFilter !== '') {
            $stmt->execute([':status' => $statusFilter]);
        } else {
            $stmt->execute();
        }
        $posts = $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('Blog list query failed: ' . $e->getMessage());
    }
}

function admin_blog_url(?string $status): string
{
    return 'blog.php' . ($status !== null && $status !== '' ? '?status=' . urlencode($status) : '');
}

require_once __DIR__ . '/includes/layout-header.php';
?>

<div class="admin-filters">
    <a class="admin-chip <?= $statusFilter === '' ? 'is-active' : '' ?>" href="<?= e(admin_blog_url('')) ?>">
        All <span class="admin-chip-count"><?= (int)($counts['published'] + $counts['draft']) ?></span>
    </a>
    <a class="admin-chip <?= $statusFilter === 'published' ? 'is-active' : '' ?>" href="<?= e(admin_blog_url('published')) ?>">
        Published <span class="admin-chip-count"><?= (int)$counts['published'] ?></span>
    </a>
    <a class="admin-chip <?= $statusFilter === 'draft' ? 'is-active' : '' ?>" href="<?= e(admin_blog_url('draft')) ?>">
        Drafts <span class="admin-chip-count"><?= (int)$counts['draft'] ?></span>
    </a>
    <a class="admin-btn admin-btn--primary" href="blog-edit.php" style="margin-left:auto;">
        <i class="ri-add-line"></i> New post
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-body admin-card-body--flush">
        <?php if (empty($posts)): ?>
            <div class="admin-empty">
                <i class="ri-article-line"></i>
                <p>No posts found. <a href="blog-edit.php">Create the first one</a>.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Published</th>
                            <th>Updated</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($posts as $p): ?>
                        <tr>
                            <td>
                                <a href="blog-edit.php?id=<?= (int)$p['id'] ?>"><?= e($p['title']) ?></a>
                                <span class="admin-sub"><?= e($p['slug']) ?></span>
                            </td>
                            <td><?= e($p['category']) ?></td>
                            <td><span class="admin-badge admin-badge--<?= e($p['status']) ?>"><?= e($p['status']) ?></span></td>
                            <td><?= e(date('M d, Y', strtotime((string)$p['published_at']))) ?></td>
                            <td><?= e(date('M d, Y H:i', strtotime((string)$p['updated_at']))) ?></td>
                            <td style="text-align:right; white-space:nowrap;">
                                <?php if ($p['status'] === 'published'): ?>
                                    <a class="admin-btn admin-btn--secondary admin-btn--sm" href="<?= e(url('blog-detail.php?slug=' . rawurlencode((string)$p['slug']))) ?>" target="_blank" rel="noopener">
                                        <i class="ri-eye-line"></i> View
                                    </a>
                                <?php endif; ?>
                                <a class="admin-btn admin-btn--secondary admin-btn--sm" href="blog-edit.php?id=<?= (int)$p['id'] ?>">
                                    <i class="ri-edit-line"></i> Edit
                                </a>
                                <form method="post" action="blog-delete.php" style="display:inline;"
                                      onsubmit="return confirm('Delete &quot;<?= e($p['title']) ?>&quot;? This cannot be undone.');">
                                    <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                    <button class="admin-btn admin-btn--danger admin-btn--sm" type="submit">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>
