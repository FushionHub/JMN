<?php
declare(strict_types=1);

/**
 * File-based data cache.
 *
 * Cuts repetitive MySQL reads on public pages (every get_all_*() otherwise
 * runs a seed COUNT + full SELECT per HTTP request). Stored under
 * storage/cache/ as serialized payloads with an expiry timestamp.
 *
 * Safety rules:
 *  - Content rows only. Never cache per-session, per-user, CSRF, consent or
 *    tracker state. Cached HTML must never be served (CSRF tokens are
 *    per-session and would break forms if shared).
 *  - Fail-open: any unreadable/corrupt cache file is treated as a miss.
 *  - Admin content saves call cache_clear() so edits go live immediately;
 *    the TTL is only a backstop.
 */

if (!defined('DATA_CACHE_TTL')) {
    /** Seconds a data-cache entry is considered fresh (5 minutes). */
    define('DATA_CACHE_TTL', 300);
}

/**
 * Resolve the cache directory, creating it on demand.
 */
function cache_dir(): string
{
    $root = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__);
    $dir  = $root . '/storage/cache';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir;
}

/**
 * Fetch a cached value. Returns null on miss, expiry or corruption.
 *
 * @return mixed
 */
function cache_get(string $key)
{
    $file = cache_dir() . '/' . preg_replace('/[^a-z0-9_\-]/i', '', $key) . '.cache';
    if (!is_file($file)) {
        return null;
    }
    $raw = @file_get_contents($file);
    if ($raw === false) {
        return null;
    }
    $payload = @unserialize($raw, ['allowed_classes' => false]);
    if (!is_array($payload) || !isset($payload['expires'], $payload['value'])) {
        @unlink($file);
        return null;
    }
    if ((int)$payload['expires'] < time()) {
        @unlink($file);
        return null;
    }
    return $payload['value'];
}

/**
 * Store a value in the cache. Silently no-ops when storage is unavailable.
 *
 * @param mixed $value Must be serializable (arrays/scalars only — no objects,
 *                     enforced via allowed_classes=false on read).
 */
function cache_set(string $key, $value, int $ttl = DATA_CACHE_TTL): void
{
    $dir = cache_dir();
    if (!is_dir($dir)) {
        return;
    }
    $file = $dir . '/' . preg_replace('/[^a-z0-9_\-]/i', '', $key) . '.cache';
    $payload = serialize(['expires' => time() + max(1, $ttl), 'value' => $value]);
    @file_put_contents($file, $payload, LOCK_EX);
}

/**
 * Clear cached entries. With no argument, clears all data-cache files.
 * Called by admin save handlers so content edits go live immediately.
 */
function cache_clear(?string $key = null): void
{
    $dir = cache_dir();
    if (!is_dir($dir)) {
        return;
    }
    if ($key !== null) {
        @unlink($dir . '/' . preg_replace('/[^a-z0-9_\-]/i', '', $key) . '.cache');
        return;
    }
    foreach ((array)@glob($dir . '/*.cache') as $file) {
        @unlink($file);
    }
}
