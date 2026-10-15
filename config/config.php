<?php
declare(strict_types=1);

/**
 * Global Application Configuration & Executive Profile Registry
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12 and strict typing.
 */

// Prevent double configuration definitions
if (!defined('APP_INITIALIZED')) {
    define('APP_INITIALIZED', true);

    // ============================================================
    // Application Environment & Meta
    // ============================================================
    define('APP_NAME', 'Jamachi Maurice Mauricennadi');
    define('APP_TAGLINE', 'Senior Full-Stack Software Engineer & AI Architect | Founder & CTO');
    define('APP_ENV', getenv('APP_ENV') ?: 'production');
    define('APP_DEBUG', APP_ENV === 'development');

    // ============================================================
    // Directory Paths & Dynamic URL Derivation
    // ============================================================
    if (!defined('ROOT_PATH')) {
        define('ROOT_PATH', dirname(__DIR__));
    }

    if (!defined('SITE_DOMAIN')) {
        define('SITE_DOMAIN', getenv('SITE_DOMAIN') ?: 'jmn.test');
    }

    if (!defined('BASE_URL')) {
        // 1) Explicit override always wins.
        $envBase = getenv('BASE_URL');
        if (is_string($envBase) && filter_var($envBase, FILTER_VALIDATE_URL)) {
            define('BASE_URL', rtrim($envBase, '/'));
        } else {
            $canonicalHost = strtolower((string) SITE_DOMAIN);

            // 2) CLI has no Host header, and SCRIPT_NAME is a filesystem path
            //    (e.g. "bin/build-seo.php") - deriving an origin from it would
            //    produce nonsense like http://localhostbin. Pin the origin.
            if (PHP_SAPI === 'cli') {
                define('BASE_URL', 'https://' . $canonicalHost);
            } else {
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (($_SERVER['SERVER_PORT'] ?? 80) == 443) ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? $canonicalHost;

                // Host-header hardening: never emit links or e-mail links
                // pointing at an attacker-controlled Host. Fall back to the
                // canonical site origin.
                $hostBare = strtolower(preg_replace('/:\d+$/', '', $host) ?? $host);
                $permittedHosts = ['localhost', '127.0.0.1', '::1', $canonicalHost, 'www.' . $canonicalHost];
                if (!in_array($hostBare, $permittedHosts, true)) {
                    $host = $canonicalHost;
                }

                // Derive any subdirectory path relative to the web server DOCUMENT_ROOT.
                // Works seamlessly for both virtual hosts (docroot == project root)
                // and localhost subdirectory installs (e.g. /JMN), without ever doubling
                // the "admin/" segment.
                $subPath = '';
                if (!empty($_SERVER['DOCUMENT_ROOT'])) {
                    $normRoot = rtrim(str_replace('\\', '/', ROOT_PATH), '/');
                    $normDoc  = rtrim(str_replace('\\', '/', (string)$_SERVER['DOCUMENT_ROOT']), '/');
                    if (str_starts_with(strtolower($normRoot), strtolower($normDoc))) {
                        $subPath = substr($normRoot, strlen($normDoc));
                    }
                }

                define('BASE_URL', $protocol . '://' . $host . $subPath);
            }
        }
    }

    if (!defined('ASSETS_URL')) {
        define('ASSETS_URL', BASE_URL . '/assets');
    }

    // ============================================================
    // Executive Profile & Contact Credentials
    // ============================================================
    define('PROFILE_NAME', 'Jamachi Maurice Mauricennadi');
    define('PROFILE_FIRST_NAME', 'Jamachi');
    define('PROFILE_LAST_NAME', 'Mauricennadi');
    define('PROFILE_TITLE', 'Senior Full-Stack Software Engineer & AI/ML Architect');
    define('PROFILE_ROLE_BADGE', 'Founder, CEO & CTO');
    define('PROFILE_COMPANY', 'MOREX WEB ENTERPRISE GROUP OF COMPANIES LTD');
    define('PROFILE_REG_NO', '8721238'); // Federal Republic of Nigeria BN/RC
    define('PROFILE_EMAIL', 'jmauricennadi@gmail.com');
    define('PROFILE_CORP_EMAIL', 'contact@morexweb.com');
    define('PROFILE_PHONE', '+234 812 756 0227');
    define('PROFILE_PHONE_RAW', '+2348127560227');
    define('PROFILE_LOCATION', 'Owerri, Imo State & Lagos (Hybrid), Nigeria');
    define('PROFILE_AVAILABILITY', 'Available Worldwide / Remote');
    define('PROFILE_LINKEDIN', 'https://linkedin.com/in/jmauricennadi');
    define('PROFILE_GITHUB', 'https://github.com/FushionHub');
    define('PROFILE_VENTURE_URL', 'https://morexweb.com');
    define('PROFILE_PORTFOLIO_URL', 'https://' . SITE_DOMAIN);
    define('PROFILE_RESUME_PDF', ASSETS_URL . '/docs/Jamachi_Maurice_Resume.pdf');

    // ============================================================
    // SEO / Indexing Credentials
    // Google, Bing and IndexNow verification tokens. Override via
    // environment when deploying (never hard-code live keys in VCS).
    // ============================================================
    define('GOOGLE_SITE_VERIFICATION', getenv('GOOGLE_SITE_VERIFICATION') ?: '');
    define('MSN_SITE_VERIFICATION', getenv('MSN_SITE_VERIFICATION') ?: '');
    define('YANDEX_SITE_VERIFICATION', getenv('YANDEX_SITE_VERIFICATION') ?: '');
    define('INDEXNOW_KEY', getenv('INDEXNOW_KEY') ?: '6f0d1b8f0d1e4b9c8a2f7d3e5b6c1a94');
    define('INDEXNOW_KEY_FILE', INDEXNOW_KEY . '.txt');

    // ============================================================
    // Executive Metrics & Flagship Highlights
    // ============================================================
    define('METRIC_UPTIME', '99.9%');
    define('METRIC_UPTIME_LABEL', 'Production Cloud Uptime');
    define('METRIC_LATENCY', '-50%');
    define('METRIC_LATENCY_LABEL', 'Database Query Latency');
    define('METRIC_CONVERSION', '+18%');
    define('METRIC_CONVERSION_LABEL', 'Checkout Conversion Rate');
    define('METRIC_COST_SAVINGS', '-35%');
    define('METRIC_COST_SAVINGS_LABEL', 'Cloud Operating Cost Savings');

    // Load Security Engine
    require_once ROOT_PATH . '/config/security.php';

    // Load structured-data builders (SEO / AEO) - pure functions, no output
    require_once ROOT_PATH . '/includes/seo.php';
}

/**
 * Helper to build internal URLs
 */
function url(string $path = ''): string
{
    $cleanPath = ltrim($path, '/');
    return empty($cleanPath) ? BASE_URL : BASE_URL . '/' . $cleanPath;
}

/**
 * Helper to build asset URLs
 */
function asset(string $path): string
{
    return ASSETS_URL . '/' . ltrim($path, '/');
}

/**
 * WebP variant lookup for optimized images.
 *
 * The optimizer (bin/optimize-images.php) writes foo.jpg.webp alongside
 * foo.jpg. Returns the asset-relative .webp path when present, else null.
 * Accepts the same $src forms as asset() ('img/x.jpg' or 'assets/img/x.jpg').
 */
function webp_variant(string $src): ?string
{
    $clean = ltrim($src, '/');
    if (str_starts_with($clean, 'assets/')) {
        $clean = substr($clean, 7);
    }
    $dot = strrpos($clean, '.');
    if ($dot === false) {
        return null;
    }
    $ext = strtolower(substr($clean, $dot + 1));
    if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
        return null;
    }
    $candidate = $clean . '.webp';
    $root = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__);
    return is_file($root . '/assets/' . $candidate) ? $candidate : null;
}

/**
 * Responsive image markup with WebP-first delivery.
 *
 * Emits <picture><source webp><img original></picture> when a pre-generated
 * .webp exists, otherwise a plain <img>. The original is always the fallback,
 * so browsers without WebP support render identically. $attrs is raw HTML
 * (class/loading/decoding/fetchpriority), $alt is escaped.
 */
function picture_img(string $src, string $alt, string $attrs = ''): string
{
    $img = '<img src="' . e(asset($src)) . '" alt="' . e($alt) . '"'
        . ($attrs !== '' ? ' ' . $attrs : '') . '>';
    $webp = webp_variant($src);
    if ($webp === null) {
        return $img;
    }
    return '<picture><source srcset="' . e(asset($webp)) . '" type="image/webp">'
        . $img . '</picture>';
}
