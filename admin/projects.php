<?php
declare(strict_types=1);

/**
 * Admin Projects — list of all portfolio projects with quick actions.
 */

require_once __DIR__ . '/includes/auth.php';
admin_require_auth();
require_once dirname(__DIR__) . '/config/db.php';

$adminTitle = 'Projects';
$adminNav   = 'projects';

$featuredFilter = isset($_GET['featured']) ? preg_replace('/[^01]/', '', (string)$_GET['featured']) : '';
if (!in_array($featuredFilter, ['0', '1'], true)) {
    $featuredFilter = '';
}

$db = get_db();
$projects = [];
$counts = ['all' => 0, 'featured' => 0];

if ($db !== null) {
    try {
        $rows = $db->query("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN featured = 1 THEN 1 ELSE 0 END) AS feat
            FROM `projects`
        ")->fetch();
        $counts['all']      = (int)($rows['total'] ?? 0);
        $counts['featured'] = (int)($rows['feat'] ?? 0);

        $whereSql = $featuredFilter === '1' ? 'WHERE `featured` = 1' : ($featuredFilter === '0' ? 'WHERE `featured` = 0' : '');
        $stmt = $db->prepare("
            SELECT id, slug, title, subtitle, role, client, date_period, featured,
                   category_name, image, created_at
            FROM `projects` {$whereSql}
            ORDER BY `featured` DESC, `id` ASC
        ");
        $stmt->execute();
        $projects = $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('Projects list query failed: ' . $e->getMessage());
    }
}

function admin_projects_url(?string $featured): string
{
    return 'projects.php' . ($featured !== null && $featured !== '' ? '?featured=' . urlencode($featured) : '');
}

require_once __DIR__ . '/includes/layout-header.php';
?>

<div class="admin-filters">
    <a class="admin-chip <?= $featuredFilter === '' ? 'is-active' : '' ?>" href="<?= e(admin_projects_url('')) ?>">
        All <span class="admin-chip-count"><?= $counts['all'] ?></span>
    </a>
    <a class="admin-chip <?= $featuredFilter === '1' ? 'is-active' : '' ?>" href="<?= e(admin_projects_url('1')) ?>">
        Featured <span class="admin-chip-count"><?= $counts['featured'] ?></span>
    </a>
    <a class="admin-chip <?= $featuredFilter === '0' ? 'is-active' : '' ?>" href="<?= e(admin_projects_url('0')) ?>">
        Standard <span class="admin-chip-count"><?= $counts['all'] - $counts['featured'] ?></span>
    </a>
    <a class="admin-btn admin-btn--primary" href="project-edit.php" style="margin-left:auto;">
        <i class="ri-add-line"></i> New project
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-body admin-card-body--flush">
        <?php if (empty($projects)): ?>
            <div class="admin-empty">
                <i class="ri-folder-chart-line"></i>
                <p>No projects found. <a href="project-edit.php">Create the first one</a>.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Category</th>
                            <th>Role</th>
                            <th>Featured</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($projects as $p): ?>
                        <tr>
                            <td>
                                <a href="project-edit.php?id=<?= (int)$p['id'] ?>"><?= e($p['title']) ?></a>
                                <span class="admin-sub"><?= e($p['slug']) ?></span>
                            </td>
                            <td><?= e($p['category_name']) ?></td>
                            <td><?= e($p['role']) ?></td>
                            <td>
                                <?php if ((int)$p['featured'] === 1): ?>
                                    <span class="admin-badge admin-badge--published">featured</span>
                                <?php else: ?>
                                    <span class="admin-badge admin-badge--draft">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <a class="admin-btn admin-btn--secondary admin-btn--sm" href="<?= e(url('single-project.php?slug=' . rawurlencode((string)$p['slug']))) ?>" target="_blank" rel="noopener">
                                    <i class="ri-eye-line"></i> View
                                </a>
                                <a class="admin-btn admin-btn--secondary admin-btn--sm" href="project-edit.php?id=<?= (int)$p['id'] ?>">
                                    <i class="ri-edit-line"></i> Edit
                                </a>
                                <form method="post" action="project-delete.php" style="display:inline;"
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
