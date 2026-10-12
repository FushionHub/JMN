<?php
declare(strict_types=1);

/**
 * About Page Template
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12, Strict Typing, and Full UI Component Preservation.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/data/skills.php';
require_once __DIR__ . '/data/testimonials.php';

$pageTitle       = 'About ' . PROFILE_NAME . ' | Executive & Engineering Biography';
$pageDescription = 'In-depth profile of Jamachi Maurice Mauricennadi: Founder, CEO & CTO at Morex Web Enterprise Group of Companies Ltd, BYU Software Engineering graduate, and GDG Best Developer of the Year 2024.';
$currentPage     = 'about';
$canonicalUrl    = url('about.php');

$skillsMatrix = get_skills_matrix();
$testimonials = get_all_testimonials();

require_once __DIR__ . '/includes/header.php';

$breadcrumbTitle = 'About Me';
$breadcrumbItems = [
    ['label' => 'Home', 'url' => url('index.php')],
    ['label' => 'About', 'url' => ''],
];
require_once __DIR__ . '/includes/breadcrumb.php';
?>

    <!-- About Section -->
    <section class="section-about relative py-[50px] max-[991px]:py-[40px] max-[767px]:py-[35px]">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full mb-[-24px]">
                <div class="min-[992px]:w-[50%] w-full px-[12px] mb-[24px]">
                    <div class="bix-aboutpage-img relative">
                        <div class="inner-img flex justify-center items-center">
                            <?= picture_img('img/about/about-one.jpg', PROFILE_NAME, 'class="w-[calc(100%-50px)] h-[calc(100%-50px)] rounded-[40%] object-cover shadow-xl border-4 border-[#fff]"') ?>
                        </div>
                    </div>
                </div>
                <div class="min-[992px]:w-[50%] w-full px-[12px] mb-[24px]">
                    <div class="bix-about-contact h-full flex flex-col justify-center">
                        <div class="bix-banner mb-[20px] max-[1199px]:mb-[15px] flex text-left">
                            <h4 class="font-montserrat text-[36px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] max-[1199px]:text-[32px] max-[767px]:text-[28px] max-[575px]:text-[24px]">
                                Engineering <span class="text-[#f41a4a]">Philosophy & Journey</span>
                            </h4>
                        </div>
                        <h5 class="mb-[20px] font-montserrat text-[22px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.3] opacity-[0.9]">
                            “A hybrid engineer-founder combining 5+ years of hands-on technical architecture with the strategic acumen of an executive leader.”
                        </h5>
                        <p class="mb-[16px] font-montserrat text-[14px] text-[#495461] font-normal leading-[26px] tracking-[0.03rem]">
                            I design high-availability backend engines, autonomous AI agent pipelines (RAG), financial transaction ledgers with zero-trust escrow models, and scalable AWS cloud-native systems. My engineering ethos revolves around zero-compromise security, mathematically verifiable double-entry ledgers, and deterministic latency reduction.
                        </p>
                        <p class="mb-[20px] font-montserrat text-[14px] text-[#495461] font-normal leading-[26px] tracking-[0.03rem]">
                            As the Founder, CEO & CTO of <strong><?= e(PROFILE_COMPANY) ?></strong> (BN: <?= e(PROFILE_REG_NO) ?>), I bridge the divide between non-technical visionaries and complex engineering executions. Whether gatekeeping deliverable milestones or orchestrating microservices, I ensure every line of code advances measurable business value.
                        </p>
                        
                        <!-- Education & Honors Highlights -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-[24px] pt-[10px] border-t border-[#eae8ef]">
                            <div>
                                <h6 class="font-montserrat text-[14px] font-bold text-[#111a24] uppercase tracking-wider mb-[4px]">Education</h6>
                                <p class="font-montserrat text-[13px] text-[#555] leading-[20px]">
                                    &bull; <strong>B.Sc. Software Engineering</strong> (BYU, 2024)<br>
                                    &bull; <strong>B.Sc. Computer Science</strong> (IMSU, Current)
                                </p>
                            </div>
                            <div>
                                <h6 class="font-montserrat text-[14px] font-bold text-[#111a24] uppercase tracking-wider mb-[4px]">Accreditations</h6>
                                <p class="font-montserrat text-[13px] text-[#555] leading-[20px]">
                                    &bull; <strong>GDG Developer of the Year</strong> (2024)<br>
                                    &bull; <strong>AWS Cloud Practitioner</strong> &amp; PIM Project Mgmt
                                </p>
                            </div>
                        </div>

                        <div class="about-name">
                            <h6 class="mb-[2px] font-montserrat text-[16px] text-[#111a24] font-bold tracking-[0.03rem] leading-[1.2]"><?= e(PROFILE_NAME) ?></h6>
                            <p class="text-[13px] text-[#f41a4a] mb-[16px] font-montserrat font-semibold leading-[20px] tracking-[0.03rem]"><?= e(PROFILE_TITLE) ?></p>
                            <div class="inner-sign-items">
                                <?= picture_img('img/about/sign.png', 'signature', 'loading="lazy" decoding="async" class="white-mode w-[120px] inline') ?>
                                <?= picture_img('img/about/sign-dark.png', 'signature', 'loading="lazy" decoding="async" class="dark-mode w-[120px] hidden') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Skills Matrix Section -->
    <section class="section-Skill py-[50px] max-[991px]:py-[40px] max-[767px]:py-[35px]" id="skills">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="w-full px-[12px]">
                <div class="bix-banner mb-[30px] flex justify-center text-center max-[1199px]:mb-[25px] max-[767px]:mb-[20px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="200">
                    <h4 class="font-montserrat text-[36px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] max-[1199px]:text-[32px] max-[767px]:text-[28px] max-[575px]:text-[24px]">
                        Technical <span class="text-[#f41a4a]">Proficiency Matrix</span>
                    </h4>
                </div>
                <div class="bix-banner-line mb-[80px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="300">
                    <span class="inner-border relative flex justify-center"></span>
                </div>
            </div>
            <div class="flex flex-wrap w-full mb-[-24px]" id="progress">
                <?php 
                $bgColors = ['#faf1c6', '#ffd5df', '#d5f5ff', '#d6ffd5', '#e1e2ff', '#c6fbf4'];
                $idx = 0;
                foreach ($skillsMatrix as $key => $skill): 
                    $bg = $bgColors[$idx % count($bgColors)];
                    $idx++;
                ?>
                <div class="min-[992px]:w-[33.33%] min-[768px]:w-[50%] w-full px-[12px] mb-[24px]">
                    <div class="bix-skill bix-block-skill h-full block" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="<?= 300 + ($idx * 100) ?>">
                        <div class="skill-inner-box p-[20px] flex flex-col relative rounded-[30px] max-[420px]:rounded-[20px] shadow-sm border border-[#eae8ef]" style="background-color: <?= $bg ?>;">
                            <div class="skill-contact inline-flex flex-wrap max-[420px]:flex-col items-center">
                                <svg class="progress noselect w-[80px] h-full bg-transparent inline-block" data-progress="<?= $skill['percentage'] ?>" x="0px" y="0px" viewBox="0 0 80 80">
                                    <path class="track" d="M5,40a35,35 0 1,0 70,0a35,35 0 1,0 -70,0" />
                                    <path class="fill" d="M5,40a35,35 0 1,0 70,0a35,35 0 1,0 -70,0" />
                                    <text class="value" x="50%" y="58%">0%</text>
                                </svg>
                                <div class="skill-box-contact w-[calc(100%-80px)] p-[10px] flex flex-col justify-center max-[420px]:w-full">
                                    <h4 class="font-montserrat text-[16px] font-bold text-[#111a24] mb-[6px] tracking-[0.03rem] leading-[1.2]">
                                        <?= e($skill['category']) ?>
                                    </h4>
                                    <p class="font-montserrat text-[12px] leading-[18px] text-[#495461] tracking-[0.02rem] mb-[8px]">
                                        <?= e($skill['summary']) ?>
                                    </p>
                                    <div class="flex flex-wrap gap-1">
                                        <?php foreach (array_slice($skill['badges'], 0, 4) as $badge): ?>
                                            <span class="text-[11px] bg-[#fff] text-[#111a24] font-semibold px-[6px] py-[2px] rounded-[6px] border border-[#111a24]/10"><?= e($badge) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="section-testimonials pt-[50px] pb-[100px] max-[991px]:pt-[40px] max-[991px]:pb-[80px] max-[767px]:pt-[35px] max-[767px]:pb-[70px]">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full">
                <div class="w-full px-[12px]">
                    <div class="bix-banner mb-[30px] flex justify-center text-center max-[1199px]:mb-[25px] max-[767px]:mb-[20px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                        <h4 class="font-montserrat text-[36px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] max-[1199px]:text-[32px] max-[767px]:text-[28px] max-[575px]:text-[24px]">Client & Peer <span class="text-[#f41a4a]">Testimonials</span></h4>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
