<?php
declare(strict_types=1);

/**
 * Dynamic Homepage
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12, Strict Typing, and Full UI Component Preservation.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/data/projects.php';
require_once __DIR__ . '/data/services.php';
require_once __DIR__ . '/data/skills.php';
require_once __DIR__ . '/data/articles.php';
require_once __DIR__ . '/data/testimonials.php';

$pageTitle       = PROFILE_NAME . ' | ' . APP_TAGLINE;
$pageDescription = 'Senior Full-Stack Software Engineer & AI/ML Architect. Founder, CEO & CTO at Morex Web Enterprise Group of Companies Ltd. Specialized in Laravel 11, RAG AI systems, and FinTech escrow architectures.';
$currentPage     = 'home';
$canonicalUrl    = url('index.php');

$projects     = get_all_projects();
$services     = get_all_services();
$skillsMatrix = get_skills_matrix();
$articles     = get_recent_articles(3);
$testimonials = get_all_testimonials();

require_once __DIR__ . '/includes/header.php';
?>

    <!-- Hero -->
    <section class="section-hero h-[800px] mt-[100px] relative pb-[50px] pt-[100px] max-[991px]:h-auto max-[991px]:mt-[95px] max-[991px]:pb-[40px] max-[991px]:pt-[80px] max-[767px]:pb-[35px] max-[767px]:pt-[70px]" id="home">
        <div class="first-name top-[0] leading-none absolute font-montserrat text-[200px] font-black text-[#fff] opacity-[0.04] max-[991px]:text-[130px] max-[991px]:top-[30px] max-[575px]:text-[100px] max-[460px]:text-[70px] select-none pointer-events-none"><?= e(PROFILE_FIRST_NAME) ?></div>
        <div class="last-name bottom-[0] right-[0] absolute font-montserrat text-[200px] font-black text-[#fff] opacity-[0.04] max-[991px]:bottom-[250px] max-[991px]:text-[130px] max-[575px]:text-[100px] max-[460px]:text-[70px] select-none pointer-events-none"><?= e(PROFILE_LAST_NAME) ?></div>
        <div class="flex h-full flex-wrap justify-between relative items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full">
                <div class="min-[992px]:w-[50%] w-full px-[12px]">
                    <div class="bix-hero-img relative" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">
                        <div class="hero-inner-img relative">
                            <?= picture_img('img/hero/hero-1.png', PROFILE_NAME, 'data-offset="30" fetchpriority="high" class="hero-parallax w-[calc(100%-300px)] mt-auto mx-auto mb-[50px] block max-[1399px]:w-[calc(100%-260px)] max-[1199px]:w-[calc(100%-215px)] max-[991px]:w-[calc(100%-400px)] max-[767px]:w-[calc(100%-250px)] max-[575px]:w-[210px]"') ?>
                        </div>
                    </div>
                </div>
                <div class="min-[992px]:w-[50%] w-full px-[12px]">
                    <div class="bix-hero-contact h-full flex flex-col justify-center relative max-[991px]:mt-[15px] max-[991px]:pb-[30px] max-[991px]:items-center max-[575px]:text-center">
                        <div class="inline-flex items-center gap-2 mb-[12px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="500">
                            <span class="w-[10px] h-[10px] rounded-full bg-green-500 animate-pulse inline-block"></span>
                            <span class="text-[13px] font-montserrat font-semibold tracking-wider uppercase text-[#777]"><?= e(PROFILE_AVAILABILITY) ?></span>
                        </div>
                        <h4 class="mb-[8px] font-montserrat tracking-[0.03rem] leading-[1.2] text-[35px] font-bold text-[#f41a4a] max-[1399px]:text-[32px] max-[1199px]:text-[28px] max-[767px]:text-[24px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">Hi, I am</h4>
                        <h1 class="mb-[14px] text-[68px] text-[#111a24] font-bold relative tracking-[0.03rem] leading-[1.1] max-[1399px]:text-[60px] max-[1199px]:text-[48px] max-[767px]:text-[36px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="700"><?= e(PROFILE_NAME) ?></h1>
                        <p class="font-montserrat text-[22px] font-semibold text-[#111a24] leading-[32px] tracking-[0.03rem] max-[1399px]:text-[20px] max-[1199px]:text-[18px] max-[767px]:text-[16px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="800">
                            Senior Full-Stack Software Engineer & AI Architect
                            <span class="block text-[16px] text-[#f41a4a] font-medium mt-[6px]">Founder, CEO & CTO at <?= e(PROFILE_COMPANY) ?> (BN: <?= e(PROFILE_REG_NO) ?>)</span>
                        </p>
                        <div class="bix-buttons m-[-10px] pt-[40px] flex flex-wrap relative max-[991px]:pt-[20px] max-[575px]:justify-center" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="900">
                            <a href="<?= e(PROFILE_RESUME_PDF) ?>" download="Jamachi_Maurice_Resume.pdf" class="bix-button m-[10px] transition-all duration-[0.3s] ease-in-out py-[12px] px-[20px] font-montserrat text-[14px] font-semibold leading-[1.2] capitalize bg-[#111a24] text-[#fff] border-[1px] border-solid border-[#111a24] rounded-[15px] hover:bg-[#f41a4a] hover:border-[#f41a4a] hover:text-[#fff] inline-flex items-center">
                                <i class="ri-download-2-line mr-[6px] text-[16px]"></i> Download CV (PDF)
                            </a>
                            <button type="button" class="modal-trigger bix-modal-toggle bix-button m-[10px] transition-all duration-[0.3s] ease-in-out py-[12px] px-[20px] font-montserrat text-[14px] font-semibold leading-[1.2] capitalize bg-[#fff] text-[#111a24] border-[1px] border-solid border-[#111a24] rounded-[15px] hover:bg-[#f41a4a] hover:border-[#f41a4a] hover:text-[#fff] inline-flex items-center">
                                Book Consultation <i class="ri-arrow-right-up-line ml-[6px] text-[16px]"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Executive Metrics Bar -->
    <section class="section-metrics py-[30px] my-[20px] bg-[#f7f5fb] border-y border-[#eae8ef]">
        <div class="mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px] px-[12px]">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="metric-card p-[20px] bg-[#fff] rounded-[20px] border border-[#eae8ef] shadow-sm hover:border-[#f41a4a] transition-all duration-300" data-aos="fade-up" data-aos-duration="1500" data-aos-delay="200">
                    <h3 class="font-montserrat text-[38px] font-bold text-[#f41a4a] leading-none mb-[8px]"><?= METRIC_UPTIME ?></h3>
                    <p class="font-montserrat text-[13px] font-semibold text-[#111a24] uppercase tracking-wider mb-[4px]"><?= METRIC_UPTIME_LABEL ?></p>
                    <span class="text-[12px] text-[#777] font-montserrat">AWS Serverless Architecture</span>
                </div>
                <div class="metric-card p-[20px] bg-[#fff] rounded-[20px] border border-[#eae8ef] shadow-sm hover:border-[#f41a4a] transition-all duration-300" data-aos="fade-up" data-aos-duration="1500" data-aos-delay="300">
                    <h3 class="font-montserrat text-[38px] font-bold text-[#111a24] leading-none mb-[8px]"><?= METRIC_LATENCY ?></h3>
                    <p class="font-montserrat text-[13px] font-semibold text-[#111a24] uppercase tracking-wider mb-[4px]"><?= METRIC_LATENCY_LABEL ?></p>
                    <span class="text-[12px] text-[#777] font-montserrat">PostgreSQL / Redis Caching</span>
                </div>
                <div class="metric-card p-[20px] bg-[#fff] rounded-[20px] border border-[#eae8ef] shadow-sm hover:border-[#f41a4a] transition-all duration-300" data-aos="fade-up" data-aos-duration="1500" data-aos-delay="400">
                    <h3 class="font-montserrat text-[38px] font-bold text-[#f41a4a] leading-none mb-[8px]"><?= METRIC_CONVERSION ?></h3>
                    <p class="font-montserrat text-[13px] font-semibold text-[#111a24] uppercase tracking-wider mb-[4px]"><?= METRIC_CONVERSION_LABEL ?></p>
                    <span class="text-[12px] text-[#777] font-montserrat">Escrow Checkout Flow</span>
                </div>
                <div class="metric-card p-[20px] bg-[#fff] rounded-[20px] border border-[#eae8ef] shadow-sm hover:border-[#f41a4a] transition-all duration-300" data-aos="fade-up" data-aos-duration="1500" data-aos-delay="500">
                    <h3 class="font-montserrat text-[38px] font-bold text-[#111a24] leading-none mb-[8px]"><?= METRIC_COST_SAVINGS ?></h3>
                    <p class="font-montserrat text-[13px] font-semibold text-[#111a24] uppercase tracking-wider mb-[4px]"><?= METRIC_COST_SAVINGS_LABEL ?></p>
                    <span class="text-[12px] text-[#777] font-montserrat">Cloud Infrastructure Overhead</span>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="section-about relative py-[50px] max-[991px]:py-[40px] max-[767px]:py-[35px]" id="about">
        <div class="container flex flex-wrap justify-between relative items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full mb-[-30px]">
                <div class="w-full px-[12px]">
                    <div class="bix-banner mb-[30px] flex justify-center text-center max-[1199px]:mb-[25px] max-[767px]:mb-[20px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                        <h4 class="font-montserrat text-[36px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] max-[1199px]:text-[32px] max-[767px]:text-[28px] max-[575px]:text-[24px]">About <span class="text-[#f41a4a]">Me</span></h4>
                    </div>
                    <div class="bix-banner-line mb-[80px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">
                        <span class="inner-border relative flex justify-center"></span>
                    </div>
                </div>
                <div class="max-[1199px]:order-2 min-[1200px]:w-[33.33%] min-[992px]:w-[50%] w-full px-[12px] mb-[30px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                    <div class="bix-about-contact h-full flex flex-col justify-center">
                        <h5 class="mb-[20px] font-montserrat text-[26px] font-bold text-[#111a24] tracking-[0.04rem] leading-[1.3] max-[1399px]:text-[24px]">
                            “Mathematical precision in backend architecture; strategic vision in product execution.”
                        </h5>
                        <p class="mb-[16px] font-montserrat text-[14px] text-[#495461] font-normal leading-[26px] tracking-[0.03rem]">
                            A results-driven hybrid engineer-founder combining 5+ years of hands-on technical architecture with the strategic acumen of an executive leader. Specialized in designing high-availability backend engines, autonomous AI agent pipelines (RAG), financial transaction ledgers with zero-trust escrow models, and scalable AWS cloud-native systems.
                        </p>
                        <div class="about-name">
                            <h6 class="mb-[2px] font-montserrat text-[16px] text-[#111a24] font-bold tracking-[0.03rem] leading-[1.2]"><?= e(PROFILE_NAME) ?></h6>
                            <p class="text-[13px] text-[#f41a4a] mb-[12px] font-montserrat font-semibold leading-[20px] tracking-[0.03rem]">Founder, CEO & CTO | <?= e(PROFILE_COMPANY) ?></p>
                            <div class="inner-sign-items">
                                <?= picture_img('img/about/sign.png', 'signature', 'loading="lazy" decoding="async" class="white-mode w-[120px] inline') ?>
                                <?= picture_img('img/about/sign-dark.png', 'signature', 'loading="lazy" decoding="async" class="dark-mode w-[120px] hidden') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="max-[1199px]:order-1 min-[1200px]:w-[33.33%] min-[992px]:w-[100%] w-full px-[12px] mb-[30px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">
                    <div class="bix-about-img border-[1px] border-solid border-[#111a24] p-[15px] rounded-[200px] relative max-[1199px]:max-w-fit max-[1199px]:m-auto">
                        <?= picture_img('img/about/about.jpg', PROFILE_NAME, 'loading="lazy" decoding="async" class="w-full rounded-[200px]"') ?>
                        <div class="bix-rounded-circle z-[1] h-[130px] w-[130px] absolute bottom-[0] right-[0] bg-[#000000cc] rounded-[50%] max-[575px]:h-[120px] max-[575px]:w-[120px] max-[575px]:bottom-[20px] max-[575px]:right-[20px] max-[420px]:bottom-[0] max-[420px]:right-[0]">
                            <a href="<?= url('about.php') ?>" class="relative block w-full h-full">
                                <svg viewBox="0 0 100 100" width="100" height="100" class="fill-current h-auto origin-center w-full">
                                    <defs>
                                        <path id="circle" d=" M 50, 50 m -37, 0 a 37,37 0 1,1 74,0 a 37,37 0 1,1 -74,0"></path>
                                    </defs>
                                    <text>
                                        <textPath xlink:href="#circle" class="text-[10px] text-[#fff] tracking-[1.3px]">About Jamachi - Founder & CTO -
                                        </textPath>
                                    </text>
                                </svg>
                                <div class="inner-contact w-full h-full absolute top-[0] left-[0] right-[0] flex justify-center items-center">
                                    <i class="ri-arrow-right-up-line text-[30px] text-[#fff]"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="max-[1199px]:order-3 min-[1200px]:w-[33.33%] min-[992px]:w-[50%] w-full px-[12px]">
                    <div class="bix-skill h-full flex flex-col justify-center max-[1199px]:h-auto" id="progress">
                        <!-- Skill 1 -->
                        <div class="skill-inner-box mb-[24px] p-[20px] flex flex-col relative rounded-[100px] max-[420px]:rounded-[50px] bg-[#faf1c6]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">
                            <div class="skill-contact inline-flex flex-wrap max-[420px]:flex-col items-center">
                                <svg class="progress noselect w-[90px] h-full bg-transparent inline-block" data-progress="95" x="0px" y="0px" viewBox="0 0 80 80"> 
                                    <path class="track" d="M5,40a35,35 0 1,0 70,0a35,35 0 1,0 -70,0" />
                                    <path class="fill" d="M5,40a35,35 0 1,0 70,0a35,35 0 1,0 -70,0" />
                                    <text class="value" x="50%" y="58%">0%</text>
                                </svg>
                                <div class="skill-box-contact w-[calc(100%-90px)] p-[10px] flex flex-col justify-center max-[420px]:w-full">
                                    <h4 class="font-montserrat text-[16px] font-semibold text-[#111a24] mb-[4px] tracking-[0.03rem] leading-[1.2]">Backend & APIs <span class="text-[13px] font-medium text-[#777]">(PHP 8.2+, Laravel, Node)</span></h4>
                                    <p class="font-montserrat text-[13px] text-[#555] leading-[18px]">ACID Ledgers, Microservices & Redis concurrency</p>
                                </div>
                            </div>
                        </div>
                        <!-- Skill 2 -->
                        <div class="skill-inner-box mb-[24px] p-[20px] flex flex-col relative rounded-[100px] max-[420px]:rounded-[50px] bg-[#ffd5df]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="700">
                            <div class="skill-contact inline-flex flex-wrap max-[420px]:flex-col items-center">
                                <svg class="progress noselect w-[90px] h-full bg-transparent inline-block" data-progress="90" x="0px" y="0px" viewBox="0 0 80 80"> 
                                    <path class="track" d="M5,40a35,35 0 1,0 70,0a35,35 0 1,0 -70,0" />
                                    <path class="fill" d="M5,40a35,35 0 1,0 70,0a35,35 0 1,0 -70,0" />
                                    <text class="value" x="50%" y="58%">0%</text>
                                </svg>
                                <div class="skill-box-contact w-[calc(100%-90px)] p-[10px] flex flex-col justify-center max-[420px]:w-full">
                                    <h4 class="font-montserrat text-[16px] font-semibold text-[#111a24] mb-[4px] tracking-[0.03rem] leading-[1.2]">AI & Agents <span class="text-[13px] font-medium text-[#777]">(RAG, LangChain, Gemini)</span></h4>
                                    <p class="font-montserrat text-[13px] text-[#555] leading-[18px]">Contextual document analysis & autonomous agents</p>
                                </div>
                            </div>
                        </div>
                        <!-- Skill 3 -->
                        <div class="skill-inner-box p-[20px] flex flex-col relative rounded-[100px] max-[420px]:rounded-[50px] bg-[#d5f5ff]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="800">
                            <div class="skill-contact inline-flex flex-wrap max-[420px]:flex-col items-center">
                                <svg class="progress noselect w-[90px] h-full bg-transparent inline-block" data-progress="88" x="0px" y="0px" viewBox="0 0 80 80"> 
                                    <path class="track" d="M5,40a35,35 0 1,0 70,0a35,35 0 1,0 -70,0" />
                                    <path class="fill" d="M5,40a35,35 0 1,0 70,0a35,35 0 1,0 -70,0" />
                                    <text class="value" x="50%" y="58%">0%</text>
                                </svg>
                                <div class="skill-box-contact w-[calc(100%-90px)] p-[10px] flex flex-col justify-center max-[420px]:w-full">
                                    <h4 class="font-montserrat text-[16px] font-semibold text-[#111a24] mb-[4px] tracking-[0.03rem] leading-[1.2]">Cloud Architecture <span class="text-[13px] font-medium text-[#777]">(AWS Serverless, Docker)</span></h4>
                                    <p class="font-montserrat text-[13px] text-[#555] leading-[18px]">Cost reduction, 99.9% uptime & automated CI/CD</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Achievement (Education & Leadership Experience) -->
    <section class="section-achievement relative py-[50px] max-[991px]:py-[40px] max-[767px]:py-[35px]">
        <div class="flex flex-wrap justify-between relative items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full">
                <div class="w-full px-[12px]">
                    <div class="bix-banner mb-[30px] flex justify-center text-center max-[1199px]:mb-[25px] max-[767px]:mb-[20px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                        <h4 class="font-montserrat text-[36px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] max-[1199px]:text-[32px] max-[767px]:text-[28px] max-[575px]:text-[24px]">Track Record & <span class="text-[#f41a4a]">Credentials</span></h4>
                    </div>
                    <div class="bix-banner-line mb-[80px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">
                        <span class="inner-border relative flex justify-center"></span>
                    </div>
                </div>
                <div class="w-full px-[12px]">
                    <div class="bix-activity-tabs flex flex-col items-center">
                        <ul class="nav nav-tabs inline-flex flex-wrap justify-center items-center bg-[#fff] border-[1px] border-solid border-[#111a24] rounded-[25px] mb-[30px]" id="bix_activity" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                            <li class="nav-item m-[10px]">
                                <a href="#education" class="py-[5px] px-[15px] rounded-[15px] capitalize font-medium text-[16px] block">Education & Honors</a>
                            </li>
                            <li class="nav-item m-[10px]">
                                <a href="#experience" class="py-[5px] px-[15px] rounded-[15px] capitalize font-medium text-[16px] block">Leadership & Experience</a>
                            </li>
                        </ul>
                        <div class="tab-content w-full">
                            <!-- Education Tab -->
                            <div class="tab-activity-pane" id="education">
                                <div class="flex flex-wrap w-full">
                                    <div class="w-full">
                                        <div class="bix-achievement relative flex w-full max-[767px]:flex-col">
                                            <div class="bix-achievement-inner bix-border-r w-[50%] pr-[30px] border-r-[0.5px] border-solid border-[#111a24] max-[767px]:w-full max-[767px]:border-[0] max-[767px]:p-[0]">
                                                <div class="bix-achievement-box left border-[1px] border-solid border-[#111a24] p-[30px] rounded-[30px] bg-[#f7f5fb] relative max-[767px]:mt-[12px] max-[767px]:mb-[24px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                                                    <div class="achievement-year w-[100px] h-full absolute top-[0] flex items-center justify-center rotate-[270deg] z-[1] right-[-82px] max-[767px]:block max-[767px]:rotate-[0] max-[767px]:top-[-13px] max-[767px]:left-[45px]">
                                                        <span class="bg-[#111a24] text-[#fff] border-[1px] border-solid border-[#111a24] rounded-[10px] leading-[1] py-[3px] px-[10px] font-montserrat font-normal tracking-[0.02rem] text-[14px]">2024</span>
                                                    </div>
                                                    <h4 class="mb-[8px] font-montserrat text-[18px] font-semibold leading-[26px] text-[#111a24] tracking-[0.03rem]">B.Sc. in Software Engineering <span class="text-[14px] text-[#f41a4a] block sm:inline sm:ml-[4px]">— Brigham Young University (BYU)</span></h4>
                                                    <p class="font-montserrat text-[14px] text-[#495461] font-normal leading-[24px]">Comprehensive engineering rigor spanning distributed systems, advanced algorithm design, software architecture, object-oriented paradigms, and cloud systems.</p>
                                                </div>
                                                <div class="bix-achievement-box left border-[1px] border-solid border-[#111a24] mt-[120px] p-[30px] rounded-[30px] bg-[#f7f5fb] relative max-[767px]:mt-[12px] max-[767px]:mb-[24px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">
                                                    <div class="achievement-year w-[100px] h-full absolute top-[0] flex items-center justify-center rotate-[270deg] z-[1] right-[-82px] max-[767px]:block max-[767px]:rotate-[0] max-[767px]:top-[-13px] max-[767px]:left-[45px]">
                                                        <span class="bg-[#111a24] text-[#fff] border-[1px] border-solid border-[#111a24] rounded-[10px] leading-[1] py-[3px] px-[10px] font-montserrat font-normal tracking-[0.02rem] text-[14px]">2024</span>
                                                    </div>
                                                    <h4 class="mb-[8px] font-montserrat text-[18px] font-semibold leading-[26px] text-[#111a24] tracking-[0.03rem]">GDG Best Developer of the Year <span class="text-[14px] text-[#f41a4a] block sm:inline sm:ml-[4px]">— Google Developer Groups</span></h4>
                                                    <p class="font-montserrat text-[14px] text-[#495461] font-normal leading-[24px]">Awarded for outstanding architectural contributions, local community engineering impact, and high-performance full-stack web application development.</p>
                                                </div>
                                            </div>
                                            <div class="bix-achievement-inner bix-border-l w-[50%] pl-[30px] border-l-[0.5px] border-solid border-[#111a24] max-[767px]:w-full max-[767px]:border-[0] max-[767px]:p-[0]">
                                                <div class="bix-achievement-box right border-[1px] border-solid border-[#111a24] mt-[120px] p-[30px] rounded-[30px] bg-[#f7f5fb] relative max-[767px]:mt-[12px] max-[767px]:mb-[24px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="500">
                                                    <div class="achievement-year w-[100px] h-full absolute top-[0] flex items-center justify-center rotate-[270deg] z-[1] left-[-82px] max-[767px]:block max-[767px]:rotate-[0] max-[767px]:top-[-13px] max-[767px]:left-[45px]">
                                                        <span class="bg-[#111a24] text-[#fff] border-[1px] border-solid border-[#111a24] rounded-[10px] leading-[1] py-[3px] px-[10px] font-montserrat font-normal tracking-[0.02rem] text-[14px]">Current</span>
                                                    </div>
                                                    <h4 class="mb-[8px] font-montserrat text-[18px] font-semibold leading-[26px] text-[#111a24] tracking-[0.03rem]">B.Sc. in Computer Science <span class="text-[14px] text-[#f41a4a] block sm:inline sm:ml-[4px]">— Imo State University (IMSU)</span></h4>
                                                    <p class="font-montserrat text-[14px] text-[#495461] font-normal leading-[24px]">2022 – Present. In-depth focus on theoretical computation, database normalization, relational calculi, operating systems, and computer networks.</p>
                                                </div>
                                                <div class="bix-achievement-box right border-[1px] border-solid border-[#111a24] mt-[120px] p-[30px] rounded-[30px] bg-[#f7f5fb] relative max-[767px]:mt-[12px] max-[767px]:mb-[24px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="700">
                                                    <div class="achievement-year w-[100px] h-full absolute top-[0] flex items-center justify-center rotate-[270deg] z-[1] left-[-82px] max-[767px]:block max-[767px]:rotate-[0] max-[767px]:top-[-13px] max-[767px]:left-[45px]">
                                                        <span class="bg-[#111a24] text-[#fff] border-[1px] border-solid border-[#111a24] rounded-[10px] leading-[1] py-[3px] px-[10px] font-montserrat font-normal tracking-[0.02rem] text-[14px]">Certified</span>
                                                    </div>
                                                    <h4 class="mb-[8px] font-montserrat text-[18px] font-semibold leading-[26px] text-[#111a24] tracking-[0.03rem]">AWS & PIM Project Management <span class="text-[14px] text-[#f41a4a] block sm:inline sm:ml-[4px]">— AWS & PIM</span></h4>
                                                    <p class="font-montserrat text-[14px] text-[#495461] font-normal leading-[24px]">AWS Cloud Practitioner Essentials; PIM Project Management Certification; CISCO Advanced JavaScript Certification (2020) & freeCodeCamp Algorithms.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Experience Tab -->
                            <div class="tab-activity-pane" id="experience">
                                <div class="flex flex-wrap w-full">
                                    <div class="w-full">
                                        <div class="bix-achievement relative flex w-full max-[767px]:flex-col">
                                            <div class="bix-achievement-inner bix-border-r w-[50%] pr-[30px] border-r-[0.5px] border-solid border-[#111a24] max-[767px]:w-full max-[767px]:border-[0] max-[767px]:p-[0]">
                                                <div class="bix-achievement-box left border-[1px] border-solid border-[#111a24] p-[30px] rounded-[30px] bg-[#f7f5fb] relative max-[767px]:mt-[12px] max-[767px]:mb-[24px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                                                    <div class="achievement-year w-[100px] h-full absolute top-[0] flex items-center justify-center rotate-[270deg] z-[1] right-[-82px] max-[767px]:block max-[767px]:rotate-[0] max-[767px]:top-[-13px] max-[767px]:left-[45px]">
                                                        <span class="bg-[#111a24] text-[#fff] border-[1px] border-solid border-[#111a24] rounded-[10px] leading-[1] py-[3px] px-[10px] font-montserrat font-normal tracking-[0.02rem] text-[14px]">2022-Pres</span>
                                                    </div>
                                                    <h4 class="mb-[8px] font-montserrat text-[18px] font-semibold leading-[26px] text-[#111a24] tracking-[0.03rem]">Founder, CEO & CTO <span class="text-[14px] text-[#f41a4a] block sm:inline sm:ml-[4px]">— Morex Web Enterprise Ltd</span></h4>
                                                    <p class="font-montserrat text-[14px] text-[#495461] font-normal leading-[24px]">Leading executive technical strategy, client-side architectural oversight, vendor auditing, and enterprise software engineering across registered ventures (BN: 8721238).</p>
                                                </div>
                                                <div class="bix-achievement-box left border-[1px] border-solid border-[#111a24] mt-[120px] p-[30px] rounded-[30px] bg-[#f7f5fb] relative max-[767px]:mt-[12px] max-[767px]:mb-[24px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">
                                                    <div class="achievement-year w-[100px] h-full absolute top-[0] flex items-center justify-center rotate-[270deg] z-[1] right-[-82px] max-[767px]:block max-[767px]:rotate-[0] max-[767px]:top-[-13px] max-[767px]:left-[45px]">
                                                        <span class="bg-[#111a24] text-[#fff] border-[1px] border-solid border-[#111a24] rounded-[10px] leading-[1] py-[3px] px-[10px] font-montserrat font-normal tracking-[0.02rem] text-[14px]">2023-Pres</span>
                                                    </div>
                                                    <h4 class="mb-[8px] font-montserrat text-[18px] font-semibold leading-[26px] text-[#111a24] tracking-[0.03rem]">Founder & Lead Architect <span class="text-[14px] text-[#f41a4a] block sm:inline sm:ml-[4px]">— FusionHub AI Systems</span></h4>
                                                    <p class="font-montserrat text-[14px] text-[#495461] font-normal leading-[24px]">Architected an intelligent headless CMS with RAG pipelines. Cut operating costs by 35% through AWS Serverless migration while maintaining 99.9% uptime SLA.</p>
                                                </div>
                                            </div>
                                            <div class="bix-achievement-inner bix-border-l w-[50%] pl-[30px] border-l-[0.5px] border-solid border-[#111a24] max-[767px]:w-full max-[767px]:border-[0] max-[767px]:p-[0]">
                                                <div class="bix-achievement-box right border-[1px] border-solid border-[#111a24] mt-[120px] p-[30px] rounded-[30px] bg-[#f7f5fb] relative max-[767px]:mt-[12px] max-[767px]:mb-[24px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="500">
                                                    <div class="achievement-year w-[100px] h-full absolute top-[0] flex items-center justify-center rotate-[270deg] z-[1] left-[-82px] max-[767px]:block max-[767px]:rotate-[0] max-[767px]:top-[-13px] max-[767px]:left-[45px]">
                                                        <span class="bg-[#111a24] text-[#fff] border-[1px] border-solid border-[#111a24] rounded-[10px] leading-[1] py-[3px] px-[10px] font-montserrat font-normal tracking-[0.02rem] text-[14px]">2023-24</span>
                                                    </div>
                                                    <h4 class="mb-[8px] font-montserrat text-[18px] font-semibold leading-[26px] text-[#111a24] tracking-[0.03rem]">Founder & Lead Engineer <span class="text-[14px] text-[#f41a4a] block sm:inline sm:ml-[4px]">— JOTAH ECOM NG LTD</span></h4>
                                                    <p class="font-montserrat text-[14px] text-[#495461] font-normal leading-[24px]">Designed an ACID double-entry PostgreSQL ledger and Redis distributed locking states, eliminating checkout race conditions and boosting conversion by +18%.</p>
                                                </div>
                                                <div class="bix-achievement-box right border-[1px] border-solid border-[#111a24] mt-[120px] p-[30px] rounded-[30px] bg-[#f7f5fb] relative max-[767px]:mt-[12px] max-[767px]:mb-[24px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="700">
                                                    <div class="achievement-year w-[100px] h-full absolute top-[0] flex items-center justify-center rotate-[270deg] z-[1] left-[-82px] max-[767px]:block max-[767px]:rotate-[0] max-[767px]:top-[-13px] max-[767px]:left-[45px]">
                                                        <span class="bg-[#111a24] text-[#fff] border-[1px] border-solid border-[#111a24] rounded-[10px] leading-[1] py-[3px] px-[10px] font-montserrat font-normal tracking-[0.02rem] text-[14px]">2023-24</span>
                                                    </div>
                                                    <h4 class="mb-[8px] font-montserrat text-[18px] font-semibold leading-[26px] text-[#111a24] tracking-[0.03rem]">Founder & Lead Developer <span class="text-[14px] text-[#f41a4a] block sm:inline sm:ml-[4px]">— Doccure Telemedicine</span></h4>
                                                    <p class="font-montserrat text-[14px] text-[#495461] font-normal leading-[24px]">Overhauled database schema, index clustering, and Redis caching to reduce consultation query latency by 50% with HIPAA-compliant encrypted PDF reports.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="section-services relative py-[50px] max-[991px]:py-[40px] max-[767px]:py-[35px]" id="services">
        <div class="flex flex-wrap justify-between relative items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full mb-[-24px]">
                <div class="w-full px-[12px]">
                    <div class="bix-banner mb-[30px] flex justify-center text-center max-[1199px]:mb-[25px] max-[767px]:mb-[20px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                        <h4 class="font-montserrat text-[36px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] max-[1199px]:text-[32px] max-[767px]:text-[28px] max-[575px]:text-[24px]">Core Engineering <span class="text-[#f41a4a]">Services</span></h4>
                    </div>
                    <div class="bix-banner-line mb-[80px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">
                        <span class="inner-border relative flex justify-center"></span>
                    </div>
                </div>
                <?php 
                $topServices = array_slice($services, 0, 4);
                $delay = 200;
                foreach ($topServices as $s): 
                ?>
                <div class="min-[1200px]:w-[25%] min-[768px]:w-[50%] w-full px-[12px] mb-[24px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="<?= $delay ?>">
                    <div class="bix-services mt-[30px] relative flex flex-col justify-center text-center rounded-[30px]" style="background-color: <?= e($s['bg_color']) ?>;">
                        <div class="services-image mt-[-30px] flex justify-center relative">
                            <div class="inner-image mt-[-7px] p-[20px] relative bg-[#fff] rounded-[50%] shadow-md">
                                <span class="ring h-[60px] w-[60px] m-auto border-[1px] border-solid border-[#111a24] absolute top-[8px] left-[8px] rounded-[50%] inline-block z-[5]"></span>
                                <?= picture_img($s['icon'], $s['title'], 'loading="lazy" decoding="async" class="h-[35px] w-[35px] relative z-[1]"') ?>
                            </div>
                        </div>
                        <div class="services-contact p-[30px]">
                            <h5 class="mb-[12px] font-montserrat text-[17px] text-[#111a24] font-bold tracking-[0.03rem] leading-[1.3]"><?= e($s['title']) ?></h5>
                            <p class="font-montserrat text-[13px] leading-[22px] text-[#495461] tracking-[0.02rem]"><?= e($s['summary']) ?></p>
                        </div>
                    </div>
                </div>
                <?php 
                    $delay += 200;
                endforeach; 
                ?>
                <div class="w-full text-center mt-[30px] mb-[24px]">
                    <a href="<?= url('services.php') ?>" class="inline-flex items-center font-montserrat text-[14px] font-bold text-[#111a24] hover:text-[#f41a4a] transition-all">
                        Explore All 8 Architecture Offerings <i class="ri-arrow-right-line ml-[6px]"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section class="section-projects py-[50px] max-[991px]:py-[40px] max-[767px]:py-[35px]" id="projects">
        <div class="flex flex-wrap justify-between relative items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full">
                <div class="w-full px-[12px]">
                    <div class="bix-banner mb-[30px] flex justify-center text-center max-[1199px]:mb-[25px] max-[767px]:mb-[20px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                        <h4 class="font-montserrat text-[36px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] max-[1199px]:text-[32px] max-[767px]:text-[28px] max-[575px]:text-[24px]">Flagship <span class="text-[#f41a4a]">Case Studies</span></h4>
                    </div>
                    <div class="bix-banner-line mb-[80px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">
                        <span class="inner-border relative flex justify-center"></span>
                    </div>
                </div>
                <div class="w-full projects-content">
                    <div class="controls bix-projects-tabs px-[12px] flex flex-col items-center" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                        <ul id="filters" class="clearfix mb-[30px] p-[5px] flex justify-center flex-wrap bg-[#f7f5fb] items-center border-[1px] border-solid border-[#1b1c20] rounded-[25px]">
                            <li class="filter m-[5px] py-[5px] px-[15px] transition-all duration-[0.25s] ease-out border-[0] rounded-[15px] font-montserrat text-[14px] leading-[26px] tracking-[0.03rem] text-[#111a24] cursor-pointer active" data-filter="all">All</li>
                            <li class="filter m-[5px] py-[5px] px-[15px] transition-all duration-[0.25s] ease-out border-[0] rounded-[15px] font-montserrat text-[14px] leading-[26px] tracking-[0.03rem] text-[#111a24] cursor-pointer" data-filter=".fintech">FinTech & Escrow</li>
                            <li class="filter m-[5px] py-[5px] px-[15px] transition-all duration-[0.25s] ease-out border-[0] rounded-[15px] font-montserrat text-[14px] leading-[26px] tracking-[0.03rem] text-[#111a24] cursor-pointer" data-filter=".ai">AI & Automation</li>
                            <li class="filter m-[5px] py-[5px] px-[15px] transition-all duration-[0.25s] ease-out border-[0] rounded-[15px] font-montserrat text-[14px] leading-[26px] tracking-[0.03rem] text-[#111a24] cursor-pointer" data-filter=".cloud">Cloud & DevOps</li>
                            <li class="filter m-[5px] py-[5px] px-[15px] transition-all duration-[0.25s] ease-out border-[0] rounded-[15px] font-montserrat text-[14px] leading-[26px] tracking-[0.03rem] text-[#111a24] cursor-pointer" data-filter=".healthtech">HealthTech</li>
                        </ul>
                    </div>
                    <div class="item-grid" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">
                        <div class="flex flex-wrap w-full mb-[-24px]">
                            <?php foreach ($projects as $proj): ?>
                            <div class="min-[1200px]:w-[33.33%] min-[768px]:w-[50%] w-full px-[12px] mb-[24px] item <?= e($proj['category_filter']) ?>">
                                <div class="bix-project-card">
                                    <div class="project-image relative px-[30px] pb-[30px] overflow-hidden rounded-[30px] z-[1] max-[480px]:px-[20px] max-[480px]:pb-[20px]">
                                        <a href="<?= asset($proj['image_large']) ?>" data-fancybox="gallery" class="flex rounded-[30px] overflow-hidden">
                                            <div class="overlay-project-card transition-all duration-[0.3s] ease-in-out opacity-[0] w-[calc(100%-60px)] h-[calc(100%-30px)] absolute top-[0] right-[30px] rounded-[30px] flex items-center justify-center bg-[#00000080] z-[45] overflow-hidden max-[480px]:p-[0] max-[480px]:w-[calc(100%-40px)] max-[480px]:h-[calc(100%-20px)] max-[480px]:top-[0] max-[480px]:bottom-[0] max-[480px]:right-[20px] max-[480px]:left-[20px]">
                                                <i class="ri-zoom-in-line text-[#fff] text-[32px]"></i>
                                            </div>
                                            <?= picture_img($proj['image'], $proj['title'], 'loading="lazy" decoding="async" class="transition-all duration-[0.3s] ease-in-out w-full object-cover h-[220px]"') ?>
                                        </a>
                                    </div>
                                    <div class="project-contact pt-[170px] pb-[30px] px-[30px] mt-[-175px] relative z-[0] border-[1px] border-solid border-[#1b1c20] rounded-[30px] max-[480px]:pb-[20px] max-[480px]:px-[20px] bg-[#fff]">
                                        <h5 class="mb-[2px] text-[18px] text-[#111a24] tracking-[0.03rem] leading-[1.2] font-medium">
                                            <a href="<?= url('single-project.php?slug=' . urlencode($proj['slug'])) ?>" class="transition-all duration-[0.3s] ease-in-out font-montserrat leading-[26px] tracking-[0.03rem] text-[18px] max-[1399px]:text-[17px] font-semibold text-[#111a24] hover:text-[#f41a4a]">
                                                <?= e($proj['title']) ?>
                                            </a>
                                        </h5>
                                        <span class="font-montserrat font-semibold leading-[26px] tracking-[0.02rem] text-[13px] text-[#f41a4a] block mb-[6px]">
                                            <?= e($proj['subtitle']) ?>
                                        </span>
                                        <p class="mt-[4px] font-montserrat text-[14px] font-normal tracking-[0.03rem] leading-[22px] text-[#495461]">
                                            <?= e($proj['summary']) ?>
                                            <a href="<?= url('single-project.php?slug=' . urlencode($proj['slug'])) ?>" class="text-[#111a24] font-semibold font-montserrat text-[14px] leading-[26px] tracking-[0.03rem] inline-flex items-center hover:text-[#f41a4a] ml-[4px]">
                                                Architecture Specs <i class="ri-arrow-right-up-line ml-[2px]"></i>
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Hire CTA Section -->
    <?php require_once __DIR__ . '/includes/cta.php'; ?>

    <!-- Testimonials Section -->
    <section class="section-testimonials relative py-[50px] max-[991px]:py-[40px] max-[767px]:py-[35px]">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full">
                <div class="w-full px-[12px]">
                    <div class="bix-banner mb-[30px] flex justify-center text-center max-[1199px]:mb-[25px] max-[767px]:mb-[20px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                        <h4 class="font-montserrat text-[36px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] max-[1199px]:text-[32px] max-[767px]:text-[28px] max-[575px]:text-[24px]">Executive <span class="text-[#f41a4a]">Endorsements</span></h4>
                    </div>
                    <div class="bix-banner-line mb-[80px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">
                        <span class="inner-border relative flex justify-center"></span>
                    </div>
                </div>
                <div class="w-full px-[12px]">
                    <div class="bix-testimonials relative" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="800">
                        <div class="owl-carousel testimonials-slider">
                            <?php foreach ($testimonials as $t): ?>
                            <div class="bix-testimonials-inner max-w-[900px] m-auto max-[1399px]:max-w-[800px]">
                                <div class="flex flex-wrap w-full items-center">
                                    <div class="min-[768px]:w-[33.33%] w-full px-[12px]">
                                        <div class="testimonials-image relative max-[767px]:w-full max-[767px]:mb-[20px] max-[767px]:flex max-[767px]:justify-center">
                                            <?= picture_img($t['image'], $t['name'], 'loading="lazy" decoding="async" class="w-full rounded-[20px] max-[767px]:max-w-[100px] shadow-md"') ?>
                                        </div>
                                    </div>
                                    <div class="min-[768px]:w-[66.66%] w-full px-[12px]">
                                        <div class="testimonials-contact h-full flex flex-col justify-end">
                                            <h4 class="mb-[4px] font-montserrat text-[20px] text-[#111a24] font-semibold leading-[1.2] tracking-[0.03rem] max-[767px]:text-[18px] max-[767px]:text-center"><?= e($t['name']) ?></h4>
                                            <span class="font-montserrat font-medium text-[14px] leading-[22px] tracking-[0.02rem] text-[#f41a4a] max-[767px]:text-center block mb-[10px]"><?= e($t['role']) ?> — <?= e($t['company']) ?></span>
                                            <div class="inner-contact border-[1px] border-solid border-[#111a24] p-[20px] bg-[#fff] rounded-[20px] shadow-sm">
                                                <p class="m-[0] font-montserrat text-[14px] text-[#495461] font-normal leading-[26px] tracking-[0.03rem]">“<?= e($t['content']) ?>”</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Spotlight Stories (Blog Section) -->
    <section class="section-blog pt-[50px] pb-[100px] max-[991px]:pt-[40px] max-[991px]:pb-[80px] max-[767px]:pt-[35px] max-[767px]:pb-[70px]" id="blog">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full">
                <div class="w-full px-[12px]">
                    <div class="bix-banner mb-[30px] flex justify-center text-center max-[1199px]:mb-[25px] max-[767px]:mb-[20px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                        <h4 class="font-montserrat text-[36px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] max-[1199px]:text-[32px] max-[767px]:text-[28px] max-[575px]:text-[24px]">Spotlight <span class="text-[#f41a4a]">Stories</span></h4>
                    </div>
                    <div class="bix-banner-line mb-[80px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">
                        <span class="inner-border relative flex justify-center"></span>
                    </div>
                </div>
                <div class="w-full px-[12px]">
                    <div class="owl-carousel bix-blog-wrap">
                        <?php foreach ($articles as $art): ?>
                        <div class="bix-blog-box mt-[53px] p-[20px] relative border-[1px] border-solid border-[#111a24] rounded-[30px] flex max-[1199px]:flex-col max-[1199px]:mt-[0] max-[767px]:max-w-[430px] max-[767px]:my-[0] max-[767px]:mx-auto bg-[#fff]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                            <div class="blog-img mr-[10px] absolute w-[200px] top-[-54px] left-[20px] right-[20px] max-[1199px]:mr-[0] max-[1199px]:mb-[10px] max-[1199px]:relative max-[1199px]:w-auto max-[1199px]:top-[0] max-[1199px]:left-[0] max-[1199px]:right-[0]">
                                <?= picture_img($art['image'], $art['title'], 'loading="lazy" decoding="async" class="w-[200px] h-[150px] object-cover rounded-[30px] max-[1199px]:w-full shadow-md"') ?>
                            </div>
                            <div class="blog-contact ml-[20px] pl-[200px] flex flex-col justify-center max-[1199px]:ml-[0] max-[1199px]:pl-[0]">
                                <span class="font-montserrat font-semibold leading-[26px] tracking-[0.02rem] text-[13px] text-[#f41a4a]"><?= e($art['category']) ?> &bull; <?= e($art['read_time']) ?></span>
                                <h4 class="mb-[36px] text-[18px] text-[#111a24] tracking-[0.03rem] leading-[1.3]">
                                    <a href="<?= url('blog-detail.php?slug=' . urlencode($art['slug'])) ?>" class="transition-all duration-[0.3s] ease-in-out font-montserrat text-[17px] leading-[24px] tracking-[0.03rem] font-bold text-[#111a24] hover:text-[#f41a4a]">
                                        <?= e($art['title']) ?>
                                    </a>
                                </h4>
                                <p class="overflow-hidden text-ellipsis mt-[32px] flex font-montserrat text-[13px] text-[#495461] font-normal leading-[22px] tracking-[0.02rem]">
                                    <?= e($art['excerpt']) ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
