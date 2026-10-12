<?php
declare(strict_types=1);

/**
 * Global Footer Partial
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12, Zero-Trust CSRF, Honeypot Protection, and Dynamic Tools.
 */

require_once dirname(__DIR__) . '/config/config.php';
?>

    <!-- Footer -->
    <footer class="bg-[#f7f5fb] py-[15px] border-t border-[#eae8ef]">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full">
                <div class="w-full px-[12px]">
                    <div class="bix-footer-contact flex justify-between items-center max-[600px]:flex-col max-[600px]:justify-center max-[600px]:text-center">
                        <p class="font-montserrat text-[14px] font-medium text-[#777] leading-[26px] tracking-[0.03rem] m-[0] max-[600px]:mb-[10px]">
                            &copy; <span id="copyright_year"><?= date('Y') ?></span> 
                            <a href="<?= url('index.php') ?>" class="font-semibold text-[#111a24] hover:text-[#f41a4a]"><?= e(PROFILE_COMPANY) ?></a> 
                            (BN: <?= e(PROFILE_REG_NO) ?>). All Rights Reserved.
                            <span class="mx-[6px] text-[#ccc]">|</span>
                            <a href="<?= url('privacy-policy.php') ?>" class="font-semibold hover:text-[#f41a4a] transition-colors">Privacy</a>
                            <span class="mx-[6px] text-[#ccc]">|</span>
                            <a href="<?= url('terms.php') ?>" class="font-semibold hover:text-[#f41a4a] transition-colors">Terms</a>
                            <span class="mx-[6px] text-[#ccc]">|</span>
                            <a href="<?= url('cookie-policy.php') ?>" class="font-semibold hover:text-[#f41a4a] transition-colors">Cookies</a>
                            <span class="mx-[6px] text-[#ccc]">|</span>
                            <button type="button" onclick="if(window.openCookiePreferences) window.openCookiePreferences();" class="font-semibold hover:text-[#f41a4a] cursor-pointer bg-transparent border-0 p-0 text-[#777] font-montserrat text-[14px] transition-colors">Preferences</button>
                        </p>
                        <div class="logo-links flex justify-center items-center">
                            <a href="<?= e(PROFILE_LINKEDIN) ?>" target="_blank" rel="noopener noreferrer" title="LinkedIn Profile" class="h-[32px] w-[32px] mx-[5px] relative flex items-center justify-center border-[1px] border-solid border-[#111a24] rounded-[20px] hover:bg-[#111a24] hover:text-[#fff] transition-all">
                                <i class="ri-linkedin-line text-[16px]"></i>
                            </a>
                            <a href="<?= e(PROFILE_GITHUB) ?>" target="_blank" rel="noopener noreferrer" title="GitHub Repository" class="h-[32px] w-[32px] mx-[5px] relative flex items-center justify-center border-[1px] border-solid border-[#111a24] rounded-[20px] hover:bg-[#111a24] hover:text-[#fff] transition-all">
                                <i class="ri-github-line text-[16px]"></i>
                            </a>
                            <a href="https://wa.me/<?= e(PROFILE_PHONE_RAW) ?>" target="_blank" rel="noopener noreferrer" title="Direct WhatsApp Line" class="h-[32px] w-[32px] mx-[5px] relative flex items-center justify-center border-[1px] border-solid border-[#111a24] rounded-[20px] hover:bg-[#111a24] hover:text-[#fff] transition-all">
                                <i class="ri-whatsapp-line text-[16px]"></i>
                            </a>
                            <a href="mailto:<?= e(PROFILE_EMAIL) ?>" title="Direct Email" class="h-[32px] w-[32px] mx-[5px] relative flex items-center justify-center border-[1px] border-solid border-[#111a24] rounded-[20px] hover:bg-[#111a24] hover:text-[#fff] transition-all">
                                <i class="ri-mail-line text-[16px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Tab to top -->
    <a href="#Top" class="back-to-top result-placeholder hidden w-[38px] h-[38px] fixed right-[15px] bottom-[15px] z-[10] rounded-[20px] cursor-pointer bg-[#fff] text-[#f41a4a] border-[1px] border-solid border-[#f41a4a] text-center text-[22px] leading-[1.6]">
        <i class="ri-arrow-up-line text-[20px]"></i>
        <div class="back-to-top-wrap active-progress">
            <svg viewBox="-1 -1 102 102" class="w-[36px] h-[36px] fixed right-[16px] bottom-[16px]">
                <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" class="fill-transparent stroke-[#f41a4a] stroke-[5px]"></path>
            </svg>
        </div>
    </a>

    <!-- Modal (Hire Me & Architectural Consultation) -->
    <div class="bix-modal-overlay w-full h-screen hidden fixed top-0 left-0 z-[50] bg-[#00000080] blur-[2px]"></div>
    <div class="bix-modal max-[575px]:w-full fixed top-[45%] max-[767px]:top-[50%] left-[50%] z-[51] max-[767px]:w-full hidden max-[767px]:max-h-full">
        <div class="bix-modal-dialog h-full my-[0%] transition-transform duration-[0.3s] ease-out mx-auto max-w-[520px] w-[520px] max-[575px]:max-w-full max-[575px]:w-full p-[0.5rem]">
            <div class="modal-content p-[24px] bg-[#fff] rounded-[30px] relative overflow-hidden shadow-2xl">
                <div class="modal-body">
                    <div class="bix-contact-tabs">
                        <div class="modal-header-text mb-[15px] text-center">
                            <h4 class="font-montserrat text-[22px] font-bold text-[#111a24]">Initiate Engagement</h4>
                            <p class="font-montserrat text-[13px] text-[#777] leading-[20px]">Direct channel to Jamachi Maurice (CTO & Lead Architect)</p>
                        </div>
                        <ul class="nav nav-tabs mb-[20px] flex justify-center items-center bg-[#f7f5fb] border-[1px] border-solid border-[#111a24] rounded-[25px]" id="contact_tabs">
                            <li class="nav-item m-[6px]">
                                <a href="#consultation_tab" class="py-[6px] px-[16px] rounded-[15px] capitalize font-medium text-[14px] block">Project / Advisory</a>
                            </li>
                            <li class="nav-item m-[6px]">
                                <a href="#recruiter_tab" class="py-[6px] px-[16px] rounded-[15px] capitalize font-medium text-[14px] block">Executive Role</a>
                            </li>
                        </ul>
                        
                        <!-- Dynamic Alert Box -->
                        <div id="modal-alert-box" class="hidden mb-[15px] p-[12px] rounded-[15px] text-[13px] font-montserrat"></div>

                        <div class="tab-content">
                            <!-- Tab 1: Project Consultation -->
                            <div class="tab-contact-pane" id="consultation_tab">
                                <form id="modal-consultation-form" class="ajax-portfolio-form" action="<?= url('config/mailer.php') ?>" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <!-- Honeypot Field -->
                                    <input type="text" name="website_hp" style="display:none;" tabindex="-1" autocomplete="off">
                                    <input type="hidden" name="service" value="Project / Architectural Consultation">

                                    <div class="form-group mb-[12px]">
                                        <input type="text" name="Name" required placeholder="Your Full Name *" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                    </div>
                                    <div class="form-group mb-[12px]">
                                        <input type="email" name="Email" required placeholder="Corporate or Direct Email *" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                    </div>
                                    <div class="form-group mb-[12px]">
                                        <select name="offer_method" class="form-select w-full h-[48px] px-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat text-[#555]">
                                            <option value="Full-Stack SaaS Platform">Full-Stack SaaS Platform (Laravel / Next.js)</option>
                                            <option value="FinTech Escrow & Double-Entry Ledger">FinTech Escrow & Double-Entry Ledger</option>
                                            <option value="AI & Autonomous Agent Pipeline">AI & Autonomous Agent Pipeline (RAG)</option>
                                            <option value="Client-Side Technical Oversight">Client-Side Technical Oversight & Vendor Audit</option>
                                            <option value="AWS Serverless Cloud Migration">AWS Serverless Cloud Migration</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-[12px]">
                                        <input name="budget" placeholder="Estimated Budget (e.g., $2,500 - $10,000)" type="text" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                    </div>
                                    <div class="form-group mb-[12px]">
                                        <textarea name="your-comment" required placeholder="Brief project scope, timeline, and objectives..." rows="3" class="w-full h-auto p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat"></textarea>
                                    </div>
                                    <div class="bix-review-buttons flex justify-between items-center">
                                        <button type="submit" class="submit-btn bix-button transition-all duration-[0.3s] ease-in-out py-[10px] px-[20px] font-montserrat text-[14px] font-semibold leading-[1.2] bg-[#f41a4a] text-[#fff] border-[1px] border-solid border-[#f41a4a] rounded-[15px] hover:bg-[#111a24] hover:border-[#111a24]">
                                            Submit Inquiry <i class="ri-send-plane-fill ml-[4px]"></i>
                                        </button>
                                        <button type="button" class="bix-close-modal transition-all duration-[0.3s] ease-in-out py-[10px] px-[18px] font-montserrat text-[14px] font-semibold leading-[1.2] bg-[#f7f5fb] text-[#111a24] border-[1px] border-solid border-[#111a24] rounded-[15px] hover:bg-[#111a24] hover:text-[#fff]">Close</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Tab 2: Executive Role -->
                            <div class="tab-contact-pane" id="recruiter_tab">
                                <form id="modal-executive-form" class="ajax-portfolio-form" action="<?= url('config/mailer.php') ?>" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="text" name="website_hp" style="display:none;" tabindex="-1" autocomplete="off">
                                    <input type="hidden" name="service" value="Executive Role / Fractional CTO Opportunity">

                                    <div class="form-group mb-[12px]">
                                        <input type="text" name="Name" required placeholder="Company / Recruiter Name *" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                    </div>
                                    <div class="form-group mb-[12px]">
                                        <input type="email" name="Email" required placeholder="Corporate Work Email *" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                    </div>
                                    <div class="form-group mb-[12px]">
                                        <select name="offer_method" class="form-select w-full h-[48px] px-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat text-[#555]">
                                            <option value="Fractional CTO Engagement">Fractional CTO / Technical Advisor</option>
                                            <option value="Senior AI / Software Architect">Senior AI / Software Architect (Remote)</option>
                                            <option value="Contract Senior Backend Lead">Contract Senior Backend Lead</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-[12px]">
                                        <input name="budget" placeholder="Comp Band / Retainer ex. $8,000+/Mo" type="text" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                    </div>
                                    <div class="form-group mb-[12px]">
                                        <textarea name="your-comment" required placeholder="Role details, tech stack expectations, or meeting proposal..." rows="3" class="w-full h-auto p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat"></textarea>
                                    </div>
                                    <div class="bix-review-buttons flex justify-between items-center">
                                        <button type="submit" class="submit-btn bix-button transition-all duration-[0.3s] ease-in-out py-[10px] px-[20px] font-montserrat text-[14px] font-semibold leading-[1.2] bg-[#f41a4a] text-[#fff] border-[1px] border-solid border-[#f41a4a] rounded-[15px] hover:bg-[#111a24] hover:border-[#111a24]">
                                            Send Proposal <i class="ri-send-plane-fill ml-[4px]"></i>
                                        </button>
                                        <button type="button" class="bix-close-modal transition-all duration-[0.3s] ease-in-out py-[10px] px-[18px] font-montserrat text-[14px] font-semibold leading-[1.2] bg-[#f7f5fb] text-[#111a24] border-[1px] border-solid border-[#111a24] rounded-[15px] hover:bg-[#111a24] hover:text-[#fff]">Close</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Feature tools (Site Language, Dark/Light Mode, RTL/LTR) -->
    <div class="bix-tools-sidebar-overlay w-full h-screen hidden fixed top-0 left-0 z-[41] bg-[#00000080] blur-[2px]"></div>
    <div class="bix-tools-sidebar w-[300px] h-screen fixed right-[0] top-[0] bg-[#fff] z-[42] transition-all duration-[0.3s] ease translate-x-[300px]">
        <a href="javascript:void(0)" class="bix-tools-sidebar-toggle in-out absolute top-[45%] right-[302px] w-[40px] h-[40px] bg-[#1d253199] transition-all ease-in duration-[0.3s] flex items-center justify-center text-[25px] z-[-1] rounded-[5px]">
            <i class="ri-settings-3-line text-[#fff]"></i>
        </a>
        <div class="bix-bar-title mb-[15px] p-[15px] flex flex-row justify-between items-center border-b-[1px] border-solid border-[#eae8ef]">
            <h6 class="m-[0] font-montserrat text-[17px] font-bold text-[#111a24] tracking-[0.03rem] leading-[1.2]">Aesthetics & Modes</h6>
            <a href="javascript:void(0)" class="close-tools text-[#ff0000] text-[17px]"><i class="ri-close-line"></i></a>
        </div>
        <div class="bix-tools-detail h-[calc(100vh-72px)] pb-[15px] px-[15px] overflow-auto">
            <div class="bix-tools-block">
                <h3 class="font-montserrat my-[15px] text-[14px] font-medium text-[#111a24] tracking-[0.03rem] leading-[1.2]">Site Language</h3>

                <?php
                // code => [label, ISO 3166-1 alpha-2 flag file]. en is the
                // page source language, so it maps to the gb flag.
                $jmnLanguages = [
                    'en'    => ['English',    'gb'],
                    'fr'    => ['French',     'fr'],
                    'es'    => ['Spanish',    'es'],
                    'de'    => ['German',     'de'],
                    'pt'    => ['Portuguese', 'pt'],
                    'it'    => ['Italian',    'it'],
                    'ru'    => ['Russian',    'ru'],
                    'ar'    => ['Arabic',     'sa'],
                    'zh-CN' => ['Chinese',    'cn'],
                    'ja'    => ['Japanese',   'jp'],
                    'hi'    => ['Hindi',      'in'],
                    'sw'    => ['Swahili',    'ke'],
                ];
                ?>

                <!-- Google's normal translate <select> is injected into
                     #jmn-translate-mount below and drives the actual
                     translation. This dropdown is a mirror of that select:
                     same value, same behaviour, but the trigger shows the
                     selected language's flag. Selecting an option writes the
                     value into Google's combo and fires its change event. -->
                <div class="jmn-lang-dd" id="jmn-lang-dd">
                    <button type="button"
                            id="jmn-lang-trigger"
                            class="jmn-lang-dd__trigger"
                            aria-haspopup="listbox"
                            aria-expanded="false"
                            aria-controls="jmn-lang-menu">
                        <img id="jmn-lang-current-flag"
                             src="<?= asset('img/flags/gb.svg') ?>"
                             alt=""
                             width="22"
                             height="15"
                             class="jmn-lang-dd__flag">
                        <span id="jmn-lang-current-label" class="jmn-lang-dd__label">English</span>
                        <i class="ri-arrow-down-s-line jmn-lang-dd__caret" aria-hidden="true"></i>
                    </button>

                    <ul id="jmn-lang-menu" class="jmn-lang-dd__menu" role="listbox" aria-label="Translate this page" hidden>
                        <?php foreach ($jmnLanguages as $jmnCode => [$jmnLabel, $jmnFlag]): ?>
                            <li class="jmn-lang-dd__option<?= $jmnCode === 'en' ? ' is-active' : '' ?>"
                                role="option"
                                tabindex="0"
                                data-lang="<?= e($jmnCode) ?>"
                                data-flag="<?= asset('img/flags/' . $jmnFlag . '.svg') ?>"
                                data-label="<?= e($jmnLabel) ?>"
                                aria-selected="<?= $jmnCode === 'en' ? 'true' : 'false' ?>">
                                <img src="<?= asset('img/flags/' . $jmnFlag . '.svg') ?>"
                                     alt=""
                                     width="22"
                                     height="15"
                                     class="jmn-lang-dd__flag">
                                <span class="jmn-lang-dd__option-label"><?= e($jmnLabel) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <p class="mb-[5px] mt-[12px] font-montserrat text-[11px] leading-[16px] tracking-[0.02rem] text-[#8a93a0]">
                    Translation is powered by Google Translate, which loads the first time you pick a language.
                </p>

                <!-- Google Translate injects its normal <select class="goog-te-combo">
                     here; the script itself is loaded on demand by assets/js/main.js.
                     The mount MUST NOT be display:none - Google's element.js refuses to
                     render its combo into a hidden container, so the visually-hidden
                     clip rules in style.css are used instead. -->
                <div id="jmn-translate-mount" class="sr-only" aria-hidden="true"></div>
            </div>
            <div class="bix-tools-block">
                <h3 class="font-montserrat my-[15px] text-[14px] font-medium text-[#111a24] tracking-[0.03rem] leading-[1.2]">Dark Mode Toggle</h3>
                <div class="bix-tools-dark m-[0] p-[0] flex flex-wrap flex-row justify-between">
                    <div class="mode-primary bix-tools-item mode w-[125px] mb-[10px] text-center active-dark-mode light" data-bix-mode-tool="light">
                        <img src="<?= asset('img/tools/light.png') ?>" alt="light mode" class="w-full p-[5px] rounded-[10px] border-[1px] border-solid border-[#eae8ef] hover:border-[#f41a4a]">
                        <p class="m-[0] capitalize font-montserrat text-[14px] text-[#495461] font-normal leading-[26px] tracking-[0.03rem]">Light Mode</p>
                    </div>
                    <div class="bix-tools-item w-[125px] mb-[10px] text-center mode dark" data-bix-mode-tool="dark">
                        <img src="<?= asset('img/tools/dark.png') ?>" alt="dark mode" class="w-full p-[5px] rounded-[10px] border-[1px] border-solid border-[#eae8ef] hover:border-[#f41a4a]">
                        <p class="m-[0] capitalize font-montserrat text-[14px] text-[#495461] font-normal leading-[26px] tracking-[0.03rem]">Dark Mode</p>
                    </div>
                </div>
            </div>
            <div class="bix-tools-block">
                <h3 class="font-montserrat my-[15px] text-[14px] font-medium text-[#111a24] tracking-[0.03rem] leading-[1.2]">Direction (LTR / RTL)</h3>
                <div class="bix-tools-rtl m-[0] p-[0] flex flex-wrap flex-row justify-between">
                    <div class="mode-primary bix-tools-item mode w-[125px] mb-[10px] text-center active-mode ltr" data-bix-mode-tool="ltr">
                        <img src="<?= asset('img/tools/ltr.png') ?>" alt="ltr direction" class="w-full p-[5px] rounded-[10px] border-[1px] border-solid border-[#eae8ef] hover:border-[#f41a4a]">
                        <p class="m-[0] capitalize font-montserrat text-[14px] text-[#495461] font-normal leading-[26px] tracking-[0.03rem]">LTR</p>
                    </div>
                    <div class="mode-primary bix-tools-item mode w-[125px] mb-[10px] text-center rtl" data-bix-mode-tool="rtl">
                        <img src="<?= asset('img/tools/rtl.png') ?>" alt="rtl direction" class="w-full p-[5px] rounded-[10px] border-[1px] border-solid border-[#eae8ef] hover:border-[#f41a4a]">
                        <p class="m-[0] capitalize font-montserrat text-[14px] text-[#495461] font-normal leading-[26px] tracking-[0.03rem]">RTL</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cookie Policy & Preferences Modal Box (GDPR & NDPR Real-Time Persistence) -->
    <?php require_once dirname(__DIR__) . '/includes/cookie_modal.php'; ?>

    <!-- Plugins -->
    <script src="<?= asset('js/vendor/jquery.min.js') ?>"></script>
    <script src="<?= asset('js/vendor/jquery.fancybox.min.js') ?>"></script>
    <script src="<?= asset('js/vendor/jquery.mixitup.min.js') ?>"></script>
    <script src="<?= asset('js/vendor/owl.carousel.min.js') ?>"></script>
    <script src="<?= asset('js/vendor/parallax.min.js') ?>"></script>
    <script src="<?= asset('js/vendor/aos.js') ?>"></script>
    <script src="<?= asset('js/vendor/smoothscroll.min.js') ?>"></script>

    <!-- Main JavaScript Core -->
    <script src="<?= asset('js/main.js') ?>"></script>

    <!-- Asynchronous CSRF-Protected Form Handler -->
    <script>
        $(function () {
            // Locate the alert box belonging to THIS form: walk up a few
            // levels looking for a .jmn-form-alert, else fall back to the
            // global modal alert.
            function findFormAlert($form) {
                var $node = $form.parent();
                for (var i = 0; i < 6 && $node.length; i++) {
                    var $found = $node.find('.jmn-form-alert').first();
                    if ($found.length) { return $found; }
                    $node = $node.parent();
                }
                return $('#modal-alert-box');
            }

            $('.ajax-portfolio-form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $form.find('.submit-btn');
                var $alert = findFormAlert($form);
                var originalBtnHtml = $btn.html();

                $btn.prop('disabled', true).html('<i class="ri-loader-4-line animate-spin mr-[4px]"></i> Processing...');
                $alert.addClass('hidden').removeClass('bg-green-100 text-green-800 border-green-300 bg-red-100 text-red-800 border-red-300');

                var formData = new FormData($form[0]);

                fetch($form.attr('action'), {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(function (response) {
                    return response.json();
                })
                .then(function (res) {
                    $btn.prop('disabled', false).html(originalBtnHtml);
                    if (res.success) {
                        $alert.removeClass('hidden')
                              .addClass('bg-green-100 text-green-800 border border-green-300')
                              .html('<strong>Success!</strong> ' + res.message);
                        $form[0].reset();
                        // Update CSRF token across all forms if rotated
                        if (res.new_csrf_token) {
                            $('input[name="csrf_token"]').val(res.new_csrf_token);
                        }
                    } else {
                        var errHtml = '<strong>Error:</strong> ' + (res.message || 'Submission failed.');
                        if (res.errors) {
                            errHtml += '<ul class="mt-[5px] list-disc pl-[18px]">';
                            for (var key in res.errors) {
                                errHtml += '<li>' + res.errors[key] + '</li>';
                            }
                            errHtml += '</ul>';
                        }
                        $alert.removeClass('hidden')
                              .addClass('bg-red-100 text-red-800 border border-red-300')
                              .html(errHtml);
                    }
                })
                .catch(function (err) {
                    $btn.prop('disabled', false).html(originalBtnHtml);
                    $alert.removeClass('hidden')
                          .addClass('bg-red-100 text-red-800 border border-red-300')
                          .html('<strong>Network Error:</strong> Failed to deliver your request. Please email directly to <?= e(PROFILE_EMAIL) ?>.');
                });
            });
        });
    </script>

</body>
</html>
