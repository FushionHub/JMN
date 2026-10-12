<?php
/**
 * End-to-end smoke test for the contact / reservation mailer.
 * Run:  php tools/e2e_mailer.php
 *
 * Notes on the two response modes under test:
 *   - JSON/AJAX clients (X-Requested-With: XMLHttpRequest) get JSON bodies.
 *   - Classic browser form posts get a 302 back to a same-site URL with a
 *     contact_status= query flag; hostile return_to values are dropped.
 *
 * check_rate_limit('contact_form', 5) is a 5-second minimum interval, so the
 * test spaces its submissions out and clears the per-IP buckets first.
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$BASE = 'http://127.0.0.1:8099';
$JAR  = sys_get_temp_dir() . '/jmn_mailer_cookies.txt';
@unlink($JAR);

$root = dirname(__DIR__);
foreach (glob($root . '/storage/ratelimit/*.json') ?: [] as $f) {
    @unlink($f);
}

$failures = 0;
function ok(bool $c, string $label, string $detail = ''): void {
    global $failures;
    if ($c) { echo "  PASS  {$label}\n"; return; }
    $failures++;
    echo "  FAIL  {$label}" . ($detail !== '' ? " :: {$detail}" : '') . "\n";
}

function req(string $method, string $url, array $opts = []): array {
    global $JAR;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_TIMEOUT        => 45,
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

function post_json(string $url, array $payload): array {
    return req('POST', $url, [
        'body'    => json_encode($payload),
        'headers' => [
            'Content-Type: application/json',
            'X-Requested-With: XMLHttpRequest',
            'Accept: application/json',
        ],
    ]);
}

function post_form(string $url, array $payload): array {
    return req('POST', $url, [
        'body'    => http_build_query($payload),
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
}

function location_of(array $res): string {
    if (preg_match('/^Location:\s*(.+)$/mi', $res['headers'], $m)) {
        return trim($m[1]);
    }
    return '';
}

require_once $root . '/config/config.php';
require_once $root . '/config/db.php';
$pdo = get_db();
if (!$pdo instanceof PDO) { fwrite(STDERR, "no db\n"); exit(1); }

// ---------------------------------------------------------------------------
echo "== 1. method + CSRF guards ==\n";
$get = req('GET', $BASE . '/config/mailer.php');
ok($get['status'] === 405, 'GET -> 405', (string) $get['status']);

$noCsrf = post_json($BASE . '/config/mailer.php', [
    'name' => 'A', 'Email' => 'a@example.com', 'your-comment' => 'hi',
]);
ok($noCsrf['status'] === 419, 'POST without csrf -> 419', (string) $noCsrf['status']);

// ---------------------------------------------------------------------------
echo "== 2. obtain a live CSRF token ==\n";
$contact = req('GET', $BASE . '/contact.php');
ok($contact['status'] === 200, 'GET /contact.php -> 200', (string) $contact['status']);
if (!preg_match('/csrf_token" value="([^"]+)"/', $contact['body'], $m)) {
    ok(false, 'csrf field found on contact form');
    exit(1);
}
$csrf = $m[1];
ok($csrf !== '', 'csrf token extracted');
ok(str_contains($contact['body'], 'id="contact-tab-reserve"') || str_contains($contact['body'], 'reservation'), 'reservation tab present');

$beforeRows = (int) $pdo->query('SELECT COUNT(*) FROM `contact_inquiries`')->fetchColumn();
$logBefore  = is_file($root . '/storage/logs/inquiries.log') ? (string) filesize($root . '/storage/logs/inquiries.log') : '0';

// ---------------------------------------------------------------------------
echo "== 3. honeypot (silent accept, no row) ==\n";
$hp = post_json($BASE . '/config/mailer.php', [
    'name' => 'Bot', 'Email' => 'bot@example.com', 'your-comment' => 'buy now',
    'website_hp' => 'http://spam.example', 'inquiry_type' => 'contact',
    'return_to' => '/index.php', 'csrf_token' => $csrf,
]);
$hpJson = json_decode($hp['body'], true);
ok($hp['status'] === 200, 'honeypot -> 200', (string) $hp['status'] . ' ' . $hp['body']);
ok(($hpJson['success'] ?? false) === true, 'honeypot fake success', $hp['body']);
$afterHp = (int) $pdo->query('SELECT COUNT(*) FROM `contact_inquiries`')->fetchColumn();
ok($afterHp === $beforeRows, "honeypot wrote no row ({$beforeRows} -> {$afterHp})");

sleep(6);

// ---------------------------------------------------------------------------
echo "== 4. validation -> 422 ==\n";
$invalid = post_json($BASE . '/config/mailer.php', [
    'name' => '', 'Email' => 'not-an-email', 'your-comment' => '',
    'website_hp' => '', 'inquiry_type' => 'contact', 'return_to' => '/index.php',
    'csrf_token' => $csrf,
]);
$invJson = json_decode($invalid['body'], true);
ok($invalid['status'] === 422, 'empty/invalid fields -> 422', (string) $invalid['status'] . ' ' . $invalid['body']);
ok(!empty($invJson['errors']), 'error list returned', $invalid['body']);

sleep(6);

// ---------------------------------------------------------------------------
echo "== 5. valid general inquiry (hostile return_to) ==\n";
$inquiry = post_json($BASE . '/config/mailer.php', [
    'Name' => 'Ada Example', 'Email' => 'ada@example.com',
    'phone' => '+2348120000000', 'company' => 'Example Ltd',
    'service' => 'AI & Autonomous Agent Pipelines',
    'offer_method' => 'Full-Stack SaaS & Web Platforms',
    'budget' => 'NGN 5,000,000 - 10,000,000',
    'your-comment' => "We need a RAG pipeline over 40k support docs.\nSecond line with <script>alert(1)</script>.",
    'website_hp' => '', 'inquiry_type' => 'contact',
    'return_to' => 'https://evil.example/steal',
    'csrf_token' => $csrf,
]);
$inqJson = json_decode($inquiry['body'], true);
ok($inquiry['status'] === 200, 'valid inquiry -> 200', (string) $inquiry['status'] . ' ' . $inquiry['body']);
ok(($inqJson['success'] ?? false) === true, 'inquiry success=true', $inquiry['body']);
ok(!empty($inqJson['inquiry_id']), 'inquiry_id returned', $inquiry['body']);
ok(!empty($inqJson['new_csrf_token']), 'csrf rotated', $inquiry['body']);
// Note: the AJAX success payload intentionally carries no 'redirect' key -
// includes/footer.php shows the message in-page and resets the form, so the
// browser never navigates. The hostile return_to is only consumed on the
// classic 302 path, which section 9 asserts.

$newId = (int) ($inqJson['inquiry_id'] ?? 0);
$row   = $newId > 0 ? $pdo->query("SELECT * FROM `contact_inquiries` WHERE `id` = {$newId}")->fetch() : false;
ok($row !== false, 'db row written');
if ($row !== false) {
    ok((string) $row['inquiry_type'] === 'contact', 'inquiry_type=contact', (string) $row['inquiry_type']);
    ok((string) $row['name'] === 'Ada Example', 'name stored', (string) $row['name']);
    ok((string) $row['service'] === 'Full-Stack SaaS & Web Platforms', 'offer_method preferred over service', (string) $row['service']);
    ok(str_contains((string) $row['message'], '<script>'), 'message preserved (escaped only on output)');
    ok($row['reserved_at'] === null, 'reserved_at null for contact', var_export($row['reserved_at'], true));
}

$logNow = '0';
if (is_file($root . '/storage/logs/inquiries.log')) {
    clearstatcache(true, $root . '/storage/logs/inquiries.log');
    $logNow = (string) filesize($root . '/storage/logs/inquiries.log');
}
ok((int) $logNow > (int) $logBefore, "disk audit log appended ({$logBefore} -> {$logNow})");

sleep(6);

// ---------------------------------------------------------------------------
echo "== 6. valid reservation ==\n";
$csrf2 = (string) ($inqJson['new_csrf_token'] ?? $csrf);
$date  = date('Y-m-d', strtotime('+7 days'));
$res   = post_json($BASE . '/config/mailer.php', [
    'Name' => 'Bola Client', 'Email' => 'bola@example.com',
    'phone' => '+2348130000000', 'company' => 'Client Co',
    'inquiry_type' => 'reservation',
    'reserved_date' => $date, 'reserved_time' => '14:30', 'reserved_zone' => 'Africa/Lagos (WAT, UTC+1)',
    'budget' => 'Under NGN 1,000,000',
    'your-comment' => '60-minute architecture review of our escrow ledger.',
    'website_hp' => '', 'return_to' => '/contact.php',
    'csrf_token' => $csrf2,
]);
$resJson = json_decode($res['body'], true);
ok($res['status'] === 200, 'valid reservation -> 200', (string) $res['status'] . ' ' . $res['body']);
ok(($resJson['success'] ?? false) === true, 'reservation success=true', $res['body']);

$newId2 = (int) ($resJson['inquiry_id'] ?? 0);
$row2   = $newId2 > 0 ? $pdo->query("SELECT * FROM `contact_inquiries` WHERE `id` = {$newId2}")->fetch() : false;
ok($row2 !== false && (string) $row2['inquiry_type'] === 'reservation', 'inquiry_type=reservation');
ok($row2 !== false && (string) $row2['reserved_at'] === $date . ' 14:30:00', 'reserved_at stored', (string) ($row2['reserved_at'] ?? 'null'));
ok($row2 !== false && (string) $row2['reserved_zone'] === 'Africa/Lagos (WAT, UTC+1)', 'reserved_zone stored', (string) ($row2['reserved_zone'] ?? ''));

sleep(6);

// ---------------------------------------------------------------------------
echo "== 7. reservation with a past date -> 422 ==\n";
$csrf3 = (string) ($resJson['new_csrf_token'] ?? $csrf2);
$past  = post_json($BASE . '/config/mailer.php', [
    'Name' => 'Past Client', 'Email' => 'past@example.com',
    'inquiry_type' => 'reservation',
    'reserved_date' => '2020-01-01', 'reserved_time' => '09:00', 'reserved_zone' => 'Africa/Lagos (WAT, UTC+1)',
    'your-comment' => 'should fail', 'website_hp' => '', 'csrf_token' => $csrf3,
]);
ok($past['status'] === 422, 'past reservation date -> 422', (string) $past['status'] . ' ' . $past['body']);

// ---------------------------------------------------------------------------
echo "== 8. rotated csrf is enforced ==\n";
$stale = post_json($BASE . '/config/mailer.php', [
    'Name' => 'Stale', 'Email' => 'stale@example.com', 'your-comment' => 'x',
    'website_hp' => '', 'csrf_token' => $csrf, // original token, already rotated
]);
ok($stale['status'] === 419, 're-using the pre-rotation token -> 419', (string) $stale['status']);

sleep(6);

// ---------------------------------------------------------------------------
echo "== 9. classic form post -> 302 on-site redirect ==\n";
$csrf4 = (string) ($resJson['new_csrf_token'] ?? $csrf3);
$form  = post_form($BASE . '/config/mailer.php', [
    'Name' => 'Chidi Browser', 'Email' => 'chidi@example.com',
    'service' => 'Cloud Infrastructure & AWS Serverless',
    'your-comment' => 'Browser form submission.',
    'website_hp' => '', 'inquiry_type' => 'contact',
    'return_to' => 'https://evil.example/phish',
    'csrf_token' => $csrf4,
]);
$loc = location_of($form);
ok($form['status'] === 302, 'form post -> 302', (string) $form['status'] . ' loc=' . $loc);
ok($loc !== '' && !str_contains($loc, 'evil.example'), 'hostile return_to ignored', $loc);
ok(str_starts_with($loc, BASE_URL) || str_starts_with($loc, '/') === false, 'redirect stays on-site', $loc);
ok(str_contains($loc, 'contact_status=success'), 'success flag on redirect', $loc);

echo "\n", $failures === 0 ? "ALL MAILER TESTS PASSED" : "FAILURES: {$failures}", "\n";
exit($failures === 0 ? 0 : 1);
