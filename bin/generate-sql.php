<?php
declare(strict_types=1);

/**
 * Generate a comprehensive, re-importable SQL file from the live database.
 *
 * Outputs database/jmn.sql containing:
 *   - DROP + CREATE for every table (idempotent re-import)
 *   - Seed INSERTs for the content tables (blogs, projects, services,
 *     testimonials, skills) so the site works immediately on a fresh import
 *   - The admin_users seed row (owner account)
 *
 * Runtime-only tables (visitor_traffic, cookie_consents, contact_inquiries,
 * indexnow_logs) get their schema but no data — that data is user-generated
 * and should not ship in a seed file.
 *
 * Usage: php bin/generate-sql.php
 */

require_once __DIR__ . '/../config/db.php';

$db = get_db();
if ($db === null) {
    fwrite(STDERR, "Could not connect to the database.\n");
    exit(1);
}

$tables = [
    'visitor_traffic',
    'cookie_consents',
    'contact_inquiries',
    'blogs',
    'projects',
    'services',
    'testimonials',
    'skills',
    'indexnow_logs',
    'admin_users',
];

// Tables whose full data is shipped as seed content.
$contentTables = ['blogs', 'projects', 'services', 'testimonials', 'skills', 'admin_users'];

$out = [];
$out[] = '-- ============================================================================';
$out[] = '-- JMN Portfolio - comprehensive database schema + seed content';
$out[] = '-- Generated: ' . date('Y-m-d H:i:s T');
$out[] = '--';
$out[] = '-- Re-importable: each table is DROPped then CREATEd, so running this file';
$out[] = '-- against an existing `jmn` database rebuilds it from scratch.';
$out[] = '--';
$out[] = '-- Content tables (blogs, projects, services, testimonials, skills) include';
$out[] = '-- their real seed rows so a fresh import renders the live site immediately.';
$out[] = '-- admin_users includes the seeded owner account (password is bcrypt of the';
$out[] = '-- ADMIN_PASSWORD env, default "ChangeMe!Admin2026").';
$out[] = '--';
$out[] = '-- Runtime tables (visitor_traffic, cookie_consents, contact_inquiries,';
$out[] = '-- indexnow_logs) ship schema only - their data is user-generated.';
$out[] = '-- ==========================================================================';
$out[] = '';
$out[] = 'SET NAMES utf8mb4;';
$out[] = 'SET FOREIGN_KEY_CHECKS = 0;';
$out[] = "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';";
$out[] = "SET time_zone = '+00:00';";
$out[] = '';

function sqlEscape(string $value): string
{
    return addslashes($value);
}

function quoteValue(mixed $value): string
{
    if ($value === null) {
        return 'NULL';
    }
    if (is_int($value) || is_float($value)) {
        return (string)$value;
    }
    return "'" . sqlEscape((string)$value) . "'";
}

foreach ($tables as $table) {
    // 1. Schema
    $row = $db->query("SHOW CREATE TABLE `$table`")->fetch();
    if (!is_array($row)) {
        fwrite(STDERR, "Skipping missing table: $table\n");
        continue;
    }
    $create = (string)($row['Create Table'] ?? '');

    $out[] = '-- ---------------------------------------------------------------------------';
    $out[] = '-- Table: ' . $table;
    $out[] = '-- ---------------------------------------------------------------------------';
    $out[] = "DROP TABLE IF EXISTS `$table`;";
    // Indent the CREATE statement body for readability.
    $out[] = rtrim($create, ';') . ';';
    $out[] = '';

    // 2. Seed data (content tables only).
    if (!in_array($table, $contentTables, true)) {
        continue;
    }

    $rows = $db->query("SELECT * FROM `$table`")->fetchAll();
    if (empty($rows)) {
        $out[] = '-- (no rows)';
        $out[] = '';
        continue;
    }

    $columns = array_keys($rows[0]);
    $colList = '`' . implode('`, `', $columns) . '`';

    $out[] = '-- Seed data for ' . $table . ' (' . count($rows) . ' rows)';
    foreach ($rows as $r) {
        $vals = [];
        foreach ($columns as $c) {
            $vals[] = quoteValue($r[$c]);
        }
        // Wrap long INSERTs at ~78 chars for readability.
        $line = "INSERT INTO `$table` ($colList) VALUES (" . implode(', ', $vals) . ');';
        $out[] = $line;
    }
    $out[] = '';
}

$out[] = 'SET FOREIGN_KEY_CHECKS = 1;';
$out[] = '';

$sql = implode("\n", $out) . "\n";

$target = dirname(__DIR__) . '/database';
if (!is_dir($target) && !@mkdir($target, 0755, true) && !is_dir($target)) {
    fwrite(STDERR, "Could not create the database/ directory.\n");
    exit(1);
}

$file = $target . '/jmn.sql';
file_put_contents($file, $sql);

$bytes = strlen($sql);
echo "Wrote $file (" . number_format($bytes) . " bytes, " . count($tables) . " tables)\n";
