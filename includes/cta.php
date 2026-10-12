<?php
declare(strict_types=1);

/**
 * Reusable CTA Banner Partial: "Hire Me / Book Architectural Consultation"
 * Jamachi Maurice Mauricennadi Portfolio Platform
 */

require_once dirname(__DIR__) . '/config/config.php';

$ctaTitle    = $ctaTitle ?? 'Have an Architectural Challenge or Vision?';
$ctaSubtitle = $ctaSubtitle ?? 'Let’s engineer something robust, high-performing, and scalable together.';
$ctaDesc     = $ctaDesc ?? 'Available worldwide for Fractional CTO advisory, technical vendor audits, RAG AI systems, and high-concurrency cloud backends.';
?>
    <!-- Hire CTA Banner -->
    <section class="section-hire my-[50px] max-[991px]:my-[40px] max-[767px]:my-[35px] py-[120px] bg-[url('<?= asset('img/hire/bg.png') ?>')] bg-cover bg-center text-center relative z-[1] max-[767px]:py-[100px] max-[575px]:py-[80px]">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full">
                <div class="w-full px-[12px]">
                    <div class="bix-hire-banner">
                        <div class="overlay w-full h-full absolute top-[0] bottom-[0] right-[0] left-[0] bg-[#000000cc] z-[-1]"></div>
                        <div class="bix-hire-contact m-auto max-w-[750px]">
                            <h2 class="mb-[16px] font-montserrat text-[35px] max-[1199px]:text-[32px] max-[767px]:text-[28px] max-[575px]:text-[24px] text-[#fff] font-bold tracking-[0.03rem] leading-[1.2]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="200">
                                <?= e($ctaTitle) ?>
                            </h2>
                            <h4 class="mb-[16px] font-montserrat text-[20px] max-[575px]:text-[18px] font-semibold text-[#fff] tracking-[0.03rem] leading-[1.2]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                                <?= e($ctaSubtitle) ?>
                            </h4>
                            <p class="font-montserrat text-[15px] font-light text-[#fff] leading-[26px] tracking-[0.03rem]" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="600">
                                <?= e($ctaDesc) ?>
                            </p>
                            <div class="inner-circle-items mt-[40px] flex justify-center items-center" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="800">
                                <div class="bix-rounded-circle relative h-[150px] w-[150px] bg-[#fff] rounded-[50%] max-[575px]:w-[120px] max-[575px]:h-[120px] hover:scale-105 transition-transform duration-300">
                                    <a href="javascript:void(0)" class="relative bix-modal-toggle block w-full h-full" title="Book Consultation with Jamachi">
                                        <svg viewBox="0 0 100 100" width="100" height="100" class="h-full w-full">
                                            <defs>
                                                <path id="circle2" d=" M 50, 50 m -37, 0 a 37,37 0 1,1 74,0 a 37,37 0 1,1 -74,0"></path>
                                            </defs>
                                            <text>
                                                <textPath xlink:href="#circle2" class="text-[#111a24] font-semibold text-[10px] tracking-[1.3px]">
                                                    Hire Jamachi - Hire Jamachi - Hire -
                                                </textPath>
                                            </text>
                                        </svg>
                                        <div class="inner-contact absolute top-[0] left-[0] right-[0] flex justify-center items-center h-full w-full">
                                            <i class="ri-arrow-right-up-line text-[#111a24] text-[30px] leading-[26px] font-normal tracking-[0.03rem]"></i>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
