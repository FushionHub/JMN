<?php
declare(strict_types=1);

/**
 * Services & Advisory Offerings Page
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12, Strict Typing, and Full UI Component Preservation.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/data/services.php';
require_once __DIR__ . '/data/testimonials.php';

$pageTitle       = 'Engineering & Consulting Services | ' . PROFILE_NAME;
$pageDescription = 'Full-Stack SaaS development, FinTech double-entry escrow ledgers, autonomous AI/RAG pipelines, client-side vendor auditing, and AWS serverless migrations.';
$currentPage     = 'services';
$canonicalUrl    = url('services.php');

$services     = get_all_services();
$pricingTiers = get_pricing_tiers();
$faqs         = get_service_faqs();
$testimonials = get_all_testimonials();

// FAQ rich results - only the questions actually rendered in the accordion
// further down this page are ever passed to seo_faq_graph().
$jsonLdBlocks = [
    seo_faq_graph($faqs, $canonicalUrl),
    seo_breadcrumb_graph([
        ['name' => 'Home', 'url' => url('index.php')],
        ['name' => 'Services', 'url' => $canonicalUrl],
    ]),
];

require_once __DIR__ . '/includes/header.php';

$breadcrumbTitle = 'Services & Solutions';
$breadcrumbItems = [
    ['label' => 'Home', 'url' => url('index.php')],
    ['label' => 'Services', 'url' => ''],
];
require_once __DIR__ . '/includes/breadcrumb.php';
?>

    <!-- Services Grid Section -->
    <section class="section-services py-[50px] max-[991px]:py-[40px] max-[767px]:py-[35px]" id="services">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full mb-[-24px]">
                <div class="w-full px-[12px]">
                    <div class="bix-banner mb-[30px] flex justify-center text-center max-[1199px]:mb-[25px] max-[767px]:mb-[20px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                        <h4 class="font-montserrat text-[36px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] max-[1199px]:text-[32px] max-[767px]:text-[28px] max-[575px]:text-[24px]">
                            Why Hire Me For Your Next <span class="text-[#f41a4a]">Initiative</span>
                        </h4>
                    </div>
                    <div class="bix-banner-line mb-[80px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">
                        <span class="inner-border relative flex justify-center"></span>
                    </div>
                </div>
                <?php 
                $delay = 200;
                foreach ($services as $srv): 
                ?>
                <div class="min-[1200px]:w-[25%] min-[768px]:w-[50%] w-full px-[12px] mb-[24px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="<?= $delay ?>">
                    <div class="bix-services mt-[30px] relative flex flex-col justify-center text-center rounded-[30px] shadow-sm hover:shadow-md transition-shadow" style="background-color: <?= e($srv['bg_color']) ?>;">
                        <div class="services-image mt-[-30px] flex justify-center relative">
                            <div class="inner-image mt-[-7px] p-[20px] relative bg-[#fff] rounded-[50%] shadow-md">
                                <span class="ring h-[60px] w-[60px] m-auto border-[1px] border-solid border-[#111a24] absolute top-[8px] left-[8px] rounded-[50%] inline-block z-[5]"></span>
                                <img src="<?= asset($srv['icon']) ?>" alt="<?= e($srv['title']) ?>" class="h-[35px] w-[35px] relative z-[1]">
                            </div>
                        </div>
                        <div class="services-contact p-[30px]">
                            <h5 class="mb-[12px] font-montserrat text-[17px] text-[#111a24] font-bold tracking-[0.03rem] leading-[1.3]"><?= e($srv['title']) ?></h5>
                            <p class="font-montserrat text-[13px] leading-[22px] text-[#495461] tracking-[0.02rem]"><?= e($srv['summary']) ?></p>
                        </div>
                    </div>
                </div>
                <?php 
                    $delay = ($delay >= 800) ? 200 : $delay + 200;
                endforeach; 
                ?>
            </div>
        </div>
    </section>

    <!-- Pricing / Engagement Tiers -->
    <section class="section-price py-[50px] max-[991px]:py-[40px] max-[767px]:py-[35px]" id="pricing">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full mb-[-24px]">
                <div class="w-full px-[12px]">
                    <div class="bix-banner mb-[30px] flex justify-center text-center max-[1199px]:mb-[25px] max-[767px]:mb-[20px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                        <h4 class="font-montserrat text-[36px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] max-[1199px]:text-[32px] max-[767px]:text-[28px] max-[575px]:text-[24px]">
                            Consulting & Engineering <span class="text-[#f41a4a]">Engagements</span>
                        </h4>
                    </div>
                    <div class="bix-banner-line mb-[80px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">
                        <span class="inner-border relative flex justify-center"></span>
                    </div>
                </div>
                <?php 
                $pDelay = 200;
                foreach ($pricingTiers as $tier): 
                ?>
                <div class="min-[1200px]:w-[33.33%] min-[768px]:w-[50%] w-full px-[12px] mb-[24px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="<?= $pDelay ?>">
                    <div class="bix-services-box transition-all duration-[0.3s] ease-in-out border-[1px] border-solid border-[#eae8ef] rounded-[30px] text-center bg-[#f7f5fb] hover:border-[#1b1c20] shadow-sm hover:shadow-lg h-full flex flex-col justify-between">
                        <div class="services-header transition-all duration-[0.3s] ease-in-out p-[24px] border-b-[1px] border-solid border-[#eae8ef]">
                            <span class="text-[13px] font-bold uppercase tracking-wider text-[#f41a4a] font-montserrat block mb-[4px]"><?= e($tier['badge']) ?></span>
                            <h3 class="text-[22px] font-bold text-[#111a24] font-montserrat mb-[8px]"><?= e($tier['name']) ?></h3>
                            <h4 class="text-[40px] font-extrabold text-[#111a24] tracking-[0.03rem] leading-[1.1] font-montserrat max-[767px]:text-[32px]">
                                <?= e($tier['price']) ?> <span class="text-[13px] font-medium text-[#777] block mt-[4px]"><?= e($tier['period']) ?></span>
                            </h4>
                            <p class="text-[13px] text-[#555] font-montserrat mt-[10px]"><?= e($tier['description']) ?></p>
                        </div>
                        <div class="services-inner-contact p-[24px] flex flex-col justify-between grow">
                            <ul class="mb-[24px] text-left space-y-2">
                                <?php foreach ($tier['features'] as $feat): ?>
                                <li class="text-[13px] leading-[22px] font-medium text-[#495461] font-montserrat flex items-start">
                                    <i class="ri-check-line text-green-600 font-bold text-[16px] mr-[8px] mt-[1px] shrink-0"></i>
                                    <span><?= e($feat) ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="buttons flex justify-center mt-auto">
                                <button type="button" class="bix-modal-toggle bix-button transition-all duration-[0.3s] ease-in-out py-[12px] px-[24px] font-montserrat text-[14px] font-semibold leading-[1.2] capitalize bg-[#111a24] text-[#fff] border-[1px] border-solid border-[#111a24] rounded-[15px] tracking-[0.03rem] hover:bg-[#f41a4a] hover:border-[#f41a4a] hover:text-[#fff] w-full">
                                    <?= e($tier['cta_text']) ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    $pDelay += 200;
                endforeach; 
                ?>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section-faq py-[50px] max-[991px]:py-[40px] max-[767px]:py-[35px]" id="faq">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full mb-[-24px]">
                <div class="w-full px-[12px]">
                    <div class="bix-banner mb-[30px] flex justify-center text-center max-[1199px]:mb-[25px] max-[767px]:mb-[20px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                        <h4 class="font-montserrat text-[36px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] max-[1199px]:text-[32px] max-[767px]:text-[28px] max-[575px]:text-[24px]">
                            Technical <span class="text-[#f41a4a]">Inquiries & FAQs</span>
                        </h4>
                    </div>
                    <div class="bix-banner-line mb-[80px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">
                        <span class="inner-border relative flex justify-center"></span>
                    </div>
                </div>
                
                <!-- Left Column FAQs -->
                <div class="min-[992px]:w-[50%] w-full px-[12px] mb-[24px]">
                    <div class="bix-faq" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                        <div class="bix-accordion">
                            <?php for ($i = 0; $i < 3 && $i < count($faqs); $i++): ?>
                            <div class="bix-accordion-item mb-[16px]">
                                <h4 class="bix-accordion-header cursor-pointer relative mb-[0] border-[1px] border-solid border-[#eae8ef] rounded-[15px] bg-[#f7f5fb] text-[#111a24] font-semibold text-[15px] p-[1.2rem] tracking-[0.03rem] <?= $i === 0 ? 'active-arrow' : '' ?>">
                                    <?= e($faqs[$i]['question']) ?>
                                </h4>
                                <div class="bix-accordion-body <?= $i === 0 ? 'show' : 'hidden' ?> rounded-b-[15px] border-x-[1px] border-b-[1px] border-solid border-[#eae8ef] p-[1.2rem] font-montserrat text-[14px] font-normal leading-[26px] tracking-[0.02rem] bg-[#fff] text-[#495461]">
                                    <?= e($faqs[$i]['answer']) ?>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column FAQs -->
                <div class="min-[992px]:w-[50%] w-full px-[12px] mb-[24px]">
                    <div class="bix-faq" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">
                        <div class="bix-accordion">
                            <?php for ($i = 3; $i < count($faqs); $i++): ?>
                            <div class="bix-accordion-item mb-[16px]">
                                <h4 class="bix-accordion-header cursor-pointer relative mb-[0] border-[1px] border-solid border-[#eae8ef] rounded-[15px] bg-[#f7f5fb] text-[#111a24] font-semibold text-[15px] p-[1.2rem] tracking-[0.03rem]">
                                    <?= e($faqs[$i]['question']) ?>
                                </h4>
                                <div class="bix-accordion-body hidden rounded-b-[15px] border-x-[1px] border-b-[1px] border-solid border-[#eae8ef] p-[1.2rem] font-montserrat text-[14px] font-normal leading-[26px] tracking-[0.02rem] bg-[#fff] text-[#495461]">
                                    <?= e($faqs[$i]['answer']) ?>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="section-testimonials pt-[50px] pb-[100px] max-[991px]:pt-[40px] max-[991px]:pb-[80px] max-[767px]:pt-[35px] max-[767px]:pb-[70px]">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full">
                <div class="w-full px-[12px]">
                    <div class="bix-banner mb-[30px] flex justify-center text-center max-[1199px]:mb-[25px] max-[767px]:mb-[20px]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                        <h4 class="font-montserrat text-[36px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] max-[1199px]:text-[32px] max-[767px]:text-[28px] max-[575px]:text-[24px]">What Clients <span class="text-[#f41a4a]">Experience</span></h4>
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
                                            <img src="<?= asset($t['image']) ?>" alt="<?= e($t['name']) ?>" class="w-full rounded-[20px] max-[767px]:max-w-[100px] shadow-md">
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
