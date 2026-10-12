<?php
declare(strict_types=1);

/**
 * Admin Inquiries — filterable list of contact / reservation submissions.
 */

require_once __DIR__ . '/includes/auth.php';
admin_require_auth();
require_once dirname(__DIR__) . '/config/db.php';

$adminTitle = 'Inquiries';
$adminNav   = 'inquiries';

$statusFilter = isset($_GET['status']) ? preg_replace('/[^a-z]/', '', (string)$_GET['status']) : '';
if (!in_array($statusFilter, ['new', 'read', 'replied', 'archived'], true)) {
    $statusFilter = '';
}
$search = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$search = mb_substr($search, 0, 100);

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$db = get_db();
$counts = ['new' => 0, 'read' => 0, 'replied' => 0, 'archived' => 0, 'all' => 0];
$inquiries = [];
$totalRows = 0;
$totalPages = 1;

if ($db !== null) {
    try {
        $rows = $db->query("SELECT status, COUNT(*) c FROM `contact_inquiries` GROUP BY status")->fetchAll();
        foreach ($rows as $r) {
            $counts[(string)$r['status']] = (int)$r['c'];
            $counts['all'] += (int)$r['c'];
        }

        $where  = [];
        $params = [];
        if ($statusFilter !== '') {
            $where[]  = '`status` = :status';
            $params[':status'] = $statusFilter;
        }
        if ($search !== '') {
            $where[]  = '(`name` LIKE :q OR `email` LIKE :q OR `service` LIKE :q OR `message` LIKE :q)';
            $params[':q'] = '%' . $search . '%';
        }
        $whereSql = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $db->prepare("SELECT COUNT(*) FROM `contact_inquiries` {$whereSql}");
        $stmt->execute($params);
        $totalRows = (int)$stmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalRows / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare("
            SELECT id, name, email, phone, company, service, budget, message,
                   inquiry_type, reserved_at, reserved_zone, status,
                   replied_at, replied_by, created_at
            FROM `contact_inquiries` {$whereSql}
            ORDER BY `created_at` DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $inquiries = $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('Inquiries list query failed: ' . $e->getMessage());
    }
}

/** Build a filter URL preserving the search term. */
function admin_inq_url(?string $status, int $pageN = 1, ?string $q = null): string
{
    $params = [];
    if ($status !== null && $status !== '') { $params['status'] = $status; }
    if ($q !== null && $q !== '') { $params['q'] = $q; }
    if ($pageN > 1) { $params['page'] = $pageN; }
    return 'inquiries.php' . ($params !== [] ? '?' . http_build_query($params) : '');
}

require_once __DIR__ . '/includes/layout-header.php';
?>

<div class="admin-filters">
    <a class="admin-chip <?= $statusFilter === '' ? 'is-active' : '' ?>" href="<?= e(admin_inq_url('', 1, $search)) ?>">
        All <span class="admin-chip-count"><?= (int)$counts['all'] ?></span>
    </a>
    <a class="admin-chip <?= $statusFilter === 'new' ? 'is-active' : '' ?>" href="<?= e(admin_inq_url('new', 1, $search)) ?>">
        New <span class="admin-chip-count"><?= (int)$counts['new'] ?></span>
    </a>
    <a class="admin-chip <?= $statusFilter === 'read' ? 'is-active' : '' ?>" href="<?= e(admin_inq_url('read', 1, $search)) ?>">
        Read <span class="admin-chip-count"><?= (int)$counts['read'] ?></span>
    </a>
    <a class="admin-chip <?= $statusFilter === 'replied' ? 'is-active' : '' ?>" href="<?= e(admin_inq_url('replied', 1, $search)) ?>">
        Replied <span class="admin-chip-count"><?= (int)$counts['replied'] ?></span>
    </a>
    <a class="admin-chip <?= $statusFilter === 'archived' ? 'is-active' : '' ?>" href="<?= e(admin_inq_url('archived', 1, $search)) ?>">
        Archived <span class="admin-chip-count"><?= (int)$counts['archived'] ?></span>
    </a>

    <form method="get" action="inquiries.php" class="admin-filters-search">
        <?php if ($statusFilter !== ''): ?>
            <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
        <?php endif; ?>
        <input class="admin-input" type="text" name="q" placeholder="Search name, email, message…"
               value="<?= e($search) ?>" style="width:230px;">
        <button class="admin-btn admin-btn--secondary" type="submit"><i class="ri-search-line"></i></button>
        <?php if ($search !== ''): ?>
            <a class="admin-btn admin-btn--secondary" href="<?= e(admin_inq_url($statusFilter)) ?>"><i class="ri-close-line"></i></a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <div class="admin-card-body admin-card-body--flush">
        <?php if (empty($inquiries)): ?>
            <div class="admin-empty">
                <i class="ri-inbox-line"></i>
                <p><?= $search !== '' ? 'No inquiries match your search.' : 'No inquiries found.' ?></p>
            </div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Type</th>
                            <th>Scope</th>
                            <th>Status</th>
                            <th>Received</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($inquiries as $q): ?>
                        <tr>
                            <td>
                                <a href="inquiry.php?id=<?= (int)$q['id'] ?>"><?= e($q['name']) ?></a>
                                <span class="admin-sub"><?= e($q['email']) ?></span>
                            </td>
                            <td><span class="admin-badge admin-badge--<?= e($q['inquiry_type'] ?? 'contact') ?>"><?= e($q['inquiry_type'] ?? 'contact') ?></span></td>
                            <td>
                                <?= e($q['service'] ?? '—') ?>
                                <?php if (!empty($q['reserved_at'])): ?>
                                    <span class="admin-sub"><?= e(date('M d, Y H:i', strtotime((string)$q['reserved_at']))) ?> (<?= e($q['reserved_zone'] ?? 'Lagos') ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="admin-badge admin-badge--<?= e($q['status']) ?>"><?= e($q['status']) ?></span></td>
                            <td><?= e(date('M d, Y H:i', strtotime((string)$q['created_at']))) ?></td>
                            <td><a class="admin-btn admin-btn--secondary admin-btn--sm" href="inquiry.php?id=<?= (int)$q['id'] ?>">Open</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($totalPages > 1): ?>
    <div class="admin-filters" style="justify-content:center;">
        <?php if ($page > 1): ?>
            <a class="admin-chip" href="<?= e(admin_inq_url($statusFilter, $page - 1, $search)) ?>"><i class="ri-arrow-left-s-line"></i> Prev</a>
        <?php endif; ?>
        <span class="admin-chip is-active">Page <?= $page ?> / <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
            <a class="admin-chip" href="<?= e(admin_inq_url($statusFilter, $page + 1, $search)) ?>">Next <i class="ri-arrow-right-s-line"></i></a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>
