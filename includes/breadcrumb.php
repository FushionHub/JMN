<?php
declare(strict_types=1);

/**
 * Reusable Breadcrumb Partial
 * Jamachi Maurice Mauricennadi Portfolio Platform
 */

require_once dirname(__DIR__) . '/config/config.php';

$breadcrumbTitle = $breadcrumbTitle ?? 'Section';
$breadcrumbItems = $breadcrumbItems ?? [
    ['label' => 'Home', 'url' => url('index.php')],
    ['label' => $breadcrumbTitle, 'url' => ''],
];
?>
    <!-- Breadcrumb -->
    <section class="section-breadcrumb mb-[50px] relative max-[991px]:mb-[40px] max-[767px]:mb-[35px] bg-[url('<?= asset('img/breadcrumb/bg.jpg') ?>')] bg-cover bg-no-repeat bg-center relative bg-blend-overlay">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full">
                <div class="w-full px-[12px]">
                    <div class="bix-breadcrumb mt-[100px] py-[80px] overflow-hidden">
                        <div class="breadcrumb-overlay w-full h-full absolute top-[0] bottom-[0] right-[0] left-[0] bg-[#0009] z-[0]"></div>
                        <div class="breadcrumb-contact flex flex-col justify-center items-center z-[12] relative">
                            <h1 class="mb-[8px] font-montserrat text-[32px] font-semibold text-[#fff] capitalize tracking-[0.03rem] leading-[1.2] max-[991px]:mb-[4px] max-[991px]:text-[28px] max-[575px]:mb-[0] max-[575px]:text-[22px]">
                                <?= e($breadcrumbTitle) ?>
                            </h1>
                            <h5 class="cursor-default text-[#111a24] text-[1.25rem] leading-[1.2] flex items-center">
                                <?php foreach ($breadcrumbItems as $idx => $item): ?>
                                    <?php if (!empty($item['url'])): ?>
                                        <span class="px-[2px]">
                                            <a href="<?= e($item['url']) ?>" class="font-montserrat leading-[26px] tracking-[0.03rem] text-[15px] font-normal text-[#fff] hover:text-[#f41a4a] max-[575px]:text-[14px]">
                                                <?= e($item['label']) ?>
                                            </a>
                                        </span>
                                        <span class="px-[2px]">
                                            <i class="ri-arrow-right-s-line text-[14px] text-[#fff]"></i>
                                        </span>
                                    <?php else: ?>
                                        <span class="disable px-[2px] opacity-[0.7]">
                                            <span class="font-montserrat leading-[26px] tracking-[0.03rem] text-[15px] font-normal text-[#fff] max-[575px]:text-[14px] cursor-default">
                                                <?= e($item['label']) ?>
                                            </span>
                                        </span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
