<?php
declare(strict_types=1);

/**
 * IndexNow submission client
 * Jamachi Maurice Mauricennadi Portfolio Platform
 *
 * IndexNow is a ping API: you tell the engines which URLs changed, and Bing,
 * Seznam, Yandex, Naver, Swoogle and DuckDuckGo pick them up within seconds.
 * Google reads the same key file opportunistically but does not consume the
 * ping endpoint; Googlebot still comes from sitemap.xml.
 *
 * CLI:
 *   php indexnow.php                            # every <loc> in sitemap.xml
 *   php indexnow.php --dry-run                  # print the list, send nothing
 *   php indexnow.php https://jmn.morexweb.com/services.php
 *   php indexnow.php <url> <url> ...            # up to 10 000 URLs
 *
 * Web (for cron / CI, key-protected):
 *   GET  /indexnow.php?key=<INDEXNOW_KEY>&url=<url>[&url=...]
 *   POST /indexnow.php           (IndexNow-native JSON body)
 *
 * Every submission is recorded in the `indexnow_logs` table.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

const INDEXNOW_ENDPOINT = 'https://api.indexnow.org/indexnow';
const INDEXNOW_MAX_URLS = 10000;
const INDEXNOW_TIMEOUT  = 15;

// ---------------------------------------------------------------------------
// URL collection
// ---------------------------------------------------------------------------

/**
 * All canonical URLs published by this site, read from sitemap.xml so the
 * sitemap stays the single source of truth for "what is indexable".
 *
 * @return list<string>
 */
function indexnow_urls_from_sitemap(): array
{
    $path = ROOT_PATH . '/sitemap.xml';
    if (!is_file($path)) {
        return [];
    }

    $xml = @file_get_contents($path);
    if ($xml === false || $xml === '') {
        return [];
    }

    $urls = [];

    if (function_exists('simplexml_load_string')) {
        $doc = @simplexml_load_string($xml);
        if ($doc !== false) {
            foreach ($doc->url as $url) {
                $loc = trim((string) $url->loc);
                if ($loc !== '') {
                    $urls[] = $loc;
                }
            }
        }
    }

    if ($urls === [] && preg_match_all('#<loc>\s*([^<\s]+)\s*</loc>#i', $xml, $m)) {
        $urls = array_map('trim', $m[1]);
    }

    return array_values(array_unique($urls));
}

/**
 * Keep only absolute URLs that live on this host. The endpoint will not accept
 * third-party URLs, and rejecting them here stops a poisoned cron argument
 * from turning us into an open relay.
 *
 * @param list<mixed> $raw
 * @return list<string>
 */
function indexnow_normalise_urls(array $raw): array
{
    $origin = BASE_URL;
    $out    = [];

    foreach ($raw as $u) {
        if (!is_string($u)) {
            continue;
        }
        $u = trim($u);
        if ($u === '' || !filter_var($u, FILTER_VALIDATE_URL)) {
            continue;
        }
        if (stripos($u, $origin . '/') !== 0 && $u !== $origin) {
            continue;
        }
        // Fragment-less only - IndexNow treats the fragment as a new URL.
        $u = preg_replace('/#.*$/', '', $u);
        if ($u === null || $u === '') {
            continue;
        }
        if (!in_array($u, $out, true)) {
            $out[] = $u;
        }
        if (count($out) >= INDEXNOW_MAX_URLS) {
            break;
        }
    }

    return $out;
}

// ---------------------------------------------------------------------------
// Submission
// ---------------------------------------------------------------------------

/**
 * @param list<string> $urls
 * @return array{ok: bool, status: int, body: string}
 */
function indexnow_post(array $urls): array
{
    $host      = (string) parse_url(BASE_URL, PHP_URL_HOST);
    $payload   = json_encode([
        'host'        => $host,
        'key'         => INDEXNOW_KEY,
        'keyLocation' => BASE_URL . '/' . INDEXNOW_KEY_FILE,
        'urlList'     => array_values($urls),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if ($payload === false) {
        return ['ok' => false, 'status' => 0, 'body' => 'payload encoding failed'];
    }

    if (function_exists('curl_init')) {
        $ch = curl_init(INDEXNOW_ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json; charset=utf-8',
                'Accept: application/json, text/plain',
            ],
            CURLOPT_USERAGENT      => 'JMN-IndexNow/1.0 (+' . BASE_URL . ')',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => INDEXNOW_TIMEOUT,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS      => 0,
        ]);
        $body   = (string) curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err    = curl_error($ch);

        return [
            'ok'   => $status >= 200 && $status < 300,
            'status' => $status,
            'body' => $body !== '' ? $body : ($err !== '' ? $err : ''),
        ];
    }

    $context = stream_context_create([
        'http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: application/json; charset=utf-8\r\n"
                             . "Accept: application/json, text/plain\r\n"
                             . "User-Agent: JMN-IndexNow/1.0 (" . BASE_URL . ")\r\n",
            'content'       => $payload,
            'timeout'       => INDEXNOW_TIMEOUT,
            'ignore_errors' => true,
        ],
    ]);
    $body   = @file_get_contents(INDEXNOW_ENDPOINT, false, $context);
    $status = 0;
    if (isset($http_response_header[0]) && preg_match('#\s(\d{3})\s#', $http_response_header[0] . ' ', $m)) {
        $status = (int) $m[1];
    }

    return [
        'ok'     => $status >= 200 && $status < 300,
        'status' => $status,
        'body'   => $body === false ? 'transport error' : $body,
    ];
}

/**
 * Submit and persist an audit row. Never lets a transport failure escape as an
 * uncaught exception - a broken ping must not break a deploy.
 *
 * @param list<string> $urls
 */
function indexnow_submit(array $urls, string $source): array
{
    if ($urls === []) {
        return ['ok' => false, 'status' => 0, 'body' => 'no valid urls', 'count' => 0];
    }

    $result = indexnow_post($urls);
    $result['count'] = count($urls);

    try {
        $pdo = get_db();
        if ($pdo instanceof PDO) {
            $stmt = $pdo->prepare(
                'INSERT INTO `indexnow_logs` (`key_location`, `urls`, `http_status`, `response_body`)
             VALUES (:key_location, :urls, :http_status, :response_body)'
            );
            $stmt->execute([
                ':key_location'  => BASE_URL . '/' . INDEXNOW_KEY_FILE . ' (' . $source . ')',
                ':urls'          => substr(implode("\n", $urls), 0, 65000),
                ':http_status'   => $result['status'],
                ':response_body' => substr($result['body'], 0, 60000),
            ]);
        }
    } catch (Throwable $e) {
        error_log('[indexnow] audit insert failed: ' . $e->getMessage());
    }

    return $result;
}

// ---------------------------------------------------------------------------
// Web mode
// ---------------------------------------------------------------------------

if (PHP_SAPI !== 'cli') {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $raw    = [];
    $key    = '';

    if ($method === 'POST') {
        $input = json_decode((string) file_get_contents('php://input'), true);
        if (is_array($input)) {
            $raw = is_array($input['urlList'] ?? null) ? $input['urlList'] : [];
            $key = $input['key'] ?? '';
        }
    } elseif ($method === 'GET') {
        $key = (string) ($_GET['key'] ?? '');
        $raw = is_array($_GET['url'] ?? null) ? $_GET['url'] : (isset($_GET['url']) ? [(string) $_GET['url']] : []);
    }

    header('Content-Type: text/plain; charset=utf-8');

    if ($method !== 'GET' && $method !== 'POST') {
        http_response_code(405);
        echo 'Method Not Allowed';
        exit;
    }

    if (!is_string($key) || $key === '' || !hash_equals(INDEXNOW_KEY, $key)) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }

    $urls = indexnow_normalise_urls($raw);
    if ($urls === []) {
        http_response_code(400);
        echo 'Bad Request: no valid urlList';
        exit;
    }

    $result = indexnow_submit($urls, 'web:' . $method);

    http_response_code($result['ok'] ? 200 : 502);
    echo 'submitted ' . $result['count'] . ' url(s), status ' . $result['status'];
    exit;
}

// ---------------------------------------------------------------------------
// CLI mode
// ---------------------------------------------------------------------------

$cliArgs  = array_slice($argv ?? [], 1);
$dryRun   = in_array('--dry-run', $cliArgs, true);
$cliArgs  = array_values(array_filter($cliArgs, static fn (string $a): bool => !str_starts_with($a, '--')));
$urls     = $cliArgs !== [] ? indexnow_normalise_urls($cliArgs) : indexnow_urls_from_sitemap();
$source   = $cliArgs !== [] ? 'cli:manual' : 'cli:sitemap';

if ($urls === []) {
    fwrite(STDERR, "No URLs to submit (missing or empty sitemap.xml).\n");
    exit(1);
}

if ($dryRun) {
    printf("DRY RUN - nothing sent.\n  urls   : %d\n", count($urls));
    foreach ($urls as $u) {
        echo "    - {$u}\n";
    }
    exit(0);
}

$result = indexnow_submit($urls, $source);

printf(
    "IndexNow -> %s\n  urls   : %d\n  status : %d\n  body   : %s\n",
    BASE_URL,
    $result['count'],
    $result['status'],
    trim($result['body']) === '' ? '(empty)' : trim($result['body'])
);

exit($result['ok'] ? 0 : 1);
