<?php
declare(strict_types=1);

/**
 * Admin Skills — list of the technical skills matrix with quick actions.
 */

require_once __DIR__ . '/includes/auth.php';
admin_require_auth();
require_once dirname(__DIR__) . '/config/db.php';

$adminTitle = 'Skills Matrix';
$adminNav   = 'skills';

$db = get_db();
$items = [];
$total = 0;

if ($db !== null) {
    try {
        $total = (int)$db->query('SELECT COUNT(*) FROM `skills`')->fetchColumn();
        $stmt = $db->query('SELECT id, slug, category, percentage, sort_order FROM `skills` ORDER BY `sort_order` ASC, `id` ASC');
        $items = $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('Skills list query failed: ' . $e->getMessage());
    }
}

require_once __DIR__ . '/includes/layout-header.php';
?>

<div class="admin-filters">
    <span class="admin-chip is-active">All <span class="admin-chip-count"><?= $total ?></span></span>
    <a class="admin-btn admin-btn--primary" href="skill-edit.php" style="margin-left:auto;">
        <i class="ri-add-line"></i> New skill
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-body admin-card-body--flush">
        <?php if (empty($items)): ?>
            <div class="admin-empty">
                <i class="ri-bar-chart-box-line"></i>
                <p>No skills found. <a href="skill-edit.php">Add the first one</a>.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Slug</th>
                            <th>Level</th>
                            <th>Sort</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $s): ?>
                        <tr>
                            <td><a href="skill-edit.php?id=<?= (int)$s['id'] ?>"><?= e($s['category']) ?></a></td>
                            <td><span class="admin-sub"><?= e($s['slug']) ?></span></td>
                            <td><?= (int)$s['percentage'] ?>%</td>
                            <td><?= (int)$s['sort_order'] ?></td>
                            <td style="text-align:right; white-space:nowrap;">
                                <a class="admin-btn admin-btn--secondary admin-btn--sm" href="skill-edit.php?id=<?= (int)$s['id'] ?>">
                                    <i class="ri-edit-line"></i> Edit
                                </a>
                                <form method="post" action="skill-delete.php" style="display:inline;"
                                      onsubmit="return confirm('Delete &quot;<?= e($s['category']) ?>&quot;? This cannot be undone.');">
                                    <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
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
