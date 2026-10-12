<?php
declare(strict_types=1);

/**
 * Admin Layout Footer — closes the document. Requires admin/assets/admin.js
 * for the sidebar toggle and any page-specific scripts ($adminScripts[]).
 * Self-hosted Chart.js is loaded in layout-header.php (in <head>, before any
 * inline body script) on pages that set $adminCharts = true.
 */

$adminScripts = $adminScripts ?? [];
?>
        </main>
    </div>
</div>

<?php $adminJsVer = file_exists(dirname(__DIR__) . '/assets/admin.js') ? (string)filemtime(dirname(__DIR__) . '/assets/admin.js') : '1.0'; ?>
<script src="<?= e(url('admin/assets/admin.js')) ?>?v=<?= e($adminJsVer) ?>"></script>
<?php foreach ($adminScripts as $src): ?>
<script src="<?= e($src) ?>"></script>
<?php endforeach; ?>
</body>
</html>
