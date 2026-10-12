<?php
declare(strict_types=1);

/**
 * Admin Testimonials — list of client endorsements with quick actions.
 */

require_once __DIR__ . '/includes/auth.php';
admin_require_auth();
require_once dirname(__DIR__) . '/config/db.php';

$adminTitle = 'Testimonials';
$adminNav   = 'testimonials';

$db = get_db();
$items = [];
$total = 0;

if ($db !== null) {
    try {
        $total = (int)$db->query('SELECT COUNT(*) FROM `testimonials`')->fetchColumn();
        $stmt = $db->query('SELECT id, name, role, company, image, sort_order FROM `testimonials` ORDER BY `sort_order` ASC, `id` ASC');
        $items = $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('Testimonials list query failed: ' . $e->getMessage());
    }
}

require_once __DIR__ . '/includes/layout-header.php';
?>

<div class="admin-filters">
    <span class="admin-chip is-active">All <span class="admin-chip-count"><?= $total ?></span></span>
    <a class="admin-btn admin-btn--primary" href="testimonial-edit.php" style="margin-left:auto;">
        <i class="ri-add-line"></i> New testimonial
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-body admin-card-body--flush">
        <?php if (empty($items)): ?>
            <div class="admin-empty">
                <i class="ri-chat-quote-line"></i>
                <p>No testimonials found. <a href="testimonial-edit.php">Add the first one</a>.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role / Company</th>
                            <th>Sort</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $t): ?>
                        <tr>
                            <td><a href="testimonial-edit.php?id=<?= (int)$t['id'] ?>"><?= e($t['name']) ?></a></td>
                            <td><?= e(trim($t['role'] . ' · ' . $t['company'], ' ·')) ?></td>
                            <td><?= (int)$t['sort_order'] ?></td>
                            <td style="text-align:right; white-space:nowrap;">
                                <a class="admin-btn admin-btn--secondary admin-btn--sm" href="testimonial-edit.php?id=<?= (int)$t['id'] ?>">
                                    <i class="ri-edit-line"></i> Edit
                                </a>
                                <form method="post" action="testimonial-delete.php" style="display:inline;"
                                      onsubmit="return confirm('Delete the testimonial from &quot;<?= e($t['name']) ?>&quot;? This cannot be undone.');">
                                    <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
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
