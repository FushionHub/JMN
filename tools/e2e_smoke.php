<?php
/**
 * End-to-end smoke test: cookie consent + tracker gating + contact form.
 * Run:  php tools/e2e_smoke.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$BASE = 'http://127.0.0.1:8099';
$JAR  = sys_get_temp_dir() . '/jmn_e2e_cookies.txt';
@unlink($JAR);

$failures = 0;

function out(string $msg): void { echo $msg, "\n"; }
function ok(bool $cond, string $label, string $detail = ''): void {
    global $failures;
    if ($cond) { out("  PASS  {$label}"); return; }
    $failures++;
    out("  FAIL  {$label}" . ($detail !== '' ? " :: {$detail}" : ''));
}

function req(string $method, string $url, array $opts = []): array {
    global $JAR;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_COOKIEJAR      => $JAR,
        CURLOPT_COOKIEFILE     => $JAR,
        CURLOPT_FOLLOWLOCATION => false,
    ]);
    if (isset($opts['body'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $opts['body']);
    }
    if (!empty($opts['headers'])) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $opts['headers']);
    }
    $raw  = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs   = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    return ['status' => $code, 'headers' => substr((string) $raw, 0, $hs), 'body' => substr((string) $raw, $hs)];
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
$pdo = get_db();
if (!$pdo instanceof PDO) { fwrite(STDERR, "no db\n"); exit(1); }

// ---------------------------------------------------------------------------
out("== 1. consent flow ==");
@unlink($JAR);
$home = req('GET', BASE_URL_TEST() . '/index.php');
ok($home['status'] === 200, 'GET /index.php -> 200', (string) $home['status']);

if (!preg_match('/id="jmn-cookie-csrf"\s+value="([^"]+)"/', $home['body'], $m)) {
    ok(false, 'csrf token present in banner');
    fwrite(STDERR, "cannot continue without csrf\n");
    exit(1);
}
$csrf = $m[1];
ok($csrf !== '', 'csrf token extracted');

$before = (int) $pdo->query('SELECT COUNT(*) FROM `visitor_traffic`')->fetchColumn();

$consent = req('POST', BASE_URL_TEST() . '/config/cookie_consent.php', [
    'body'    => json_encode([
        'consent_type'         => 'all',
        'analytics_enabled'    => 1,
        'preferences_enabled'  => 1,
        'csrf_token'           => $csrf,
    ]),
    'headers' => [
        'Content-Type: application/json',
        'X-Requested-With: XMLHttpRequest',
        'X-CSRF-Token: ' . $csrf,
    ],
]);
$consentJson = json_decode($consent['body'], true);
ok($consent['status'] === 200, 'POST cookie_consent -> 200', (string) $consent['status']);
ok(is_array($consentJson) && ($consentJson['success'] ?? false) === true, 'consent json success', $consent['body']);
ok(stripos($consent['headers'], 'jmn_cookie_consent=') !== false, 'jmn_cookie_consent cookie set');

$row = $pdo->query('SELECT * FROM `cookie_consents` ORDER BY `id` DESC LIMIT 1')->fetch();
ok($row !== false && (int) $row['analytics_enabled'] === 1, 'db row analytics_enabled=1');
ok($row !== false && (int) $row['preferences_enabled'] === 1, 'db row preferences_enabled=1');
ok($row !== false && (int) $row['essential_enabled'] === 1, 'db row essential_enabled=1');
ok($row !== false && (string) $row['consent_type'] === 'all', 'db row consent_type=all');

// After consent, a page view should be logged.
$home2 = req('GET', BASE_URL_TEST() . '/index.php');
$after = (int) $pdo->query('SELECT COUNT(*) FROM `visitor_traffic`')->fetchColumn();
ok($after > $before, "tracker logged a row after consent ({$before} -> {$after})");
ok($after - $before <= 2, 'no duplicate spam rows for same path', (string) ($after - $before));

// ---------------------------------------------------------------------------
out("== 2. consent = analytics off must NOT log ==");
@unlink($JAR);
$home3 = req('GET', BASE_URL_TEST() . '/index.php');
preg_match('/id="jmn-cookie-csrf"\s+value="([^"]+)"/', $home3['body'], $m2);
$csrf2 = $m2[1] ?? '';
$c2 = req('POST', BASE_URL_TEST() . '/config/cookie_consent.php', [
    'body'    => json_encode([
        'consent_type'        => 'necessary',
        'analytics_enabled'   => 0,
        'preferences_enabled' => 0,
        'csrf_token'          => $csrf2,
    ]),
    'headers' => [
        'Content-Type: application/json',
        'X-CSRF-Token: ' . $csrf2,
    ],
]);
ok($c2['status'] === 200, 'POST necessary-only consent -> 200', (string) $c2['status']);
$beforeN = (int) $pdo->query('SELECT COUNT(*) FROM `visitor_traffic`')->fetchColumn();
$dummy = req('GET', BASE_URL_TEST() . '/about.php');
ok($dummy['status'] === 200, 'GET /about.php -> 200', (string) $dummy['status']);
$afterN = (int) $pdo->query('SELECT COUNT(*) FROM `visitor_traffic`')->fetchColumn();
ok($afterN === $beforeN, "no visitor_traffic row without analytics consent ({$beforeN} -> {$afterN})");

// ---------------------------------------------------------------------------
out("== 3. CSRF rejection ==");
$bad = req('POST', BASE_URL_TEST() . '/config/cookie_consent.php', [
    'body'    => json_encode(['consent_type' => 'all', 'analytics_enabled' => 1, 'csrf_token' => 'not-a-real-token']),
    'headers' => ['Content-Type: application/json'],
]);
ok($bad['status'] === 419, 'cookie_consent rejects bad csrf with 419', (string) $bad['status']);

$get = req('GET', BASE_URL_TEST() . '/config/cookie_consent.php');
ok($get['status'] === 405, 'cookie_consent GET -> 405', (string) $get['status']);

echo "\n", $failures === 0 ? "ALL CONSENT/TRACKER TESTS PASSED" : "FAILURES: {$failures}", "\n";
exit($failures === 0 ? 0 : 1);

// Local helper: BASE_URL is derived from HTTP_HOST which CLI lacks.
function BASE_URL_TEST(): string { return 'http://127.0.0.1:8099'; }
