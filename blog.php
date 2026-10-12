<?php
declare(strict_types=1);

/**
 * Architectural Articles & Blog Directory
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12, Strict Typing, and Full UI Component Preservation.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/data/articles.php';

$pageTitle       = 'Engineering Insights & Architectural Essays | ' . PROFILE_NAME;
$pageDescription = 'Deep-dive technical essays on PostgreSQL double-entry ledgers, production RAG pipelines, AWS Serverless cost optimization, and agency codebase auditing.';
$currentPage     = 'blog';
$canonicalUrl    = url('blog.php');

$articles = get_all_articles();

require_once __DIR__ . '/includes/header.php';

$breadcrumbTitle = 'Architectural Essays';
$breadcrumbItems = [
    ['label' => 'Home', 'url' => url('index.php')],
    ['label' => 'Blog', 'url' => ''],
];
require_once __DIR__ . '/includes/breadcrumb.php';
?>

    <!-- Blog Section -->
    <section class="section-blog pt-[50px] pb-[100px] max-[991px]:pt-[40px] max-[991px]:pb-[80px] max-[767px]:pt-[35px] max-[767px]:pb-[70px]">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full mb-[-24px]">
                <?php 
                $delay = 400;
                foreach ($articles as $art): 
                ?>
                <div class="min-[768px]:w-[50%] w-full px-[12px] mb-[24px]">
                    <div class="bix-blog-box mt-[50px] p-[20px] relative border-[1px] border-solid border-[#111a24] rounded-[30px] flex max-[1199px]:flex-col max-[1199px]:mt-[0] max-[767px]:max-w-[430px] max-[767px]:my-[0] max-[767px]:mx-auto bg-[#fff] shadow-sm hover:shadow-md transition-shadow" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="<?= $delay ?>">
                        <div class="blog-img mr-[10px] absolute w-[200px] top-[-54px] left-[20px] right-[20px] max-[1199px]:mr-[0] max-[1199px]:mb-[10px] max-[1199px]:relative max-[1199px]:w-auto max-[1199px]:top-[0] max-[1199px]:left-[0] max-[1199px]:right-[0]">
                            <?= picture_img($art['image'], $art['title'], 'loading="lazy" decoding="async" class="w-[200px] h-[150px] object-cover rounded-[30px] max-[1199px]:w-full shadow-md"') ?>
                        </div>
                        <div class="blog-contact ml-[20px] pl-[200px] flex flex-col justify-center max-[1199px]:ml-[0] max-[1199px]:pl-[0]">
                            <span class="font-montserrat font-bold leading-[26px] tracking-[0.02rem] text-[13px] text-[#f41a4a]"><?= e($art['category']) ?> &bull; <?= e($art['read_time']) ?></span>
                            <h4 class="mb-[32px] text-[18px] text-[#111a24] tracking-[0.03rem] leading-[1.3]">
                                <a href="<?= url('blog-detail.php?slug=' . urlencode($art['slug'])) ?>" class="transition-all duration-[0.3s] ease-in-out font-montserrat text-[17px] leading-[24px] tracking-[0.03rem] font-bold text-[#111a24] hover:text-[#f41a4a]">
                                    <?= e($art['title']) ?>
                                </a>
                            </h4>
                            <p class="overflow-hidden text-ellipsis mt-[28px] flex font-montserrat text-[13px] text-[#495461] font-normal leading-[22px] tracking-[0.02rem] mb-[10px]">
                                <?= e($art['excerpt']) ?>
                            </p>
                            <a href="<?= url('blog-detail.php?slug=' . urlencode($art['slug'])) ?>" class="text-[13px] font-bold text-[#111a24] font-montserrat hover:text-[#f41a4a] inline-flex items-center">
                                Read Complete Breakdown <i class="ri-arrow-right-line ml-[4px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php 
                    $delay += 200;
                endforeach; 
                ?>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
