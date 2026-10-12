<?php
declare(strict_types=1);

/**
 * Admin Layout Header — opens the document, sidebar and topbar.
 * Every admin page sets $adminTitle before requiring this file.
 */

require_once __DIR__ . '/auth.php';
admin_require_auth();

// The admin must never be indexed. send_security_headers() already sent an
// index/follow X-Robots-Tag; replace it before any body output.
if (!headers_sent()) {
    header_remove('X-Robots-Tag');
    header('X-Robots-Tag: noindex, nofollow, noarchive');
}

$adminTitle = $adminTitle ?? 'Dashboard';
$adminUser  = admin_current_user();
$adminNav   = $adminNav ?? '';   // active sidebar key, e.g. 'dashboard'

/** Current admin path segment for active-link detection. */
$adminScript = basename($_SERVER['SCRIPT_NAME'] ?? '');

$flashes = [];
foreach (['ok', 'err', 'msg'] as $k) {
    if (!empty($_SESSION['flash_' . $k])) {
        $flashes[] = ['type' => $k, 'text' => (string)$_SESSION['flash_' . $k]];
        unset($_SESSION['flash_' . $k]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($adminTitle) ?> | JMN Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">
    <?php $adminCssVer = file_exists(dirname(__DIR__) . '/assets/admin.css') ? (string)filemtime(dirname(__DIR__) . '/assets/admin.css') : '1.0'; ?>
    <link rel="stylesheet" href="<?= e(url('admin/assets/admin.css')) ?>?v=<?= e($adminCssVer) ?>">
    <?php if ($adminCharts ?? false): ?>
    <!-- Self-hosted Chart.js: loaded in <head> so it is available to any
         inline chart-init script that runs in the page body. -->
    <script src="<?= e(url('admin/assets/chart.umd.min.js')) ?>"></script>
    <?php endif; ?>
</head>
<body class="admin-body">
<div class="admin-shell">

    <!-- Mobile Drawer Backdrop -->
    <div class="admin-sidebar-backdrop" id="adminSidebarBackdrop" aria-hidden="true"></div>

    <!-- ── Sidebar ─────────────────────────────────────────────────── -->
    <aside class="admin-sidebar" id="adminSidebar" aria-label="Admin Navigation">
        <div class="admin-brand">
            <span class="admin-brand-mark">JMN</span>
            <span class="admin-brand-text">Admin</span>
            <button type="button" class="admin-sidebar-close" id="adminSidebarClose" aria-label="Close sidebar">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <nav class="admin-nav">
            <a href="index.php" class="<?= $adminNav === 'dashboard' ? 'is-active' : '' ?>">
                <i class="ri-dashboard-3-line"></i><span>Dashboard</span>
            </a>
            <a href="inquiries.php" class="<?= $adminNav === 'inquiries' ? 'is-active' : '' ?>">
                <i class="ri-inbox-2-line"></i><span>Inquiries</span>
            </a>
            <a href="blog.php" class="<?= $adminNav === 'blog' ? 'is-active' : '' ?>">
                <i class="ri-article-line"></i><span>Blog</span>
            </a>
            <a href="projects.php" class="<?= $adminNav === 'projects' ? 'is-active' : '' ?>">
                <i class="ri-folder-chart-line"></i><span>Projects</span>
            </a>
            <a href="services.php" class="<?= $adminNav === 'services' ? 'is-active' : '' ?>">
                <i class="ri-stack-line"></i><span>Services</span>
            </a>
            <a href="testimonials.php" class="<?= $adminNav === 'testimonials' ? 'is-active' : '' ?>">
                <i class="ri-chat-quote-line"></i><span>Testimonials</span>
            </a>
            <a href="skills.php" class="<?= $adminNav === 'skills' ? 'is-active' : '' ?>">
                <i class="ri-bar-chart-box-line"></i><span>Skills</span>
            </a>
            <a href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener" class="admin-nav-external">
                <i class="ri-external-link-line"></i><span>View Site</span>
            </a>
        </nav>
        <div class="admin-sidebar-foot">
            <div class="admin-user-chip">
                <i class="ri-shield-user-line"></i>
                <div>
                    <strong><?= e($adminUser['display_name'] ?? 'Admin') ?></strong>
                    <small><?= e($adminUser['role'] ?? 'owner') ?></small>
                </div>
            </div>
            <a href="settings.php" class="<?= $adminNav === 'settings' ? 'is-active' : '' ?>">
                <i class="ri-settings-3-line"></i><span>Settings</span>
            </a>
            <a href="logout.php"><i class="ri-logout-box-r-line"></i><span>Log out</span></a>
        </div>
    </aside>

    <!-- ── Main ────────────────────────────────────────────────────── -->
    <div class="admin-main">
        <header class="admin-topbar">
            <button type="button" class="admin-burger" id="adminBurger" aria-label="Toggle navigation">
                <i class="ri-menu-line"></i>
            </button>
            <h1 class="admin-topbar-title"><?= e($adminTitle) ?></h1>
            <div class="admin-topbar-spacer"></div>
            <div class="admin-topbar-actions">
                <a href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener" class="admin-btn admin-btn--secondary admin-btn--sm" title="View public site">
                    <i class="ri-external-link-line"></i> <span class="admin-topbar-btn-text">View Site</span>
                </a>
                <a href="logout.php" class="admin-btn admin-btn--secondary admin-btn--sm" title="Log out">
                    <i class="ri-logout-box-r-line"></i> <span class="admin-topbar-btn-text">Logout</span>
                </a>
            </div>
        </header>

        <main class="admin-content">
            <?php foreach ($flashes as $f): ?>
                <div class="admin-flash admin-flash--<?= e($f['type']) ?>">
                    <i class="ri-<?= $f['type'] === 'ok' ? 'checkbox-circle-fill' : ($f['type'] === 'err' ? 'error-warning-fill' : 'information-fill') ?>"></i>
                    <span><?= e($f['text']) ?></span>
                </div>
            <?php endforeach; ?>
