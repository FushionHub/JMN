<?php
declare(strict_types=1);

/**
 * Admin Services — list of all service offerings with quick actions.
 */

require_once __DIR__ . '/includes/auth.php';
admin_require_auth();
require_once dirname(__DIR__) . '/config/db.php';

$adminTitle = 'Services';
$adminNav   = 'services';

$db = get_db();
$services = [];
$total = 0;

if ($db !== null) {
    try {
        $total = (int)$db->query('SELECT COUNT(*) FROM `services`')->fetchColumn();
        $stmt = $db->query('SELECT id, slug, title, tagline, bg_color, sort_order FROM `services` ORDER BY `sort_order` ASC, `id` ASC');
        $services = $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('Services list query failed: ' . $e->getMessage());
    }
}

require_once __DIR__ . '/includes/layout-header.php';
?>

<div class="admin-filters">
    <span class="admin-chip is-active">All <span class="admin-chip-count"><?= $total ?></span></span>
    <a class="admin-btn admin-btn--primary" href="service-edit.php" style="margin-left:auto;">
        <i class="ri-add-line"></i> New service
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-body admin-card-body--flush">
        <?php if (empty($services)): ?>
            <div class="admin-empty">
                <i class="ri-stack-line"></i>
                <p>No services found. <a href="service-edit.php">Create the first one</a>.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Tagline</th>
                            <th>Sort</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($services as $s): ?>
                        <tr>
                            <td>
                                <a href="service-edit.php?id=<?= (int)$s['id'] ?>"><?= e($s['title']) ?></a>
                                <span class="admin-sub"><?= e($s['slug']) ?></span>
                            </td>
                            <td><?= e($s['tagline']) ?></td>
                            <td><?= (int)$s['sort_order'] ?></td>
                            <td style="text-align:right; white-space:nowrap;">
                                <a class="admin-btn admin-btn--secondary admin-btn--sm" href="service-edit.php?id=<?= (int)$s['id'] ?>">
                                    <i class="ri-edit-line"></i> Edit
                                </a>
                                <form method="post" action="service-delete.php" style="display:inline;"
                                      onsubmit="return confirm('Delete &quot;<?= e($s['title']) ?>&quot;? This cannot be undone.');">
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
