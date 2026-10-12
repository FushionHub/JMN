<?php
declare(strict_types=1);

/**
 * Admin shared helpers — image upload validation shared by every editor
 * (blog, projects, services, …). Extracted from blog-edit.php so each new
 * admin section reuses the same finfo-validated, size-capped uploader.
 */

/**
 * Validate + move an uploaded image.
 *
 * @param array<string,mixed> $file   One entry of $_FILES
 * @param string              $subdir Upload folder under assets/img/ (e.g. 'blog/uploads')
 * @return string Relative path on success, array{error:string} on failure
 */
function admin_handle_image_upload(array $file, string $subdir = 'blog/uploads'): array|string
{
    $err = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($err === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if ($err !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload failed (code ' . $err . ').'];
    }

    $tmp  = (string)($file['tmp_name'] ?? '');
    $size = (int)($file['size'] ?? 0);
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return ['error' => 'Invalid upload.'];
    }
    if ($size <= 0 || $size > 5 * 1024 * 1024) {
        return ['error' => 'Image must be between 1 byte and 5 MB.'];
    }

    // Determine the real MIME type from the bytes, never from the client.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = (string)$finfo->file($tmp);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        'image/svg+xml' => 'svg',
    ];
    if (!isset($allowed[$mime])) {
        return ['error' => 'Unsupported image type (' . $mime . '). Allowed: JPG, PNG, WEBP, GIF, SVG.'];
    }
    $ext = $allowed[$mime];

    $subdir = trim($subdir, '/');
    if ($subdir === '' || str_contains($subdir, '..')) {
        $subdir = 'blog/uploads';
    }

    $uploadDir = dirname(__DIR__, 2) . '/assets/img/' . $subdir;
    if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        return ['error' => 'Could not create the upload directory.'];
    }

    $name = 'up-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $uploadDir . '/' . $name;
    if (!move_uploaded_file($tmp, $dest)) {
        return ['error' => 'Could not move the uploaded file.'];
    }
    @chmod($dest, 0644);

    return 'assets/img/' . $subdir . '/' . $name;
}

/**
 * Split a textarea (one value per line) into a clean string list.
 *
 * @return list<string>
 */
function admin_lines_to_list(string $raw): array
{
    $items = [];
    foreach (preg_split('/\R/', $raw) ?: [] as $line) {
        $line = trim($line);
        if ($line !== '') {
            $items[] = $line;
        }
    }
    return $items;
}

/**
 * Parse a "Key: Value" (or "Key=Value") per-line textarea into an assoc array.
 *
 * @return array<string,string>
 */
function admin_lines_to_map(string $raw): array
{
    $map = [];
    foreach (preg_split('/\R/', $raw) ?: [] as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $parts = preg_split('/\s*[:=]\s*/', $line, 2);
        if (is_array($parts) && count($parts) === 2 && trim($parts[0]) !== '') {
            $map[trim($parts[0])] = trim($parts[1]);
        }
    }
    return $map;
}
