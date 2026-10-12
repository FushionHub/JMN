<?php
declare(strict_types=1);

/**
 * Filterable Projects Gallery
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12, Strict Typing, and Full UI Component Preservation.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/data/projects.php';

$pageTitle       = 'Engineering Projects & Architecture Case Studies | ' . PROFILE_NAME;
$pageDescription = 'Explore architectural case studies by Jamachi Maurice: JOTAH ECOM (escrow multi-vendor ledger), FusionHub AI CMS (RAG automation), Doccure (HIPAA telemedicine), and SOKARLAB.';
$currentPage     = 'projects';
$canonicalUrl    = url('projects.php');

$projects   = get_all_projects();
$categories = get_project_categories();

require_once __DIR__ . '/includes/header.php';

$breadcrumbTitle = 'Projects & Case Studies';
$breadcrumbItems = [
    ['label' => 'Home', 'url' => url('index.php')],
    ['label' => 'Projects', 'url' => ''],
];
require_once __DIR__ . '/includes/breadcrumb.php';
?>

    <!-- Projects Section -->
    <section class="section-projects py-[50px] max-[991px]:py-[40px] max-[767px]:py-[35px]">
        <div class="flex flex-wrap justify-between relative items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full">
                <div class="w-full px-[12px]">
                    <div class="bix-banner mb-[30px] flex justify-center text-center max-[1199px]:mb-[25px] max-[767px]:mb-[20px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                        <h4 class="font-montserrat text-[36px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] max-[1199px]:text-[32px] max-[767px]:text-[28px] max-[575px]:text-[24px]">
                            Verified <span class="text-[#f41a4a]">Engineering Deployments</span>
                        </h4>
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
                            <li class="filter m-[5px] py-[5px] px-[15px] transition-all duration-[0.25s] ease-out border-[0] rounded-[15px] font-montserrat text-[14px] leading-[26px] tracking-[0.03rem] text-[#111a24] cursor-pointer" data-filter=".cloud">Cloud & Systems</li>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
