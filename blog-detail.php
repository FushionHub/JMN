<?php
declare(strict_types=1);

/**
 * Dynamic Article Detail View
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12, Strict Typing, and Full UI Component Preservation.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/data/articles.php';

$allArticles = get_all_articles();
$slug = sanitize_text($_GET['slug'] ?? 'double-entry-ledger-architecture');
$article = get_article_by_slug($slug);

if (!$article) {
    http_response_code(404);
    $slug = 'double-entry-ledger-architecture';
    $article = $allArticles['double-entry-ledger-architecture'];
}

$pageTitle       = $article['title'] . ' | ' . PROFILE_NAME;
$pageDescription = $article['excerpt'];
$currentPage     = 'blog-detail';
$canonicalUrl    = url('blog-detail.php?slug=' . urlencode($slug));
$ogImage         = asset($article['image']);

require_once __DIR__ . '/includes/header.php';

$breadcrumbTitle = 'Architectural Essay';
$breadcrumbItems = [
    ['label' => 'Home', 'url' => url('index.php')],
    ['label' => 'Blog', 'url' => url('blog.php')],
    ['label' => $article['title'], 'url' => ''],
];
require_once __DIR__ . '/includes/breadcrumb.php';
?>

    <!-- Blog-details Section -->
    <section class="section section-blog-details pt-[50px] pb-[100px] max-[991px]:pt-[40px] max-[991px]:pb-[80px] max-[767px]:pt-[35px] max-[767px]:pb-[70px]">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full mb-[-24px]">
                <!-- Left Sidebar -->
                <div class="min-[992px]:w-[33.33%] w-full px-[12px] mb-[24px]">
                    <div class="bix-blog-sidebar border-[1px] border-solid border-[#eae8ef] rounded-[20px] bg-[#f7f5fb] shadow-sm">
                        <!-- Author Profile Card -->
                        <div class="inner-details border-b-[1px] border-solid border-[#eae8ef]">
                            <div class="blog-image pt-[24px] px-[24px] max-[575px]:px-[15px] max-[575px]:pt-[15px]">
                                <?= picture_img('img/resume/resume.jpg', PROFILE_NAME, 'loading="lazy" decoding="async" class="w-full rounded-[20px] shadow-sm object-cover"') ?>
                            </div>
                            <div class="inner-blog-contact p-[24px] max-[575px]:p-[15px]">
                                <span class="font-montserrat text-[13px] leading-[22px] tracking-[0.02rem] font-bold text-[#f41a4a] block"><?= e(PROFILE_ROLE_BADGE) ?></span>
                                <h4 class="mb-[6px] font-montserrat text-[18px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2]"><?= e(PROFILE_NAME) ?></h4>
                                <p class="m-[0] font-montserrat text-[13px] text-[#495461] font-normal leading-[22px] tracking-[0.02rem]">
                                    Founder, CEO & CTO at <?= e(PROFILE_COMPANY) ?>. Specializing in high-availability backend engines and autonomous AI agent workflows.
                                </p>
                            </div>
                        </div>

                        <!-- Categories -->
                        <div class="bix-categories p-[24px] max-[575px]:p-[15px] border-b-[1px] border-solid border-[#eae8ef]">
                            <div class="sub-title mb-[12px]">
                                <h4 class="font-montserrat text-[17px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2]">Article Domains</h4>
                            </div>
                            <div class="inner-categories">
                                <ul class="space-y-2 mb-[0]">
                                    <li class="leading-[24px]"><a href="<?= url('blog.php') ?>" class="font-montserrat leading-[24px] text-[13px] font-medium tracking-[0.03rem] text-[#777] hover:text-[#f41a4a] flex items-center"><i class="ri-arrow-right-s-line mr-[4px]"></i> FinTech &amp; Escrow Ledgers</a></li>
                                    <li class="leading-[24px]"><a href="<?= url('blog.php') ?>" class="font-montserrat leading-[24px] text-[13px] font-medium tracking-[0.03rem] text-[#777] hover:text-[#f41a4a] flex items-center"><i class="ri-arrow-right-s-line mr-[4px]"></i> AI &amp; Autonomous Agents (RAG)</a></li>
                                    <li class="leading-[24px]"><a href="<?= url('blog.php') ?>" class="font-montserrat leading-[24px] text-[13px] font-medium tracking-[0.03rem] text-[#777] hover:text-[#f41a4a] flex items-center"><i class="ri-arrow-right-s-line mr-[4px]"></i> Cloud &amp; AWS Serverless</a></li>
                                    <li class="leading-[24px]"><a href="<?= url('blog.php') ?>" class="font-montserrat leading-[24px] text-[13px] font-medium tracking-[0.03rem] text-[#777] hover:text-[#f41a4a] flex items-center"><i class="ri-arrow-right-s-line mr-[4px]"></i> Technical Vendor Auditing</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Recent Posts List -->
                        <div class="bix-blog-sub-details p-[24px] max-[575px]:p-[15px]">
                            <div class="sub-title mb-[14px]">
                                <h4 class="font-montserrat text-[17px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2]">Recent Essays</h4>
                            </div>
                            <?php foreach ($allArticles as $rSlug => $rArt): ?>
                            <div class="blog-rows-card flex rounded-[15px] mb-[18px] items-center">
                                <div class="blog-image shrink-0">
                                    <?= picture_img($rArt['image'], $rArt['title'], 'loading="lazy" decoding="async" class="w-[60px] h-[50px] object-cover rounded-[10px]"') ?>
                                </div>
                                <div class="inner-contact flex flex-col justify-center pl-[12px]">
                                    <h5 class="mb-[3px] text-[13px]">
                                        <a href="<?= url('blog-detail.php?slug=' . urlencode($rSlug)) ?>" class="font-montserrat tracking-[0.02rem] text-[13px] font-bold leading-[18px] text-[#111a24] hover:text-[#f41a4a]">
                                            <?= e(substr($rArt['title'], 0, 48)) ?>...
                                        </a>
                                    </h5>
                                    <p class="font-montserrat text-[11px] font-semibold text-[#f41a4a]"><?= e($rArt['date']) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="min-[992px]:w-[66.66%] w-full px-[12px] mb-[24px]">
                    <div class="bix-blog-contact">
                        <div class="inner-main-contact mb-[24px] border-[1px] border-solid border-[#eae8ef] rounded-[20px] bg-[#fff] shadow-sm overflow-hidden">
                            <div class="main-blog-image">
                                <?= picture_img('img/blog/blog-details.jpg', $article['title'], 'class="w-full h-[360px] object-cover rounded-t-[20px]"') ?>
                            </div>
                            <div class="main-blog-contact p-[24px] border-b-[1px] border-solid border-[#eae8ef] max-[575px]:p-[15px]">
                                <div class="inline-flex items-center gap-2 mb-[8px]">
                                    <span class="text-[13px] font-bold text-[#f41a4a] font-montserrat uppercase tracking-wider"><?= e($article['category']) ?></span>
                                    <span class="text-[13px] text-[#777]">&bull;</span>
                                    <span class="text-[13px] text-[#777] font-montserrat"><?= e($article['date']) ?></span>
                                    <span class="text-[13px] text-[#777]">&bull;</span>
                                    <span class="text-[13px] text-[#777] font-montserrat"><?= e($article['read_time']) ?></span>
                                </div>
                                <div class="main-title mb-[12px]">
                                    <h2 class="font-montserrat text-[26px] font-bold text-[#111a24] leading-[34px] tracking-[0.03rem] max-[575px]:text-[20px] max-[575px]:leading-[28px]">
                                        <?= e($article['title']) ?>
                                    </h2>
                                    <h4 class="font-montserrat text-[16px] font-medium text-[#777] mt-[6px]">
                                        <?= e($article['subtitle']) ?>
                                    </h4>
                                </div>
                                <div class="article-body-content mt-[20px]">
                                    <?= $article['content'] ?>
                                </div>
                            </div>

                            <!-- Two Column Architectural Diagram / Image Highlight -->
                            <div class="inner-blog-details-2colum p-[24px] max-[575px]:p-[15px]">
                                <div class="flex flex-wrap mx-[-12px]">
                                    <div class="min-[576px]:w-[50%] w-full px-[12px]">
                                        <div class="inner-blog-image mb-[20px]">
                                            <?= picture_img('img/blog/1.jpg', 'architecture 1', 'loading="lazy" decoding="async" class="w-full h-[180px] object-cover rounded-[15px]"') ?>
                                        </div>
                                    </div>
                                    <div class="min-[576px]:w-[50%] w-full px-[12px]">
                                        <div class="inner-blog-image mb-[20px]">
                                            <?= picture_img('img/blog/2.jpg', 'architecture 2', 'loading="lazy" decoding="async" class="w-full h-[180px] object-cover rounded-[15px]"') ?>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-[0] font-montserrat text-[14px] text-[#495461] font-normal leading-[26px] tracking-[0.03rem]">
                                    Building software that operates securely under enterprise production loads is both an engineering discipline and a leadership mindset. Architectural decisions made in the first 30 days of development dictate operational overhead, latency boundaries, and debugging complexity for years to come.
                                </p>
                            </div>
                        </div>

                        <!-- Technical Discussion & Comment Submission -->
                        <div class="inner-replies-contact p-[24px] border-[1px] border-solid border-[#eae8ef] rounded-[20px] max-[575px]:p-[15px] bg-[#fff] shadow-sm">
                            <div class="main-title mb-[16px]">
                                <h4 class="font-montserrat text-[18px] font-bold text-[#111a24] leading-[24px] tracking-[0.03rem]">
                                    Discuss Architecture or Request Consultation
                                </h4>
                                <p class="text-[13px] text-[#777] font-montserrat mt-[4px]">Have thoughts on this architectural breakdown or facing similar scalability hurdles?</p>
                            </div>

                            <div class="jmn-form-alert hidden mb-[12px] p-[12px] rounded-[15px] text-[13px] font-montserrat"></div>

                            <form class="ajax-portfolio-form" action="<?= url('config/mailer.php') ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="return_to" value="<?= e(url('blog-detail.php?slug=' . ($article['slug'] ?? ''))) ?>">
                                <input type="text" name="website_hp" style="display:none;" tabindex="-1" autocomplete="off">
                                <input type="hidden" name="service" value="Article Inquiry: <?= e($article['title']) ?>">

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-[12px]">
                                    <input name="Name" required placeholder="Your Name *" type="text" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                    <input name="Email" required placeholder="Your Email Address *" type="email" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                </div>
                                <div class="mb-[12px]">
                                    <textarea name="your-comment" required placeholder="Your technical inquiry, feedback, or consultation objective..." rows="4" class="w-full p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat"></textarea>
                                </div>
                                <div class="bix-review-buttons">
                                    <button type="submit" class="submit-btn bix-button transition-all duration-[0.3s] ease-in-out py-[10px] px-[24px] font-montserrat text-[14px] font-semibold leading-[1.2] bg-[#f41a4a] text-[#fff] border-[1px] border-solid border-[#f41a4a] rounded-[15px] hover:bg-[#111a24] hover:border-[#111a24]">
                                        Send Message <i class="ri-send-plane-fill ml-[4px]"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
