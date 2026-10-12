<?php
declare(strict_types=1);

/**
 * Global Header Partial
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12, Strict Typing, and SEO Best Practices.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/tracker.php';
require_once __DIR__ . '/seo.php';

// Record real-time visitor traffic to MySQL database
track_visitor_traffic();

// Fallback SEO defaults if not provided by the calling template
$pageTitle       = $pageTitle ?? (APP_NAME . ' | ' . APP_TAGLINE);
$pageDescription = $pageDescription ?? 'Official engineering portfolio of Jamachi Maurice Mauricennadi: Senior Full-Stack Software Engineer, AI/ML Architect, and Founder/CTO at Morex Web Enterprise Group of Companies Ltd.';
$pageKeywords    = $pageKeywords ?? 'Jamachi Maurice, Mauricennadi, Full-Stack Engineer, AI Architect, Laravel 11, Node.js, Next.js, PostgreSQL, Redis, AWS Serverless, FinTech Escrow, Morex Web Enterprise';
$currentPage     = $currentPage ?? 'home';
$canonicalUrl    = $canonicalUrl ?? url(basename($_SERVER['SCRIPT_NAME'] ?? ''));
$ogImage         = $ogImage ?? asset('img/hero/hero.png');

// Structured data. Pages push extra graphs via $jsonLdBlocks[].
$jsonLdBlocks   = $jsonLdBlocks ?? [];
$breadcrumbTrail = $breadcrumbTrail ?? [];

if ($breadcrumbTrail !== []) {
    $crumbGraph = seo_breadcrumb_graph($breadcrumbTrail);
    if ($crumbGraph !== []) {
        $jsonLdBlocks[] = $crumbGraph;
    }
}

$jsonLdBlocks = array_values(array_filter(array_merge(
    [seo_website_graph(), seo_organization_graph(), seo_person_graph()],
    $jsonLdBlocks
), static fn (array $g): bool => $g !== []));

$jsonLdOutput = seo_jsonld($jsonLdBlocks);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="keywords" content="<?= e($pageKeywords) ?>">
    <meta name="author" content="<?= e(PROFILE_NAME) ?>">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    
    <!-- OpenGraph Metadata -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:site_name" content="<?= e(APP_NAME) ?>">
    
    <!-- Twitter Card Metadata -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($pageDescription) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">

    <!-- Indexing directives -->
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1, max-text-length:-1">
    <meta name="googlebot" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="bingbot" content="index, follow">
    <meta name="ai-content-declaration" content="human-authored">
    <link rel="sitemap" type="application/xml" href="<?= url('sitemap.xml') ?>">
    <link rel="alternate" type="text/plain" title="llms.txt (AI/LLM site index)" href="<?= url('llms.txt') ?>">
    <link rel="alternate" type="text/markdown" title="llms.md (AI-readable site specification)" href="<?= url('llms.md') ?>">

    <?php if (GOOGLE_SITE_VERIFICATION !== ''): ?>
    <meta name="google-site-verification" content="<?= e(GOOGLE_SITE_VERIFICATION) ?>">
    <?php endif; ?>
    <?php if (MSN_SITE_VERIFICATION !== ''): ?>
    <meta name="msn-site-verification" content="<?= e(MSN_SITE_VERIFICATION) ?>">
    <?php endif; ?>
    <?php if (YANDEX_SITE_VERIFICATION !== ''): ?>
    <meta name="yandex-verification" content="<?= e(YANDEX_SITE_VERIFICATION) ?>">
    <?php endif; ?>
    <meta name="verification" content="indexnow:<?= url(INDEXNOW_KEY_FILE) ?>">

    <?= $jsonLdOutput ?: '' ?>

    <title><?= e($pageTitle) ?></title>

    <!-- Site Favicon -->
    <link rel="icon" href="<?= asset('img/favicon/favicon.png') ?>" type="image/x-icon">

    <!-- CSS Plugins Files -->
    <link rel="stylesheet" href="<?= asset('css/vendor/remixicon.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/vendor/jquery.fancybox.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/vendor/owl.carousel.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/vendor/aos.css') ?>">

    <!-- Tailwind CSS Runtime Engine -->
    <script src="<?= asset('js/vendor/tailwindcss3.4.5.js') ?>"></script>

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">

    <!-- Inline Route Highlight Tokens -->
    <style>
        .bix-navbar .nav-link.active-route {
            color: #f41a4a !important;
            font-weight: 700 !important;
        }
        .bix-mobile-menu .nav-link.active-route {
            color: #f41a4a !important;
            border-color: #f41a4a !important;
            background-color: #fdf2f4;
            font-weight: 700 !important;
        }
    </style>
</head>

<body>

    <!-- Loader -->
    <div class="bix-loader">
        <span class="loader"></span>
        <img src="<?= asset('img/logo/logo.png') ?>" alt="<?= e(PROFILE_NAME) ?>">
    </div>

    <!-- Header -->
    <header class="bix-static transition-all duration-[0.3s] ease-in-out py-[30px] fixed top-[0] right-[0] left-[0] z-[20]">
        <div class="flex flex-wrap justify-between relative items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full">
                <div class="w-full px-[12px]">
                    <nav class="navbar navbar-expand-lg bix-navbar transition-all duration-[0.3s] ease-in-out p-[15px] bg-[#f8f6fc] border-[1px] border-solid border-[#111a24] rounded-[30px] relative z-[3] flex items-center justify-between max-[992px]:flex-nowrap">
                        <!-- Header Logo Start -->
                        <a class="navbar-brand flex items-center" href="<?= url('index.php') ?>" title="<?= e(PROFILE_NAME) ?>">
                            <img src="<?= asset('img/logo/logo.png') ?>" alt="<?= e(PROFILE_NAME) ?>" class="bix-logo w-[90px] px-[15px] flex">
                            <img src="<?= asset('img/logo/logo-dark.png') ?>" alt="<?= e(PROFILE_NAME) ?>" class="dark-logo w-[90px] px-[15px] hidden">
                        </a>
                        <!-- Header Logo End -->
                        <button class="navbar-toggler min-[992px]:hidden w-[40px] h-[40px] flex items-center justify-center rounded-[50%] text-[#111a24] border-[1px] border-solid border-[#111a24] text-[1.25rem] bg-transparent" type="button" title="Toggle Navigation">
                            <i class="ri-menu-2-line"></i>
                        </button>
                        <div class="bix-main-menu flex relative justify-end max-[991px]:hidden" id="navbarSupportedContent">
                            <ul class="navbar-nav m-[0] pl-[0] flex flex-wrap min-[992px]:flex-row items-center">
                                <li class="nav-item transition-all duration-[0.3s] ease-in-out ml-[30px]">
                                    <a class="nav-link transition-all duration-[0.3s] ease-in-out font-montserrat tracking-[0.03rem] p-[0] text-[15px] font-medium leading-[40px] capitalize text-[#1b1c20] flex items-center relative hover:text-[#f41a4a] <?= $currentPage === 'home' ? 'active-route' : '' ?>" href="<?= url('index.php') ?>">Home</a>
                                </li>
                                <li class="nav-item transition-all duration-[0.3s] ease-in-out ml-[30px]">
                                    <a class="nav-link transition-all duration-[0.3s] ease-in-out font-montserrat tracking-[0.03rem] p-[0] text-[15px] font-medium leading-[40px] capitalize text-[#1b1c20] flex items-center relative hover:text-[#f41a4a] <?= $currentPage === 'about' ? 'active-route' : '' ?>" href="<?= url('about.php') ?>">About</a>
                                </li>
                                <li class="nav-item transition-all duration-[0.3s] ease-in-out ml-[30px]">
                                    <a class="nav-link transition-all duration-[0.3s] ease-in-out font-montserrat tracking-[0.03rem] p-[0] text-[15px] font-medium leading-[40px] capitalize text-[#1b1c20] flex items-center relative hover:text-[#f41a4a] <?= $currentPage === 'projects' ? 'active-route' : '' ?>" href="<?= url('projects.php') ?>">Projects</a>
                                </li>
                                <li class="nav-item transition-all duration-[0.3s] ease-in-out ml-[30px]">
                                    <a class="nav-link transition-all duration-[0.3s] ease-in-out font-montserrat tracking-[0.03rem] p-[0] text-[15px] font-medium leading-[40px] capitalize text-[#1b1c20] flex items-center relative hover:text-[#f41a4a] <?= $currentPage === 'services' ? 'active-route' : '' ?>" href="<?= url('services.php') ?>">Services</a>
                                </li>
                                <li class="nav-item transition-all duration-[0.3s] ease-in-out ml-[30px]">
                                    <a class="nav-link transition-all duration-[0.3s] ease-in-out font-montserrat tracking-[0.03rem] p-[0] text-[15px] font-medium leading-[40px] capitalize text-[#1b1c20] flex items-center relative hover:text-[#f41a4a] <?= $currentPage === 'resume' ? 'active-route' : '' ?>" href="<?= url('resume.php') ?>">Resume</a>
                                </li>
                                <li class="nav-item transition-all duration-[0.3s] ease-in-out ml-[30px]">
                                    <a class="nav-link transition-all duration-[0.3s] ease-in-out font-montserrat tracking-[0.03rem] p-[0] text-[15px] font-medium leading-[40px] capitalize text-[#1b1c20] flex items-center relative hover:text-[#f41a4a] <?= $currentPage === 'contact' ? 'active-route' : '' ?>" href="<?= url('contact.php') ?>">Contact</a>
                                </li>
                                <li class="nav-item dropdown drop-list transition-all duration-[0.3s] ease-in-out ml-[30px] relative">
                                    <a class="nav-link dropdown-arrow transition-all duration-[0.3s] ease-in-out font-montserrat tracking-[0.03rem] p-[0] text-[15px] font-medium leading-[40px] capitalize text-[#1b1c20] flex items-center relative hover:text-[#f41a4a] <?= in_array($currentPage, ['single-project', 'blog', 'blog-detail']) ? 'active-route' : '' ?>" href="javascript:void(0)">
                                        Pages
                                        <i class="ri-arrow-drop-down-line transition-all duration-[0.3s] ease-in-out mr-[5px] text-[24px] text-[#1b1c20] flex"></i>
                                    </a>
                                    <ul class="sub-menu transition-all duration-[0.3s] ease-in-out min-w-[190px] mt-[15px] py-[15px] px-[20px] absolute z-[16] text-left opacity-[0] invisible left-[0] rounded-[15px] border-[1px] border-solid border-[#eae8ef] shadow-lg right-auto bg-[#fff] block">
                                        <li class="py-[5px] block">
                                            <a href="<?= url('blog.php') ?>" class="transition-all duration-[0.3s] ease-in-out font-montserrat tracking-[0.03rem] leading-[20px] font-medium text-[14px] text-[#777] capitalize flex justify-between items-center p-[0] hover:text-[#f41a4a] <?= $currentPage === 'blog' ? 'text-[#f41a4a] font-bold' : '' ?>">Tech Articles</a>
                                        </li>
                                    </ul>
                                </li>
                                <!-- Quick Hire Consultation CTA -->
                                <li class="nav-item ml-[25px]">
                                    <button type="button" class="bix-modal-toggle transition-all duration-[0.3s] ease-in-out py-[8px] px-[16px] font-montserrat text-[13px] font-semibold leading-[1.2] bg-[#f41a4a] text-[#fff] rounded-[15px] hover:bg-[#111a24] shadow-sm flex items-center">
                                        Hire Jamachi <i class="ri-arrow-right-up-line ml-[4px]"></i>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile-menu -->
    <div class="bix-sidebar-overlay hidden w-full h-screen fixed top-[0] left-[0] bg-[#000000cc] z-[42]"></div>
    <div id="in_mobile_menu" class="bix-side-cart bix-mobile-menu transition-all duration-[0.3s] ease-in-out w-[300px] h-full pt-[15px] px-[20px] pb-[20px] fixed top-[0] right-auto left-[0] bg-[#fff] translate-x-[-100%] flex flex-col z-[43] overflow-auto">
        <div class="bix-menu-title w-full pb-[20px] flex flex-wrap justify-between items-center border-b border-[#eee]">
            <div class="menu-title flex items-center">
                <h4 class="relative border-[0] font-montserrat text-[17px] font-semibold leading-[1] text-[#1b1c20] bg-transparent tracking-[0.03rem]"><?= e(PROFILE_FIRST_NAME) ?></h4>
            </div>
            <button type="button" class="bix-close relative border-[0] text-[30px] leading-[1] text-[#ff0000] bg-transparent ">&times;</button>
        </div>
        <div class="bix-menu-inner mt-[15px]">
            <div class="bix-menu-content">
                <ul>
                    <li class="nav-item relative">
                        <a href="<?= url('index.php') ?>" class="nav-link mb-[12px] py-[8px] px-[16px] block capitalize text-[#777] border-[1px] border-solid border-[#eae8ef] rounded-[10px] font-montserrat leading-[26px] text-[14px] font-medium tracking-[0.03rem] <?= $currentPage === 'home' ? 'active-route' : '' ?>">Home</a>
                    </li>
                    <li class="nav-item relative">
                        <a href="<?= url('about.php') ?>" class="nav-link mb-[12px] py-[8px] px-[16px] block capitalize text-[#777] border-[1px] border-solid border-[#eae8ef] rounded-[10px] font-montserrat leading-[26px] text-[14px] font-medium tracking-[0.03rem] <?= $currentPage === 'about' ? 'active-route' : '' ?>">About</a>
                    </li>
                    <li class="nav-item relative">
                        <a href="<?= url('projects.php') ?>" class="nav-link mb-[12px] py-[8px] px-[16px] block capitalize text-[#777] border-[1px] border-solid border-[#eae8ef] rounded-[10px] font-montserrat leading-[26px] text-[14px] font-medium tracking-[0.03rem] <?= $currentPage === 'projects' ? 'active-route' : '' ?>">Projects</a>
                    </li>
                    <li class="nav-item relative">
                        <a href="<?= url('services.php') ?>" class="nav-link mb-[12px] py-[8px] px-[16px] block capitalize text-[#777] border-[1px] border-solid border-[#eae8ef] rounded-[10px] font-montserrat leading-[26px] text-[14px] font-medium tracking-[0.03rem] <?= $currentPage === 'services' ? 'active-route' : '' ?>">Services</a>
                    </li>
                    <li class="nav-item relative">
                        <a href="<?= url('resume.php') ?>" class="nav-link mb-[12px] py-[8px] px-[16px] block capitalize text-[#777] border-[1px] border-solid border-[#eae8ef] rounded-[10px] font-montserrat leading-[26px] text-[14px] font-medium tracking-[0.03rem] <?= $currentPage === 'resume' ? 'active-route' : '' ?>">Resume</a>
                    </li>
                    <li class="nav-item relative">
                        <a href="<?= url('contact.php') ?>" class="nav-link mb-[12px] py-[8px] px-[16px] block capitalize text-[#777] border-[1px] border-solid border-[#eae8ef] rounded-[10px] font-montserrat leading-[26px] text-[14px] font-medium tracking-[0.03rem] <?= $currentPage === 'contact' ? 'active-route' : '' ?>">Contact</a>
                    </li>
                    <li class="nav-item dropdown drop-list relative">
                        <a href="javascript:void(0)" class="nav-link dropdown-arrow mb-[12px] py-[8px] px-[16px] block capitalize text-[#777] border-[1px] border-solid border-[#eae8ef] rounded-[10px] font-montserrat leading-[26px] text-[14px] font-medium tracking-[0.03rem]">Pages</a>
                        <ul class="sub-menu w-full hidden min-w-auto p-[0] mb-[0]">
                            <li class="relative"><a href="<?= url('blog.php') ?>" class="text-[14px] py-[8px] px-[16px] block capitalize font-montserrat text-[#777] leading-[26px] font-normal tracking-[0.03rem]">Blog</a></li>
                        </ul>
                    </li>
                </ul>
                <div class="mt-[20px] pt-[15px] border-t border-[#eee]">
                    <button type="button" class="bix-modal-toggle w-full py-[10px] px-[15px] font-montserrat text-[14px] font-semibold bg-[#f41a4a] text-[#fff] rounded-[15px] text-center">
                        Book Consultation
                    </button>
                </div>
            </div>
        </div>
    </div>
