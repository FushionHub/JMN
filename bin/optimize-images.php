<?php
declare(strict_types=1);

/**
 * Image optimizer — generates WebP variants alongside source rasters.
 *
 * Usage: php bin/optimize-images.php [--quality=80] [--min-kb=20]
 *
 * - Scans assets/img for .jpg/.jpeg/.png.
 * - Skips favicon/ (browsers fetch directly, not via templates), uploads/
 *   (dynamic user content), files below --min-kb, and sources whose .webp is
 *   already newer (re-runnable).
 * - Originals are NEVER modified or deleted; foo.jpg gains foo.webp next to it.
 * - Templates serve the .webp via <picture> with the original as fallback
 *   (see picture_img() in config/config.php), so every browser works.
 */

$quality = 80;
$minKb   = 20;
foreach (array_slice($argv ?? [], 1) as $arg) {
    if (str_starts_with($arg, '--quality=')) {
        $quality = max(1, min(100, (int)substr($arg, 10)));
    }
    if (str_starts_with($arg, '--min-kb=')) {
        $minKb = max(0, (int)substr($arg, 9));
    }
}

$root = dirname(__DIR__);
$imgRoot = $root . '/assets/img';
if (!is_dir($imgRoot)) {
    fwrite(STDERR, "assets/img not found\n");
    exit(1);
}

// CLI batch tool: huge sources (7MB+ PNG) need room for GD's raw bitmap.
ini_set('memory_limit', '512M');

$skipSegments = ['favicon', 'uploads'];
$files = [];
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($imgRoot, FilesystemIterator::SKIP_DOTS)
);
foreach ($it as $file) {
    /** @var SplFileInfo $file */
    if (!$file->isFile()) {
        continue;
    }
    $ext = strtolower($file->getExtension());
    if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
        continue;
    }
    $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($imgRoot) + 1));
    // Skip any file under a favicon/ or uploads/ segment at any depth
    // (favicons are fetched directly; uploads are dynamic user content).
    $segments = explode('/', $rel);
    foreach ($skipSegments as $skip) {
        if (in_array($skip, $segments, true)) {
            continue 2;
        }
    }
    if ($file->getSize() < $minKb * 1024) {
        continue;
    }
    $files[] = $file->getPathname();
}
sort($files);

$done = 0; $skipped = 0; $failed = 0;
$beforeTotal = 0; $afterTotal = 0;

foreach ($files as $src) {
    // Append-style naming (foo.jpg -> foo.jpg.webp) so same-basename sources
    // with different extensions (hero.jpg vs hero.png) never collide.
    $dst = $src . '.webp';
    if (is_file($dst)) {
        // Treat 0-byte/corrupt outputs as stale so crashes self-heal.
        $size = filesize($dst);
        if ($size === 0) {
            @unlink($dst);
        } elseif (filemtime($dst) >= filemtime($src)) {
            $skipped++;
            continue;
        }
    }
    $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION));
    $im = ($ext === 'png') ? @imagecreatefrompng($src) : @imagecreatefromjpeg($src);
    if ($im === false) {
        fwrite(STDERR, "FAIL decode: $src\n");
        $failed++;
        continue;
    }
    if ($ext === 'png') {
        imagepalettetotruecolor($im);
        imagealphablending($im, false);
        imagesavealpha($im, true);
    }
    $ok = @imagewebp($im, $dst, $quality);
    imagedestroy($im);
    if (!$ok || !is_file($dst) || filesize($dst) === 0) {
        @unlink($dst); // never leave partial/corrupt outputs behind
        fwrite(STDERR, "FAIL encode: $src\n");
        $failed++;
        continue;
    }
    $before = filesize($src);
    $after = filesize($dst);
    $beforeTotal += $before;
    $afterTotal += $after;
    $pct = $before > 0 ? round(100 * (1 - $after / $before)) : 0;
    printf(
        "%-55s %7dKB -> %7dKB  (-%d%%)\n",
        substr(str_replace('\\', '/', $src), -55),
        (int)round($before / 1024),
        (int)round($after / 1024),
        $pct
    );
    $done++;
}

echo str_repeat('-', 70) . "\n";
printf(
    "converted: %d  up-to-date: %d  failed: %d\n",
    $done,
    $skipped,
    $failed
);
if ($beforeTotal > 0) {
    printf(
        "total: %.1fMB -> %.1fMB  (-%d%%)\n",
        $beforeTotal / 1048576,
        $afterTotal / 1048576,
        (int)round(100 * (1 - $afterTotal / $beforeTotal))
    );
}
exit($failed > 0 ? 1 : 0);
