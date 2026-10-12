<?php
declare(strict_types=1);

/**
 * Dynamic Case Study & Architecture Viewer
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12, Strict Typing, and Full UI Component Preservation.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/data/projects.php';

$allProjects = get_all_projects();
$slug = sanitize_text($_GET['slug'] ?? 'jotah-ecom');
$project = get_project_by_slug($slug);

// 404 Fallback if invalid slug
if (!$project) {
    http_response_code(404);
    $slug = 'jotah-ecom';
    $project = $allProjects['jotah-ecom'];
}

$pageTitle       = $project['title'] . ' | Technical Architecture Deep-Dive | ' . PROFILE_NAME;
$pageDescription = $project['summary'];
$currentPage     = 'single-project';
$canonicalUrl    = url('single-project.php?slug=' . urlencode($slug));
$ogImage         = asset($project['image_large']);

require_once __DIR__ . '/includes/header.php';

$breadcrumbTitle = $project['title'];
$breadcrumbItems = [
    ['label' => 'Home', 'url' => url('index.php')],
    ['label' => 'Projects', 'url' => url('projects.php')],
    ['label' => $project['title'], 'url' => ''],
];
require_once __DIR__ . '/includes/breadcrumb.php';
?>

    <!-- Single Project Section -->
    <section class="section-single-projects pt-[50px] pb-[100px] max-[991px]:pt-[40px] max-[991px]:pb-[80px] max-[767px]:pt-[35px] max-[767px]:pb-[70px]">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap mb-[-24px] w-full">
                <!-- Sidebar -->
                <div class="min-[1200px]:w-[25%] min-[992px]:w-[33.33%] w-full px-[12px] mb-[24px]">
                    <div class="bix-single-projects-sidebar border-[1px] border-solid border-[#eae8ef] bg-[#f7f5fb] rounded-[20px] shadow-sm">
                        <!-- Category Filter Links -->
                        <div class="bix-category p-[24px] border-b-[1px] border-solid border-[#eae8ef] max-[575px]:p-[15px]">
                            <div class="sub-title mb-[12px]">
                                <h4 class="text-[18px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] font-montserrat">Case Study Registry</h4>
                            </div>
                            <div class="category-contact">
                                <?php foreach ($allProjects as $pSlug => $pItem): ?>
                                <p>
                                    <a href="<?= url('single-project.php?slug=' . urlencode($pSlug)) ?>" class="transition-all duration-[0.3s] ease-in-out py-[6px] flex font-montserrat text-[13px] leading-[22px] tracking-[0.03rem] <?= $pSlug === $slug ? 'text-[#f41a4a] font-bold pl-[6px]' : 'text-[#495461] hover:pl-[5px] hover:text-[#111a24]' ?>">
                                        <i class="ri-arrow-right-s-line mr-[4px]"></i> <?= e($pItem['title']) ?>
                                    </a>
                                </p>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Flagship Spotlight Card -->
                        <?php 
                        $otherProjects = array_filter($allProjects, fn($k) => $k !== $slug, ARRAY_FILTER_USE_KEY);
                        $featuredOther = reset($otherProjects);
                        if ($featuredOther): 
                        ?>
                        <div class="bix-recent-post p-[24px] border-b-[1px] border-solid border-[#eae8ef] max-[575px]:p-[15px]">
                            <div class="sub-title mb-[12px]">
                                <h4 class="text-[16px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] font-montserrat">Related Architecture</h4>
                            </div>
                            <div class="bix-recent-card rounded-[20px]">
                                <div class="recent-img">
                                    <?= picture_img($featuredOther['image'], $featuredOther['title'], 'loading="lazy" decoding="async" class="w-full rounded-[15px] object-cover h-[130px]"') ?>
                                </div>
                                <div class="recent-contact pt-[12px]">
                                    <span class="text-[12px] font-semibold text-[#f41a4a] font-montserrat"><?= e($featuredOther['category_name']) ?></span>
                                    <h4 class="mb-[6px] text-[15px]">
                                        <a href="<?= url('single-project.php?slug=' . urlencode($featuredOther['slug'])) ?>" class="text-[15px] font-bold leading-[20px] text-[#111a24] font-montserrat hover:text-[#f41a4a]">
                                            <?= e($featuredOther['title']) ?>
                                        </a>
                                    </h4>
                                    <p class="text-[12px] leading-[18px] font-montserrat text-[#495461]"><?= e(substr($featuredOther['summary'], 0, 85)) ?>...</p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Latest Gallery -->
                        <div class="bix-latest-gallery p-[24px] max-[575px]:p-[15px]">
                            <div class="sub-title mb-[12px]">
                                <h4 class="text-[16px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] font-montserrat">Project Screenshots</h4>
                            </div>
                            <div class="bix-gallery-card grid grid-cols-3 gap-2">
                                <?php foreach ($project['gallery'] as $gImg): ?>
                                <a href="<?= asset($gImg) ?>" data-fancybox="project-gallery">
                                    <?= picture_img($gImg, 'screenshot', 'loading="lazy" decoding="async" class="w-full h-[60px] object-cover rounded-[10px] hover:opacity-80 transition-opacity"') ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="min-[1200px]:w-[75%] min-[992px]:w-[66.66%] w-full px-[12px] mb-[24px]">
                    <div class="bix-single-projects-contact">
                        <div class="flex flex-wrap mx-[-12px]">
                            <div class="min-[1200px]:w-[45%] w-full px-[12px] mb-[24px]">
                                <div class="inner-projects-img mb-[24px] sticky top-[110px] max-[1199px]:m-[0]">
                                    <a href="<?= asset($project['image_large']) ?>" data-fancybox="main-view">
                                        <?= picture_img($project['image_large'], $project['title'], 'class="w-full rounded-[25px] shadow-lg object-cover"') ?>
                                    </a>
                                    <div class="mt-[15px] p-[16px] bg-[#fff] border border-[#eae8ef] rounded-[20px]">
                                        <div class="text-[13px] font-montserrat text-[#777] mb-[4px]">Verified Role:</div>
                                        <div class="text-[15px] font-bold font-montserrat text-[#111a24]"><?= e($project['role']) ?></div>
                                        <?php if (!empty($project['live_url'])): ?>
                                        <div class="mt-[10px]">
                                            <a href="<?= e($project['live_url']) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-[13px] font-bold text-[#f41a4a] hover:underline">
                                                View Live Deployment <i class="ri-external-link-line ml-[4px]"></i>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="min-[1200px]:w-[55%] w-full px-[12px] mb-[24px]">
                                <div class="inner-projects-contact">
                                    <div class="sub-title mb-[10px]">
                                        <span class="text-[13px] font-bold uppercase tracking-wider text-[#f41a4a] font-montserrat"><?= e($project['category_name']) ?></span>
                                        <h2 class="text-[28px] font-bold leading-[36px] text-[#111a24] tracking-[0.03rem] font-montserrat max-[767px]:text-[22px] max-[767px]:leading-[30px] mt-[4px]">
                                            <?= e($project['title']) ?> — <?= e($project['subtitle']) ?>
                                        </h2>
                                    </div>
                                    <p class="mb-[18px] font-montserrat text-[14px] text-[#495461] font-normal leading-[26px] tracking-[0.03rem]">
                                        <?= e($project['summary']) ?>
                                    </p>
                                    
                                    <!-- Architectural Highlights List -->
                                    <div class="inner-contact mb-[20px]">
                                        <h4 class="mb-[10px] text-[18px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2] font-montserrat">Core Architectural Highlights</h4>
                                        <ul class="space-y-3 mb-[0]">
                                            <?php foreach ($project['architectural_highlights'] as $highlight): ?>
                                            <li class="text-[13px] font-medium font-montserrat text-[#495461] leading-[22px] flex items-start">
                                                <i class="ri-checkbox-circle-fill text-[#f41a4a] text-[16px] mr-[8px] mt-[2px] shrink-0"></i>
                                                <span><?= e($highlight) ?></span>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>

                                    <!-- Technology Stack Badges -->
                                    <div class="mb-[20px]">
                                        <h5 class="text-[14px] font-bold text-[#111a24] font-montserrat uppercase tracking-wider mb-[8px]">Technology Stack & Infrastructure</h5>
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach ($project['stack'] as $tech): ?>
                                                <span class="text-[12px] font-semibold font-montserrat bg-[#f7f5fb] text-[#111a24] border border-[#111a24]/15 px-[10px] py-[4px] rounded-[10px]">
                                                    <?= e($tech) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4 Stat Metric Cards -->
                        <div class="bix-product-details mt-[10px] mb-[30px]">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <?php
                                // Metrics live in MySQL, so they arrive in either shape:
                                //   {"Conversion": "+18%"}        -> label => value
                                //   [{"label":"Volume","value":"$85M+"}]
                                $metricCards = [];
                                foreach ((array) $project['metrics'] as $metricKey => $metricVal) {
                                    if (is_array($metricVal)) {
                                        $metricLabel = (string) ($metricVal['label'] ?? $metricVal['name'] ?? $metricKey);
                                        $metricValue = (string) ($metricVal['value'] ?? $metricVal['val'] ?? '');
                                    } else {
                                        $metricLabel = (string) $metricKey;
                                        $metricValue = (string) $metricVal;
                                    }
                                    if ($metricLabel !== '' && $metricValue !== '') {
                                        $metricCards[$metricLabel] = $metricValue;
                                    }
                                }
                                foreach ($metricCards as $label => $val): ?>
                                <div class="bix-product-details-card transition-all duration-[0.3s] p-[20px] bg-[#f7f5fb] border-[1px] border-solid border-[#eae8ef] rounded-[20px] text-center hover:border-[#1b1c20]">
                                    <h4 class="text-[13px] font-bold text-[#777] uppercase tracking-wider font-montserrat mb-[4px]"><?= e($label) ?></h4>
                                    <p class="text-[22px] font-bold text-[#f41a4a] font-montserrat leading-none"><?= e($val) ?></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Interactive Specification Tabs -->
                        <div class="bix-products-tabs mt-[40px]">
                            <ul class="nav nav-tabs inline-flex flex-wrap justify-center items-center bg-[#fff] border-[1px] border-solid border-[#111a24] rounded-[25px] mb-[24px]" id="bix_activity" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="200">
                                <li class="nav-item m-[6px]">
                                    <a href="#details" class="py-[6px] px-[16px] rounded-[15px] capitalize font-medium text-[15px] block">Problem & Solution</a>
                                </li>
                                <li class="nav-item m-[6px]">
                                    <a href="#reviews" class="py-[6px] px-[16px] rounded-[15px] capitalize font-medium text-[15px] block">System Architecture</a>
                                </li>
                                <li class="nav-item m-[6px]">
                                    <a href="#comment" class="py-[6px] px-[16px] rounded-[15px] capitalize font-medium text-[15px] block">Consultation Request</a>
                                </li>
                            </ul>
                            <div class="tab-content border-[1px] border-solid border-[#eae8ef] rounded-[20px] p-[25px] max-[575px]:p-[15px] bg-[#fff] shadow-sm" id="myTabContent" data-aos="fade-up" data-aos-duration="2000" data-aos-delay="400">
                                <!-- Tab 1: Problem & Solution -->
                                <div class="tab-activity-pane" id="details">
                                    <div class="products-tabs-details">
                                        <h4 class="font-montserrat text-[18px] font-bold text-[#111a24] mb-[8px]">The Engineering Problem</h4>
                                        <p class="mb-[18px] font-montserrat text-[14px] text-[#495461] leading-[26px]">
                                            <?= e($project['problem_statement']) ?>
                                        </p>
                                        <h4 class="font-montserrat text-[18px] font-bold text-[#111a24] mb-[8px]">Architectural Solution</h4>
                                        <p class="font-montserrat text-[14px] text-[#495461] leading-[26px]">
                                            <?= e($project['solution']) ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Tab 2: System Architecture -->
                                <div class="tab-activity-pane" id="reviews">
                                    <div class="products-tabs-details">
                                        <h4 class="font-montserrat text-[18px] font-bold text-[#111a24] mb-[8px]">Zero-Trust Architecture & Database Guarantees</h4>
                                        <p class="mb-[14px] font-montserrat text-[14px] text-[#495461] leading-[26px]">
                                            Every subsystem within <strong><?= e($project['title']) ?></strong> is engineered with zero-trust security principles. Balance states are enforced at the database transaction layer via serializable isolation levels and verified against external gateway webhooks through cryptographic signature verification.
                                        </p>
                                        <div class="p-[16px] bg-[#f7f5fb] border-l-4 border-[#111a24] rounded-[10px] mb-[16px]">
                                            <p class="font-mono text-[13px] text-[#111a24] leading-[22px] m-[0]">
                                                // Architectural Rule: No arbitrary UPDATE user SET balance = balance + :amount.<br>
                                                // All modifications require equal credit/debit transaction log pairs.
                                            </p>
                                        </div>
                                        <h5 class="font-montserrat text-[16px] font-bold text-[#111a24] mb-[6px]">Production SLA Status</h5>
                                        <p class="font-montserrat text-[14px] text-[#495461] leading-[24px]">
                                            Maintains a verified 99.9% uptime with automated error tracing and proactive alerting across all distributed nodes.
                                        </p>
                                    </div>
                                </div>

                                <!-- Tab 3: Consultation Request -->
                                <div class="tab-activity-pane" id="comment">
                                    <div class="bix-reviews-details">
                                        <div class="main-title mb-[16px]">
                                            <h4 class="font-montserrat text-[18px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2]">
                                                Inquire About Similar Architecture for Your Platform
                                            </h4>
                                            <p class="text-[13px] text-[#777] font-montserrat mt-[4px]">Direct inquiry to Jamachi Maurice for technical advisory or custom development.</p>
                                        </div>
                                        <div class="jmn-form-alert hidden mb-[12px] p-[12px] rounded-[15px] text-[13px] font-montserrat"></div>
                                        <form class="ajax-portfolio-form" action="<?= url('config/mailer.php') ?>" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <input type="hidden" name="return_to" value="<?= e(url('single-project.php?slug=' . ($project['slug'] ?? ''))) ?>">
                                            <input type="text" name="website_hp" style="display:none;" tabindex="-1" autocomplete="off">
                                            <input type="hidden" name="service" value="Case Study Inquiry: <?= e($project['title']) ?>">

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-[12px]">
                                                <input name="Name" required placeholder="Your Name *" type="text" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                                <input name="Email" required placeholder="Corporate Email *" type="email" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                            </div>
                                            <div class="mb-[12px]">
                                                <textarea name="your-comment" required placeholder="Describe your platform requirements, timeline, or engineering bottlenecks..." rows="4" class="w-full p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat"></textarea>
                                            </div>
                                            <div class="bix-review-buttons">
                                                <button type="submit" class="submit-btn bix-button transition-all duration-[0.3s] ease-in-out py-[10px] px-[24px] font-montserrat text-[14px] font-semibold leading-[1.2] bg-[#f41a4a] text-[#fff] border-[1px] border-solid border-[#f41a4a] rounded-[15px] hover:bg-[#111a24] hover:border-[#111a24]">
                                                    Submit Architectural Inquiry <i class="ri-send-plane-fill ml-[4px]"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
