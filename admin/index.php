<?php
declare(strict_types=1);

/**
 * Admin Dashboard — analytics charts, counts, recent inquiries, recent posts.
 * Uses self-hosted Chart.js for the analytics visualisations.
 */

$adminTitle  = 'Dashboard';
$adminNav    = 'dashboard';
$adminCharts = true;   // load self-hosted Chart.js in the layout footer

require_once __DIR__ . '/includes/layout-header.php';
require_once dirname(__DIR__) . '/config/db.php';

$db = get_db();

$stats = [
    'new'        => 0,
    'read'       => 0,
    'replied'    => 0,
    'archived'   => 0,
    'total'      => 0,
    'published'  => 0,
    'draft'      => 0,
    'month'      => 0,
    'projects'   => 0,
    'services'   => 0,
    'testimonials' => 0,
    'skills'     => 0,
];
$recentInquiries = [];
$recentPosts     = [];

// ── Chart datasets ────────────────────────────────────────────────────
$chartTrendLabels = [];
$chartTrendInq    = [];
$chartTrendViews  = [];
$chartStatus      = ['new' => 0, 'read' => 0, 'replied' => 0, 'archived' => 0];
$chartTypeLabels  = [];
$chartTypeCounts  = [];

if ($db !== null) {
    try {
        $rows = $db->query("SELECT status, COUNT(*) c FROM `contact_inquiries` GROUP BY status")->fetchAll();
        foreach ($rows as $r) {
            $status = (string)$r['status'];
            $stats[$status] = (int)$r['c'];
            if (array_key_exists($status, $chartStatus)) {
                $chartStatus[$status] = (int)$r['c'];
            }
        }
        $stats['total'] = array_sum($stats);

        $stats['published'] = (int)$db->query("SELECT COUNT(*) FROM `blogs` WHERE `status` = 'published'")->fetchColumn();
        $stats['draft']     = (int)$db->query("SELECT COUNT(*) FROM `blogs` WHERE `status` = 'draft'")->fetchColumn();
        $stats['month']     = (int)$db->query("SELECT COUNT(*) FROM `contact_inquiries` WHERE `created_at` >= DATE_FORMAT(NOW(), '%Y-%m-01')")->fetchColumn();

        $stats['projects']     = (int)$db->query('SELECT COUNT(*) FROM `projects`')->fetchColumn();
        $stats['services']     = (int)$db->query('SELECT COUNT(*) FROM `services`')->fetchColumn();
        $stats['testimonials'] = (int)$db->query('SELECT COUNT(*) FROM `testimonials`')->fetchColumn();
        $stats['skills']       = (int)$db->query('SELECT COUNT(*) FROM `skills`')->fetchColumn();

        $recentInquiries = $db->query(
            "SELECT id, name, email, service, inquiry_type, status, created_at
             FROM `contact_inquiries` ORDER BY `created_at` DESC LIMIT 6"
        )->fetchAll();

        $recentPosts = $db->query(
            "SELECT id, slug, title, status, published_at FROM `blogs`
             ORDER BY `updated_at` DESC LIMIT 6"
        )->fetchAll();

        // Trend: inquiries + consented page views, last 30 days.
        $inqMap = [];
        foreach ($db->query(
            "SELECT DATE(`created_at`) d, COUNT(*) c FROM `contact_inquiries`
             WHERE `created_at` >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
             GROUP BY DATE(`created_at`)"
        )->fetchAll() as $r) {
            $inqMap[(string)$r['d']] = (int)$r['c'];
        }
        $viewMap = [];
        foreach ($db->query(
            "SELECT DATE(`visited_at`) d, COUNT(*) c FROM `visitor_traffic`
             WHERE `visited_at` >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
             GROUP BY DATE(`visited_at`)"
        )->fetchAll() as $r) {
            $viewMap[(string)$r['d']] = (int)$r['c'];
        }
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime('-' . $i . ' days'));
            $chartTrendLabels[] = date('M d', strtotime($date));
            $chartTrendInq[]    = $inqMap[$date] ?? 0;
            $chartTrendViews[]  = $viewMap[$date] ?? 0;
        }

        // Inquiries by type.
        foreach ($db->query(
            "SELECT `inquiry_type`, COUNT(*) c FROM `contact_inquiries`
             GROUP BY `inquiry_type` ORDER BY c DESC"
        )->fetchAll() as $r) {
            $chartTypeLabels[] = (string)$r['inquiry_type'];
            $chartTypeCounts[] = (int)$r['c'];
        }
    } catch (Throwable $e) {
        error_log('Dashboard query failed: ' . $e->getMessage());
    }
}

$contentLabels = ['Projects', 'Services', 'Testimonials', 'Skills', 'Published', 'Drafts'];
$contentCounts = [
    (int)$stats['projects'],
    (int)$stats['services'],
    (int)$stats['testimonials'],
    (int)$stats['skills'],
    (int)$stats['published'],
    (int)$stats['draft'],
];

$chartPayload = [
    'trend'   => ['labels' => $chartTrendLabels, 'inquiries' => $chartTrendInq, 'views' => $chartTrendViews],
    'status'  => ['labels' => ['new', 'read', 'replied', 'archived'], 'counts' => array_values($chartStatus)],
    'type'    => ['labels' => $chartTypeLabels, 'counts' => $chartTypeCounts],
    'content' => ['labels' => $contentLabels, 'counts' => $contentCounts],
];
?>

<div class="admin-stats">
    <div class="admin-stat">
        <div class="admin-stat-icon admin-stat-icon--blue"><i class="ri-inbox-2-line"></i></div>
        <div>
            <div class="admin-stat-value"><?= (int)$stats['total'] ?></div>
            <div class="admin-stat-label">Total inquiries</div>
        </div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon admin-stat-icon--red"><i class="ri-mail-unread-line"></i></div>
        <div>
            <div class="admin-stat-value"><?= (int)$stats['new'] ?></div>
            <div class="admin-stat-label">Unread</div>
        </div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon admin-stat-icon--green"><i class="ri-mail-send-line"></i></div>
        <div>
            <div class="admin-stat-value"><?= (int)$stats['replied'] ?></div>
            <div class="admin-stat-label">Replied</div>
        </div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon admin-stat-icon--amber"><i class="ri-calendar-line"></i></div>
        <div>
            <div class="admin-stat-value"><?= (int)$stats['month'] ?></div>
            <div class="admin-stat-label">This month</div>
        </div>
    </div>
</div>

<!-- ── Analytics charts ─────────────────────────────────────────────── -->
<div class="admin-section-label admin-section-label--accent">Analytics overview</div>

<div class="admin-chart-grid">
    <div class="admin-card admin-chart-card admin-chart-grid--trend">
        <div class="admin-card-head">
            <div>
                <h2>Inquiries &amp; page views</h2>
                <p class="admin-chart-sub">Last 30 days · page views recorded only with Analytics consent</p>
            </div>
            <span class="admin-badge admin-badge--contact"><i class="ri-line-chart-line"></i> Trend</span>
        </div>
        <div class="admin-card-body">
            <div class="admin-chart-canvas-wrap">
                <canvas id="chartTrend"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="admin-chart-grid">
    <div class="admin-card admin-chart-card">
        <div class="admin-card-head"><h2>Inquiry status</h2></div>
        <div class="admin-card-body">
            <div class="admin-chart-canvas-wrap admin-chart-canvas-wrap--sm">
                <canvas id="chartStatus"></canvas>
            </div>
        </div>
    </div>
    <div class="admin-card admin-chart-card">
        <div class="admin-card-head"><h2>Inquiries by type</h2></div>
        <div class="admin-card-body">
            <div class="admin-chart-canvas-wrap admin-chart-canvas-wrap--sm">
                <canvas id="chartType"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="admin-chart-grid">
    <div class="admin-card admin-chart-card admin-chart-grid--trend">
        <div class="admin-card-head">
            <div>
                <h2>Content overview</h2>
                <p class="admin-chart-sub">Published portfolio &amp; blog inventory</p>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="admin-chart-canvas-wrap">
                <canvas id="chartContent"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ── Content quick links ──────────────────────────────────────────── -->
<div class="admin-section-label">Manage content</div>
<div class="admin-stats">
    <a class="admin-stat" href="blog.php">
        <div class="admin-stat-icon admin-stat-icon--amber"><i class="ri-article-line"></i></div>
        <div>
            <div class="admin-stat-value"><?= (int)$stats['published'] + (int)$stats['draft'] ?></div>
            <div class="admin-stat-label">Blog posts</div>
        </div>
    </a>
    <a class="admin-stat" href="projects.php">
        <div class="admin-stat-icon admin-stat-icon--green"><i class="ri-folder-chart-line"></i></div>
        <div>
            <div class="admin-stat-value"><?= (int)$stats['projects'] ?></div>
            <div class="admin-stat-label">Projects</div>
        </div>
    </a>
    <a class="admin-stat" href="services.php">
        <div class="admin-stat-icon admin-stat-icon--amber"><i class="ri-stack-line"></i></div>
        <div>
            <div class="admin-stat-value"><?= (int)$stats['services'] ?></div>
            <div class="admin-stat-label">Services</div>
        </div>
    </a>
    <a class="admin-stat" href="testimonials.php">
        <div class="admin-stat-icon admin-stat-icon--red"><i class="ri-chat-quote-line"></i></div>
        <div>
            <div class="admin-stat-value"><?= (int)$stats['testimonials'] ?></div>
            <div class="admin-stat-label">Testimonials</div>
        </div>
    </a>
    <a class="admin-stat" href="skills.php">
        <div class="admin-stat-icon admin-stat-icon--blue"><i class="ri-bar-chart-box-line"></i></div>
        <div>
            <div class="admin-stat-value"><?= (int)$stats['skills'] ?></div>
            <div class="admin-stat-label">Skills</div>
        </div>
    </a>
</div>

<div class="admin-dashboard-grid">
    <!-- Recent inquiries -->
    <div class="admin-card">
        <div class="admin-card-head">
            <h2>Recent inquiries</h2>
            <a class="admin-btn admin-btn--secondary admin-btn--sm" href="inquiries.php">View all</a>
        </div>
        <div class="admin-card-body admin-card-body--flush">
            <?php if (empty($recentInquiries)): ?>
                <div class="admin-empty"><i class="ri-inbox-line"></i><p>No inquiries yet.</p></div>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>From</th><th>Type</th><th>Status</th><th>Date</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($recentInquiries as $q): ?>
                            <tr>
                                <td>
                                    <a href="inquiry.php?id=<?= (int)$q['id'] ?>"><?= e($q['name']) ?></a>
                                    <span class="admin-sub"><?= e($q['email']) ?></span>
                                </td>
                                <td><span class="admin-badge admin-badge--<?= e($q['inquiry_type'] ?? 'contact') ?>"><?= e($q['inquiry_type'] ?? 'contact') ?></span></td>
                                <td><span class="admin-badge admin-badge--<?= e($q['status']) ?>"><?= e($q['status']) ?></span></td>
                                <td><?= e(date('M d, H:i', strtotime((string)$q['created_at']))) ?></td>
                                <td><a class="admin-btn admin-btn--secondary admin-btn--sm" href="inquiry.php?id=<?= (int)$q['id'] ?>">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent posts -->
    <div class="admin-card">
        <div class="admin-card-head">
            <h2>Recent posts</h2>
            <a class="admin-btn admin-btn--primary admin-btn--sm" href="blog-edit.php"><i class="ri-add-line"></i> New</a>
        </div>
        <div class="admin-card-body admin-card-body--flush">
            <?php if (empty($recentPosts)): ?>
                <div class="admin-empty"><i class="ri-article-line"></i><p>No posts yet.</p></div>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Title</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($recentPosts as $p): ?>
                            <tr>
                                <td>
                                    <a href="blog-edit.php?id=<?= (int)$p['id'] ?>"><?= e($p['title']) ?></a>
                                    <span class="admin-sub"><?= e($p['slug']) ?></span>
                                </td>
                                <td><span class="admin-badge admin-badge--<?= e($p['status']) ?>"><?= e($p['status']) ?></span></td>
                                <td><a class="admin-btn admin-btn--secondary admin-btn--sm" href="blog-edit.php?id=<?= (int)$p['id'] ?>">Edit</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';
    var data = <?= json_encode($chartPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    if (typeof Chart === 'undefined') { return; }

    Chart.defaults.font.family = "'Montserrat', sans-serif";
    Chart.defaults.color = '#777';
    Chart.defaults.font.size = 11;

    var ACCENT = '#f41a4a';
    var INK    = '#111a24';
    var MUTED  = '#777';
    var PALETTE = ['#f41a4a', '#111a24', '#2e90fa', '#12b76a', '#f79009', '#5925dc'];

    function fade(hex, alpha) {
        var n = parseInt(hex.slice(1), 16);
        return 'rgba(' + (n >> 16) + ',' + ((n >> 8) & 255) + ',' + (n & 255) + ',' + alpha + ')';
    }

    // 1. Trend line (inquiries + page views)
    var tctx = document.getElementById('chartTrend');
    if (tctx) {
        new Chart(tctx, {
            type: 'line',
            data: {
                labels: data.trend.labels,
                datasets: [
                    {
                        label: 'Inquiries',
                        data: data.trend.inquiries,
                        borderColor: ACCENT,
                        backgroundColor: fade(ACCENT, 0.10),
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 5
                    },
                    {
                        label: 'Page views',
                        data: data.trend.views,
                        borderColor: INK,
                        backgroundColor: fade(INK, 0.06),
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 5
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top', align: 'end', labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8 } } },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } },
                    y: { beginAtZero: true, grid: { color: '#eae8ef' }, ticks: { precision: 0 } }
                }
            }
        });
    }

    // 2. Status doughnut
    var sctx = document.getElementById('chartStatus');
    if (sctx) {
        new Chart(sctx, {
            type: 'doughnut',
            data: {
                labels: data.status.labels,
                datasets: [{
                    data: data.status.counts,
                    backgroundColor: PALETTE,
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, padding: 14 } } }
            }
        });
    }

    // 3. Type doughnut
    var yctx = document.getElementById('chartType');
    if (yctx) {
        if (data.type.labels.length === 0) {
            yctx.parentNode.innerHTML = '<div class="admin-empty"><i class="ri-donut-chart-line"></i><p>No inquiries yet.</p></div>';
        } else {
            new Chart(yctx, {
                type: 'doughnut',
                data: {
                    labels: data.type.labels,
                    datasets: [{
                        data: data.type.counts,
                        backgroundColor: [ACCENT, INK, '#2e90fa', '#12b76a'],
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '62%',
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, padding: 14 } } }
                }
            });
        }
    }

    // 4. Content bar
    var cctx = document.getElementById('chartContent');
    if (cctx) {
        new Chart(cctx, {
            type: 'bar',
            data: {
                labels: data.content.labels,
                datasets: [{
                    label: 'Items',
                    data: data.content.counts,
                    backgroundColor: PALETTE,
                    borderRadius: 8,
                    maxBarThickness: 56
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: { color: '#eae8ef' }, ticks: { precision: 0 } }
                }
            }
        });
    }
})();
</script>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>
