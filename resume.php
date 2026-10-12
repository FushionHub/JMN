<?php
declare(strict_types=1);

/**
 * Interactive CV & Resume Showcase
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12, Strict Typing, and Full UI Component Preservation.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/data/projects.php';

$pageTitle       = 'Verified Curriculum Vitae & Resume | ' . PROFILE_NAME;
$pageDescription = 'Official resume and executive CV of Jamachi Maurice Mauricennadi. Senior Full-Stack Software Engineer, AI/ML Architect, Founder & CTO at Morex Web Enterprise Group of Companies Ltd.';
$currentPage     = 'resume';
$canonicalUrl    = url('resume.php');

$projects = get_all_projects();

require_once __DIR__ . '/includes/header.php';

$breadcrumbTitle = 'Curriculum Vitae';
$breadcrumbItems = [
    ['label' => 'Home', 'url' => url('index.php')],
    ['label' => 'Resume', 'url' => ''],
];
require_once __DIR__ . '/includes/breadcrumb.php';
?>

    <!-- Resume Section -->
    <section class="section-resume pt-[50px] pb-[100px] max-[991px]:pt-[40px] max-[991px]:pb-[80px] max-[767px]:pt-[35px] max-[767px]:pb-[70px]">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full">
                <!-- Sidebar -->
                <div class="min-[992px]:w-[33.33%] w-full px-[12px] mb-[30px]">
                    <div class="bix-resume-sidebar border-[1px] border-solid border-[#eae8ef] rounded-[20px] bg-[#f7f5fb] shadow-sm">
                        <div class="inner-img pt-[24px] px-[24px] max-[575px]:pt-[15px] max-[575px]:px-[15px]">
                            <img src="<?= asset('img/resume/resume.jpg') ?>" alt="<?= e(PROFILE_NAME) ?>" class="w-full rounded-[20px] shadow-md object-cover">
                        </div>
                        
                        <!-- Contact Info -->
                        <div class="inner-contact border-b-[1px] border-solid border-[#eae8ef] p-[24px]">
                            <div class="resume-title mb-[12px]">
                                <h4 class="font-montserrat text-[17px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] flex items-center">
                                    <i class="ri-contacts-fill mr-[10px] text-[#f41a4a]"></i> Contact Information
                                </h4>
                            </div>
                            <div class="resume-contact-info">
                                <ul class="m-[0] p-[0] space-y-2">
                                    <li class="py-[3px] flex items-center">
                                        <i class="ri-phone-line text-[15px] font-medium text-[#f41a4a] mr-[10px] shrink-0"></i>
                                        <a href="tel:<?= e(PROFILE_PHONE_RAW) ?>" class="text-[14px] font-medium text-[#777] font-montserrat hover:text-[#111a24]"><?= e(PROFILE_PHONE) ?></a>
                                    </li>
                                    <li class="py-[3px] flex items-center">
                                        <i class="ri-mail-open-line text-[15px] font-medium text-[#f41a4a] mr-[10px] shrink-0"></i>
                                        <a href="mailto:<?= e(PROFILE_EMAIL) ?>" class="text-[14px] font-medium text-[#777] font-montserrat hover:text-[#111a24]"><?= e(PROFILE_EMAIL) ?></a>
                                    </li>
                                    <li class="py-[3px] flex items-center">
                                        <i class="ri-building-line text-[15px] font-medium text-[#f41a4a] mr-[10px] shrink-0"></i>
                                        <a href="mailto:<?= e(PROFILE_CORP_EMAIL) ?>" class="text-[14px] font-medium text-[#777] font-montserrat hover:text-[#111a24]"><?= e(PROFILE_CORP_EMAIL) ?></a>
                                    </li>
                                    <li class="py-[3px] flex items-center">
                                        <i class="ri-global-line text-[15px] font-medium text-[#f41a4a] mr-[10px] shrink-0"></i>
                                        <a href="<?= e(PROFILE_VENTURE_URL) ?>" target="_blank" rel="noopener noreferrer" class="text-[14px] font-medium text-[#777] font-montserrat hover:text-[#111a24]"><?= e(str_replace('https://', '', PROFILE_VENTURE_URL)) ?></a>
                                    </li>
                                    <li class="py-[3px] flex items-start">
                                        <i class="ri-map-pin-line text-[15px] font-medium text-[#f41a4a] mr-[10px] shrink-0 mt-[2px]"></i>
                                        <span class="text-[14px] font-medium text-[#777] font-montserrat leading-[22px]"><?= e(PROFILE_LOCATION) ?> (<?= e(PROFILE_AVAILABILITY) ?>)</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Education & Honors -->
                        <div class="inner-contact border-b-[1px] border-solid border-[#eae8ef] p-[24px]">
                            <div class="resume-title mb-[12px]">
                                <h4 class="font-montserrat text-[17px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] flex items-center">
                                    <i class="ri-book-3-line mr-[10px] text-[#f41a4a]"></i> Education & Honors
                                </h4>
                            </div>
                            <div class="resume-education-info mb-[14px]">
                                <span class="font-montserrat leading-[22px] text-[13px] font-bold text-[#f41a4a]">2024</span>
                                <h5 class="text-[15px] font-bold leading-[22px] text-[#111a24] font-montserrat">B.Sc. in Software Engineering</h5>
                                <p class="text-[13px] leading-[20px] font-montserrat text-[#495461]">Brigham Young University (BYU)</p>
                            </div>
                            <div class="resume-education-info mb-[14px]">
                                <span class="font-montserrat leading-[22px] text-[13px] font-bold text-[#f41a4a]">2022 - Present</span>
                                <h5 class="text-[15px] font-bold leading-[22px] text-[#111a24] font-montserrat">B.Sc. in Computer Science</h5>
                                <p class="text-[13px] leading-[20px] font-montserrat text-[#495461]">Imo State University (IMSU)</p>
                            </div>
                            <div class="resume-education-info">
                                <span class="font-montserrat leading-[22px] text-[13px] font-bold text-[#f41a4a]">2024 Honor</span>
                                <h5 class="text-[15px] font-bold leading-[22px] text-[#111a24] font-montserrat">GDG Best Developer of the Year</h5>
                                <p class="text-[13px] leading-[20px] font-montserrat text-[#495461]">Google Developer Groups (GDG)</p>
                            </div>
                        </div>

                        <!-- Core Capabilities -->
                        <div class="inner-contact p-[24px]">
                            <div class="resume-title mb-[12px]">
                                <h4 class="font-montserrat text-[17px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] flex items-center">
                                    <i class="ri-settings-5-line mr-[10px] text-[#f41a4a]"></i> Core Tech Matrix
                                </h4>
                            </div>
                            <div class="resume-contact-info">
                                <div class="flex flex-wrap gap-1">
                                    <span class="text-[12px] bg-[#fff] text-[#111a24] font-semibold px-[8px] py-[3px] rounded-[6px] border border-[#111a24]/10">PHP 8.2+</span>
                                    <span class="text-[12px] bg-[#fff] text-[#111a24] font-semibold px-[8px] py-[3px] rounded-[6px] border border-[#111a24]/10">Laravel 11</span>
                                    <span class="text-[12px] bg-[#fff] text-[#111a24] font-semibold px-[8px] py-[3px] rounded-[6px] border border-[#111a24]/10">Node.js</span>
                                    <span class="text-[12px] bg-[#fff] text-[#111a24] font-semibold px-[8px] py-[3px] rounded-[6px] border border-[#111a24]/10">TypeScript</span>
                                    <span class="text-[12px] bg-[#fff] text-[#111a24] font-semibold px-[8px] py-[3px] rounded-[6px] border border-[#111a24]/10">PostgreSQL</span>
                                    <span class="text-[12px] bg-[#fff] text-[#111a24] font-semibold px-[8px] py-[3px] rounded-[6px] border border-[#111a24]/10">Redis Locks</span>
                                    <span class="text-[12px] bg-[#fff] text-[#111a24] font-semibold px-[8px] py-[3px] rounded-[6px] border border-[#111a24]/10">RAG AI / LLMs</span>
                                    <span class="text-[12px] bg-[#fff] text-[#111a24] font-semibold px-[8px] py-[3px] rounded-[6px] border border-[#111a24]/10">AWS Serverless</span>
                                    <span class="text-[12px] bg-[#fff] text-[#111a24] font-semibold px-[8px] py-[3px] rounded-[6px] border border-[#111a24]/10">React / Next.js</span>
                                    <span class="text-[12px] bg-[#fff] text-[#111a24] font-semibold px-[8px] py-[3px] rounded-[6px] border border-[#111a24]/10">Docker &amp; CI/CD</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Area -->
                <div class="min-[992px]:w-[66.66%] w-full px-[12px]">
                    <div class="bix-resume-contact">
                        <!-- Top Header with Instant Download Button -->
                        <div class="sub-heading mb-[24px] p-[24px] border-[1px] border-solid border-[#eae8ef] rounded-[20px] bg-[#f7f5fb] flex justify-between items-center flex-wrap gap-4 shadow-sm">
                            <div class="inner-heading">
                                <h3 class="mb-[4px] font-montserrat text-[26px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2]">
                                    <?= e(PROFILE_NAME) ?>
                                </h3>
                                <span class="text-[14px] font-semibold text-[#f41a4a] font-montserrat">
                                    <?= e(PROFILE_TITLE) ?> | <?= e(PROFILE_ROLE_BADGE) ?>
                                </span>
                            </div>
                            <div>
                                <a href="<?= e(PROFILE_RESUME_PDF) ?>" download="Jamachi_Maurice_Resume.pdf" class="inline-flex items-center py-[10px] px-[20px] font-montserrat text-[14px] font-bold bg-[#111a24] text-[#fff] rounded-[15px] hover:bg-[#f41a4a] transition-all shadow-md">
                                    <i class="ri-file-pdf-fill mr-[6px] text-[18px]"></i> Download PDF Resume
                                </a>
                            </div>
                        </div>

                        <div class="inner-resume-contact mb-[-24px]">
                            <!-- Executive Summary -->
                            <div class="resume-about mb-[24px] p-[24px] border-[1px] border-solid border-[#eae8ef] rounded-[20px] bg-[#f7f5fb] shadow-sm">
                                <div class="inner-title mb-[12px]">
                                    <h5 class="text-[18px] font-bold text-[#111a24] tracking-[0.03rem] font-montserrat">Executive Summary</h5>
                                </div>
                                <p class="text-[14px] mb-[12px] font-montserrat text-[#495461] font-normal leading-[26px] tracking-[0.03rem]">
                                    A results-driven hybrid engineer-founder combining 5+ years of hands-on technical architecture with the strategic acumen of an executive leader. Specialized in designing high-availability backend engines, autonomous AI agent pipelines (RAG), financial transaction ledgers with zero-trust escrow models, and scalable AWS cloud-native systems.
                                </p>
                                <p class="text-[14px] mb-[0] font-montserrat text-[#495461] font-normal leading-[26px] tracking-[0.03rem]">
                                    Proven record of cutting database query latencies by <strong>50%</strong>, reducing cloud infrastructure operating costs by <strong>35%</strong>, boosting checkout conversions by <strong>18%</strong>, and maintaining <strong>99.9% uptime</strong> across production platforms.
                                </p>
                            </div>

                            <!-- Experience Timeline -->
                            <div class="resume-experience mb-[24px] p-[24px] border-[1px] border-solid border-[#eae8ef] rounded-[20px] bg-[#f7f5fb] shadow-sm">
                                <div class="inner-title mb-[16px]">
                                    <h5 class="text-[18px] font-bold text-[#111a24] tracking-[0.03rem] font-montserrat">Professional Experience &amp; Leadership</h5>
                                </div>
                                
                                <!-- Role 1 -->
                                <div class="inner-experience mb-[12px] p-[20px] bg-[#fff] border-[1px] border-solid border-[#eae8ef] rounded-[15px]">
                                    <h5 class="mb-[4px] text-[16px] font-bold text-[#111a24] font-montserrat">Founder, CEO & CTO — MOREX WEB ENTERPRISE GROUP OF COMPANIES LTD</h5>
                                    <div class="experience-value mb-[10px] flex justify-between flex-wrap text-[13px] font-semibold text-[#f41a4a] font-montserrat">
                                        <span>Corporate Registration: BN 8721238</span>
                                        <span>2022 — Present (Hybrid / Nigeria)</span>
                                    </div>
                                    <p class="text-[14px] font-montserrat mb-[0] text-[#495461] leading-[24px]">
                                        Lead end-to-end technical strategy, architecture governance, and executive oversight for high-scale digital platforms. Provide Fractional CTO advisory and vendor auditing services to non-technical founders, ensuring deliverable integrity and milestone adherence.
                                    </p>
                                </div>

                                <!-- Role 2 -->
                                <div class="inner-experience mb-[12px] p-[20px] bg-[#fff] border-[1px] border-solid border-[#eae8ef] rounded-[15px]">
                                    <h5 class="mb-[4px] text-[16px] font-bold text-[#111a24] font-montserrat">Founder & Lead Architect — FusionHub AI CMS</h5>
                                    <div class="experience-value mb-[10px] flex justify-between flex-wrap text-[13px] font-semibold text-[#f41a4a] font-montserrat">
                                        <span>Next.js, Node.js, LangChain, AWS Serverless</span>
                                        <span>2023 — Present</span>
                                    </div>
                                    <p class="text-[14px] font-montserrat mb-[0] text-[#495461] leading-[24px]">
                                        Architected an intelligent headless CMS automating 80% of routine content drafting and RAG document analysis. Migrated monolithic services to AWS Serverless (Lambda, DynamoDB, S3), reducing infrastructure costs by 35% with a 99.9% uptime SLA.
                                    </p>
                                </div>

                                <!-- Role 3 -->
                                <div class="inner-experience p-[20px] bg-[#fff] border-[1px] border-solid border-[#eae8ef] rounded-[15px]">
                                    <h5 class="mb-[4px] text-[16px] font-bold text-[#111a24] font-montserrat">Founder & Lead Engineer — JOTAH ECOM NG LTD</h5>
                                    <div class="experience-value mb-[10px] flex justify-between flex-wrap text-[13px] font-semibold text-[#f41a4a] font-montserrat">
                                        <span>Laravel 11, PostgreSQL, Redis Distributed Locks, AWS</span>
                                        <span>2023 — 2024</span>
                                    </div>
                                    <p class="text-[14px] font-montserrat mb-[0] text-[#495461] leading-[24px]">
                                        Engineered Nigeria’s premier escrow-integrated multi-vendor marketplace. Implemented an ACID-compliant double-entry ledger in PostgreSQL and distributed Redis locks (SET NX EX) to eliminate race conditions and boost successful checkouts by +18%.
                                    </p>
                                </div>
                            </div>

                            <!-- Featured Case Studies -->
                            <div class="resume-project mb-[24px] p-[24px] border-[1px] border-solid border-[#eae8ef] rounded-[20px] bg-[#f7f5fb] shadow-sm">
                                <div class="inner-title mb-[16px]">
                                    <h5 class="text-[18px] font-bold text-[#111a24] tracking-[0.03rem] font-montserrat">Flagship Architectures</h5>
                                </div>
                                <div class="inner-project mb-[12px] p-[20px] border-[1px] border-solid border-[#eae8ef] bg-[#fff] rounded-[15px]">
                                    <h5 class="text-[16px] font-bold text-[#111a24] font-montserrat">JOTAH ECOM — FinTech & Escrow Ledger Engine</h5>
                                    <a href="<?= url('single-project.php?slug=jotah-ecom') ?>" class="text-[13px] font-bold text-[#f41a4a] inline-flex items-center my-[4px] hover:underline">
                                        Inspect Technical Case Study <i class="ri-arrow-right-up-line ml-[2px]"></i>
                                    </a>
                                    <p class="text-[14px] font-montserrat mb-[0] text-[#495461] leading-[24px]">Zero-discrepancy virtual accounting system with automated multi-party fund custody and atomic inventory decrement.</p>
                                </div>
                                <div class="inner-project p-[20px] border-[1px] border-solid border-[#eae8ef] bg-[#fff] rounded-[15px]">
                                    <h5 class="text-[16px] font-bold text-[#111a24] font-montserrat">Doccure — Telemedicine Platform (-50% Query Latency)</h5>
                                    <a href="<?= url('single-project.php?slug=doccure') ?>" class="text-[13px] font-bold text-[#f41a4a] inline-flex items-center my-[4px] hover:underline">
                                        Inspect Technical Case Study <i class="ri-arrow-right-up-line ml-[2px]"></i>
                                    </a>
                                    <p class="text-[14px] font-montserrat mb-[0] text-[#495461] leading-[24px]">Database query optimization, index clustering, Redis key-value caching, and HIPAA-compliant cryptographic medical report delivery.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
