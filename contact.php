<?php
declare(strict_types=1);

/**
 * Contact & Consultation Reservation Page
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12, Strict Typing, and Zero-Trust form handling.
 *
 * Two intake modes, both handled by config/mailer.php:
 *   contact    - a normal brief / RFP
 *   reservation - an actual date + time slot (validated against Africa/Lagos)
 *
 * Either way the mailer dispatches to BOTH parties: the site owner's two
 * inboxes (PROFILE_CORP_EMAIL + PROFILE_EMAIL) and an acknowledgement back to
 * the sender, each rendered with the live site's visual language.
 */

require_once __DIR__ . '/config/config.php';

$pageTitle       = 'Contact & Book a Consultation | ' . PROFILE_NAME;
$pageDescription = 'Send a project brief or reserve a consultation slot with Jamachi Maurice Mauricennadi — Senior Full-Stack Software Engineer, AI/ML Architect and Founder/CTO of ' . PROFILE_COMPANY . '. Response within 24 hours.';
$pageKeywords    = 'Contact Jamachi Maurice, book software architect consultation, hire fractional CTO Nigeria, senior full-stack engineer contact, AI architect booking';
$currentPage     = 'contact';
$canonicalUrl    = url('contact.php');
$ogImage         = asset('img/hero/hero.jpg');

// Visible FAQ below feeds the FAQPage JSON-LD - keep both in sync.
$contactFaqs = [
    [
        'question' => 'How quickly will I get a reply after contacting you?',
        'answer'   => 'Every brief is read by me personally and answered within 24 hours, including weekends. If you reserved a slot, the confirmation email you receive immediately is the receipt; the substantive engineering reply follows within the same 24-hour window.',
    ],
    [
        'question' => 'What happens when I reserve a consultation slot?',
        'answer'   => 'You pick a date, a time and your timezone. The slot is written to the contact_inquiries table with inquiry_type = reservation, emailed to you and to ' . PROFILE_CORP_EMAIL . ', and held for you. You can request a change from the confirmation email or through this page.',
    ],
    [
        'question' => 'Do you work with clients outside Nigeria?',
        'answer'   => 'Yes. Engagement is worldwide and fully remote. Consultation slots are offered in Africa/Lagos (WAT) and you choose your own timezone on the form, so the time you see is the time you get. Contracts, invoicing and NDAs are handled in English.',
    ],
    [
        'question' => 'What do you need from me to quote a project?',
        'answer'   => 'A one-paragraph problem statement, the outcome you want, any fixed deadline, and an approximate budget band. If you have an existing codebase or vendor deliverables, mention them — I take over legacy systems regularly and can audit them before you commit.',
    ],
    [
        'question' => 'Is my message kept private?',
        'answer'   => 'Your brief is stored only in this project\'s own MySQL database and emailed to the two addresses above. There is no CRM, no ad pixel and no third-party analytics sharing it. You can request erasure at any time — see the Privacy Policy.',
    ],
    [
        'question' => 'Can I submit a role or recruiting proposal instead?',
        'answer'   => 'Yes. Use the "Executive Role" option in the engagement select, or open the Hire Jamachi button in the header for the dedicated recruiter form. Fractional CTO, contract backend lead and senior AI architect roles are all considered.',
    ],
];

$faqGraph = seo_faq_graph($contactFaqs, $canonicalUrl);
$jsonLdBlocks = array_filter(array_merge($jsonLdBlocks ?? [], [
    [
        '@type' => 'ContactPage',
        '@id'   => $canonicalUrl . '#contactpage',
        'url'   => $canonicalUrl,
        'name'  => $pageTitle,
        'description' => $pageDescription,
        'mainEntity' => [
            '@type' => 'Organization',
            '@id'   => url('') . '#organization',
            'contactPoint' => [
                '@type'          => 'ContactPoint',
                'contactType'    => 'sales',
                'email'          => PROFILE_CORP_EMAIL,
                'telephone'      => PROFILE_PHONE,
                'availableLanguage' => ['en'],
                'areaServed'     => 'Worldwide',
            ],
        ],
    ],
    $faqGraph,
    seo_breadcrumb_graph([
        ['name' => 'Home',    'url' => url('index.php')],
        ['name' => 'Contact', 'url' => $canonicalUrl],
    ]),
]), static fn (array $g): bool => $g !== []);

require_once __DIR__ . '/includes/header.php';

$breadcrumbTitle = 'Contact & Consultation';
$breadcrumbItems = [
    ['label' => 'Home', 'url' => url('index.php')],
    ['label' => 'Contact', 'url' => ''],
];
require_once __DIR__ . '/includes/breadcrumb.php';

$statusBanner = '';
$status = sanitize_text((string)($_GET['contact_status'] ?? ''));
if ($status === 'success') {
    $statusBanner = '<div class="mb-[20px] p-[16px] rounded-[18px] border border-green-300 bg-green-50 text-green-800 font-montserrat text-[14px] leading-[22px]"><strong>Message delivered.</strong> A confirmation has been emailed to you and to ' . e(PROFILE_CORP_EMAIL) . '. Expect a reply within 24 hours.</div>';
} elseif ($status === 'validation_error') {
    $statusBanner = '<div class="mb-[20px] p-[16px] rounded-[18px] border border-red-300 bg-red-50 text-red-800 font-montserrat text-[14px] leading-[22px]"><strong>We could not send that.</strong> Please review the highlighted fields and try again.</div>';
}

$sameDay = date('Y-m-d');
$minDate = $sameDay;
$csrf    = generate_csrf_token();
?>

    <!-- Contact & Reservation -->
    <section class="section-contact py-[50px] max-[991px]:py-[40px] max-[767px]:py-[35px] bg-[#fff]">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full">
                <div class="w-full px-[12px]">

                    <div class="mb-[30px] max-w-[760px]" data-aos="fade-up" data-aos-duration="1500">
                        <span class="inline-block py-[4px] px-[14px] mb-[14px] bg-[#fdf2f4] border-[1px] border-solid border-[#f41a4a]/30 rounded-[20px] font-montserrat text-[12px] font-bold uppercase tracking-wider text-[#f41a4a]">Direct Channel</span>
                        <h2 class="mb-[12px] font-montserrat text-[30px] max-[767px]:text-[26px] max-[575px]:text-[22px] font-bold text-[#111a24] leading-[1.25]">
                            Send a brief, or <span class="text-[#f41a4a]">reserve a slot</span>
                        </h2>
                        <p class="font-montserrat text-[15px] text-[#495461] leading-[26px]">
                            No ticket queue, no account required. Choose a general inquiry if you are still shaping the scope, or pick a date and time if you already know when you want to talk.
                        </p>
                    </div>

                    <div class="flex flex-wrap -mx-[12px]">

                        <!-- Left: direct details -->
                        <div class="w-full md:w-5/12 px-[12px] mb-[30px]" data-aos="fade-up" data-aos-duration="1500">
                            <div class="p-[24px] bg-[#f7f5fb] border-[1px] border-solid border-[#eae8ef] rounded-[24px] mb-[20px]">
                                <h4 class="mb-[16px] font-montserrat text-[17px] font-bold text-[#111a24]">Direct lines</h4>

                                <a href="mailto:<?= e(PROFILE_CORP_EMAIL) ?>" class="flex items-start gap-[12px] mb-[16px] group">
                                    <span class="h-[40px] w-[40px] shrink-0 rounded-[12px] bg-[#fff] border border-[#eae8ef] flex items-center justify-center text-[#f41a4a] text-[19px]"><i class="ri-mail-line"></i></span>
                                    <span>
                                        <span class="block font-montserrat text-[12px] font-bold uppercase tracking-wide text-[#777]">Corporate</span>
                                        <span class="block font-montserrat text-[14px] font-semibold text-[#111a24] group-hover:text-[#f41a4a] transition-colors break-all"><?= e(PROFILE_CORP_EMAIL) ?></span>
                                    </span>
                                </a>

                                <a href="mailto:<?= e(PROFILE_EMAIL) ?>" class="flex items-start gap-[12px] mb-[16px] group">
                                    <span class="h-[40px] w-[40px] shrink-0 rounded-[12px] bg-[#fff] border border-[#eae8ef] flex items-center justify-center text-[#f41a4a] text-[19px]"><i class="ri-contacts-book-line"></i></span>
                                    <span>
                                        <span class="block font-montserrat text-[12px] font-bold uppercase tracking-wide text-[#777]">Direct</span>
                                        <span class="block font-montserrat text-[14px] font-semibold text-[#111a24] group-hover:text-[#f41a4a] transition-colors break-all"><?= e(PROFILE_EMAIL) ?></span>
                                    </span>
                                </a>

                                <a href="https://wa.me/<?= e(PROFILE_PHONE_RAW) ?>" target="_blank" rel="noopener noreferrer" class="flex items-start gap-[12px] mb-[16px] group">
                                    <span class="h-[40px] w-[40px] shrink-0 rounded-[12px] bg-[#fff] border border-[#eae8ef] flex items-center justify-center text-[#f41a4a] text-[19px]"><i class="ri-whatsapp-line"></i></span>
                                    <span>
                                        <span class="block font-montserrat text-[12px] font-bold uppercase tracking-wide text-[#777]">WhatsApp</span>
                                        <span class="block font-montserrat text-[14px] font-semibold text-[#111a24] group-hover:text-[#f41a4a] transition-colors"><?= e(PROFILE_PHONE) ?></span>
                                    </span>
                                </a>

                                <div class="flex items-start gap-[12px]">
                                    <span class="h-[40px] w-[40px] shrink-0 rounded-[12px] bg-[#fff] border border-[#eae8ef] flex items-center justify-center text-[#f41a4a] text-[19px]"><i class="ri-map-pin-line"></i></span>
                                    <span>
                                        <span class="block font-montserrat text-[12px] font-bold uppercase tracking-wide text-[#777]">Base</span>
                                        <span class="block font-montserrat text-[14px] font-semibold text-[#111a24] leading-[22px]"><?= e(PROFILE_LOCATION) ?></span>
                                    </span>
                                </div>
                            </div>

                            <div class="p-[24px] bg-[#111a24] rounded-[24px]">
                                <div class="flex items-center gap-[8px] mb-[10px]">
                                    <span class="h-[9px] w-[9px] rounded-full bg-[#34d399] animate-ping"></span>
                                    <span class="font-montserrat text-[12px] font-bold uppercase tracking-wider text-[#34d399]">Availability</span>
                                </div>
                                <p class="font-montserrat text-[14px] text-[#cbd5e1] leading-[24px] mb-[14px]">
                                    <?= e(PROFILE_AVAILABILITY) ?>. New discovery calls open <strong class="text-[#fff]">Monday to Friday</strong>, and urgent escalations are handled on weekends.
                                </p>
                                <div class="flex flex-wrap gap-[8px]">
                                    <span class="py-[5px] px-[12px] rounded-[12px] bg-[#1f2a37] font-montserrat text-[12px] font-semibold text-[#cbd5e1]">Reply &lt; 24h</span>
                                    <span class="py-[5px] px-[12px] rounded-[12px] bg-[#1f2a37] font-montserrat text-[12px] font-semibold text-[#cbd5e1]">NDA friendly</span>
                                    <span class="py-[5px] px-[12px] rounded-[12px] bg-[#1f2a37] font-montserrat text-[12px] font-semibold text-[#cbd5e1]">Invoice in USD / NGN</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right: intake form -->
                        <div class="w-full md:w-7/12 px-[12px]" data-aos="fade-up" data-aos-duration="1500" data-aos-delay="200">
                            <div class="p-[24px] sm:p-[28px] bg-[#fff] border-[1px] border-solid border-[#111a24] rounded-[24px] shadow-sm">
                                <?= $statusBanner ?>

                                <ul class="nav nav-tabs mb-[20px] flex items-center bg-[#f7f5fb] border-[1px] border-solid border-[#111a24] rounded-[25px]" id="contact_page_tabs">
                                    <li class="nav-item m-[6px] grow">
                                        <a href="#contact_general_tab" class="py-[8px] px-[16px] rounded-[15px] capitalize font-medium text-[14px] block text-center font-montserrat">General inquiry</a>
                                    </li>
                                    <li class="nav-item m-[6px] grow">
                                        <a href="#contact_reservation_tab" class="py-[8px] px-[16px] rounded-[15px] capitalize font-medium text-[14px] block text-center font-montserrat">Reserve a slot</a>
                                    </li>
                                </ul>

                                <div id="contact_alert_box" class="jmn-form-alert hidden mb-[15px] p-[12px] rounded-[15px] text-[13px] font-montserrat"></div>

                                <div class="tab-content">

                                    <!-- Tab 1: General inquiry -->
                                    <div class="tab-contact-pane" id="contact_general_tab">
                                        <form class="ajax-portfolio-form" action="<?= url('config/mailer.php') ?>" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="inquiry_type" value="contact">
                                            <input type="hidden" name="return_to" value="<?= e(url('contact.php')) ?>">
                                            <input type="text" name="website_hp" style="display:none;" tabindex="-1" autocomplete="off">
                                            <input type="hidden" name="service" value="General Project Inquiry">

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-[12px] mb-[12px]">
                                                <div class="form-group">
                                                    <label for="ci_name" class="block mb-[6px] font-montserrat text-[13px] font-semibold text-[#111a24]">Full name *</label>
                                                    <input type="text" id="ci_name" name="Name" required maxlength="150" placeholder="Your full name" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                                </div>
                                                <div class="form-group">
                                                    <label for="ci_email" class="block mb-[6px] font-montserrat text-[13px] font-semibold text-[#111a24]">Email *</label>
                                                    <input type="email" id="ci_email" name="Email" required maxlength="150" placeholder="you@company.com" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                                </div>
                                                <div class="form-group">
                                                    <label for="ci_phone" class="block mb-[6px] font-montserrat text-[13px] font-semibold text-[#111a24]">Phone</label>
                                                    <input type="tel" id="ci_phone" name="phone" maxlength="30" placeholder="+234 ..." class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                                </div>
                                                <div class="form-group">
                                                    <label for="ci_company" class="block mb-[6px] font-montserrat text-[13px] font-semibold text-[#111a24]">Company</label>
                                                    <input type="text" id="ci_company" name="company" maxlength="150" placeholder="Organisation (optional)" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                                </div>
                                            </div>

                                            <div class="form-group mb-[12px]">
                                                <label for="ci_service" class="block mb-[6px] font-montserrat text-[13px] font-semibold text-[#111a24]">Engagement *</label>
                                                <select id="ci_service" name="offer_method" class="form-select w-full h-[48px] px-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat text-[#555]">
                                                    <option value="Full-Stack SaaS Platform">Full-Stack SaaS Platform (Laravel / Next.js)</option>
                                                    <option value="FinTech Escrow &amp; Double-Entry Ledger">FinTech Escrow &amp; Double-Entry Ledger</option>
                                                    <option value="AI &amp; Autonomous Agent Pipeline">AI &amp; Autonomous Agent Pipeline (RAG)</option>
                                                    <option value="Client-Side Technical Oversight">Client-Side Technical Oversight &amp; Vendor Audit</option>
                                                    <option value="AWS Serverless Cloud Migration">AWS Serverless Cloud Migration</option>
                                                    <option value="Executive Role / Fractional CTO Opportunity">Executive Role / Fractional CTO Opportunity</option>
                                                    <option value="General Project Inquiry">Something else / not sure yet</option>
                                                </select>
                                            </div>

                                            <div class="form-group mb-[12px]">
                                                <label for="ci_budget" class="block mb-[6px] font-montserrat text-[13px] font-semibold text-[#111a24]">Budget band</label>
                                                <input type="text" id="ci_budget" name="budget" maxlength="100" placeholder="e.g. $2,500 - $10,000" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                            </div>

                                            <div class="form-group mb-[18px]">
                                                <label for="ci_message" class="block mb-[6px] font-montserrat text-[13px] font-semibold text-[#111a24]">Your brief *</label>
                                                <textarea id="ci_message" name="your-comment" required rows="5" maxlength="8000" placeholder="What are you building, what is blocking you, and when does it need to ship?" class="w-full h-auto p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat leading-[24px]"></textarea>
                                            </div>

                                            <div class="flex flex-wrap justify-between items-center gap-[12px]">
                                                <p class="font-montserrat text-[12px] text-[#777] leading-[18px] m-0 max-w-[280px]">
                                                    Stored in this project's own database only. See the <a href="<?= url('privacy-policy.php') ?>" class="text-[#f41a4a] underline hover:text-[#111a24]">Privacy Policy</a>.
                                                </p>
                                                <button type="submit" class="submit-btn bix-button transition-all duration-[0.3s] ease-in-out py-[11px] px-[24px] font-montserrat text-[14px] font-semibold leading-[1.2] bg-[#f41a4a] text-[#fff] border-[1px] border-solid border-[#f41a4a] rounded-[15px] hover:bg-[#111a24] hover:border-[#111a24]">
                                                    Send brief <i class="ri-send-plane-fill ml-[4px]"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Tab 2: Reservation -->
                                    <div class="tab-contact-pane" id="contact_reservation_tab">
                                        <form class="ajax-portfolio-form" action="<?= url('config/mailer.php') ?>" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="inquiry_type" value="reservation">
                                            <input type="hidden" name="return_to" value="<?= e(url('contact.php')) ?>">
                                            <input type="text" name="website_hp" style="display:none;" tabindex="-1" autocomplete="off">
                                            <input type="hidden" name="service" value="Reserved Technical Discovery Consultation">

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-[12px] mb-[12px]">
                                                <div class="form-group">
                                                    <label for="cr_name" class="block mb-[6px] font-montserrat text-[13px] font-semibold text-[#111a24]">Full name *</label>
                                                    <input type="text" id="cr_name" name="Name" required maxlength="150" placeholder="Your full name" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                                </div>
                                                <div class="form-group">
                                                    <label for="cr_email" class="block mb-[6px] font-montserrat text-[13px] font-semibold text-[#111a24]">Email *</label>
                                                    <input type="email" id="cr_email" name="Email" required maxlength="150" placeholder="you@company.com" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                                </div>
                                                <div class="form-group">
                                                    <label for="cr_phone" class="block mb-[6px] font-montserrat text-[13px] font-semibold text-[#111a24]">Phone / WhatsApp</label>
                                                    <input type="tel" id="cr_phone" name="phone" maxlength="30" placeholder="+234 ..." class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                                </div>
                                                <div class="form-group">
                                                    <label for="cr_company" class="block mb-[6px] font-montserrat text-[13px] font-semibold text-[#111a24]">Company</label>
                                                    <input type="text" id="cr_company" name="company" maxlength="150" placeholder="Organisation (optional)" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-[12px] mb-[12px] p-[15px] bg-[#f7f5fb] border-[1px] border-solid border-[#eae8ef] rounded-[20px]">
                                                <div class="form-group">
                                                    <label for="cr_date" class="block mb-[6px] font-montserrat text-[13px] font-semibold text-[#111a24]">Date *</label>
                                                    <input type="date" id="cr_date" name="reserved_date" required min="<?= e($minDate) ?>" class="w-full h-[48px] p-[12px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat bg-[#fff]">
                                                </div>
                                                <div class="form-group">
                                                    <label for="cr_time" class="block mb-[6px] font-montserrat text-[13px] font-semibold text-[#111a24]">Time *</label>
                                                    <input type="time" id="cr_time" name="reserved_time" required step="900" class="w-full h-[48px] p-[12px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat bg-[#fff]">
                                                </div>
                                                <div class="form-group">
                                                    <label for="cr_zone" class="block mb-[6px] font-montserrat text-[13px] font-semibold text-[#111a24]">Timezone</label>
                                                    <select id="cr_zone" name="reserved_zone" class="form-select w-full h-[48px] px-[12px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat text-[#555] bg-[#fff]">
                                                        <option value="WAT (UTC+1)">WAT (UTC+1)</option>
                                                        <option value="UTC">UTC</option>
                                                        <option value="GMT (UTC+0)">GMT (UTC+0)</option>
                                                        <option value="WEST (UTC+1)">WEST (UTC+1)</option>
                                                        <option value="EST (UTC-5)">EST (UTC-5)</option>
                                                        <option value="PST (UTC-8)">PST (UTC-8)</option>
                                                        <option value="IST (UTC+5:30)">IST (UTC+5:30)</option>
                                                        <option value="GST (UTC+4)">GST (UTC+4)</option>
                                                        <option value="SGT (UTC+8)">SGT (UTC+8)</option>
                                                    </select>
                                                </div>
                                                <p class="sm:col-span-3 font-montserrat text-[12px] text-[#777] leading-[18px] m-0">
                                                    Slot length: <strong class="text-[#111a24]">60 minutes</strong>. Times are stored against <strong class="text-[#111a24]">Africa/Lagos</strong>; the confirmation email shows the slot in the timezone you selected above.
                                                </p>
                                            </div>

                                            <div class="form-group mb-[12px]">
                                                <label for="cr_budget" class="block mb-[6px] font-montserrat text-[13px] font-semibold text-[#111a24]">Budget band</label>
                                                <input type="text" id="cr_budget" name="budget" maxlength="100" placeholder="e.g. $2,500 - $10,000" class="w-full h-[48px] p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat">
                                            </div>

                                            <div class="form-group mb-[18px]">
                                                <label for="cr_message" class="block mb-[6px] font-montserrat text-[13px] font-semibold text-[#111a24]">Agenda / what you want out of the call *</label>
                                                <textarea id="cr_message" name="your-comment" required rows="4" maxlength="8000" placeholder="Two or three sentences on the decision you need to make in that hour." class="w-full h-auto p-[15px] border-[1px] border-solid border-[#eae8ef] outline-[0] rounded-[20px] text-[14px] font-montserrat leading-[24px]"></textarea>
                                            </div>

                                            <div class="flex flex-wrap justify-between items-center gap-[12px]">
                                                <p class="font-montserrat text-[12px] text-[#777] leading-[18px] m-0 max-w-[280px]">
                                                    Confirmation goes to <strong class="text-[#111a24]">both</strong> your inbox and <?= e(PROFILE_CORP_EMAIL) ?>.
                                                </p>
                                                <button type="submit" class="submit-btn bix-button transition-all duration-[0.3s] ease-in-out py-[11px] px-[24px] font-montserrat text-[14px] font-semibold leading-[1.2] bg-[#f41a4a] text-[#fff] border-[1px] border-solid border-[#f41a4a] rounded-[15px] hover:bg-[#111a24] hover:border-[#111a24]">
                                                    Reserve slot <i class="ri-calendar-check-line ml-[4px]"></i>
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

    <!-- Visible FAQ (mirrors the FAQPage JSON-LD emitted in <head>) -->
    <section class="section-faq py-[50px] max-[991px]:py-[40px] max-[767px]:py-[35px] bg-[#fff]" id="faq">
        <div class="flex flex-wrap justify-between items-center mx-auto min-[1400px]:max-w-[1320px] min-[1200px]:max-w-[1140px] min-[992px]:max-w-[960px] min-[768px]:max-w-[720px] min-[576px]:max-w-[540px]">
            <div class="flex flex-wrap w-full">
                <div class="w-full px-[12px]">
                    <div class="text-center mb-[35px]">
                        <h2 class="mb-[8px] font-montserrat text-[30px] max-[767px]:text-[26px] font-bold text-[#111a24] leading-[1.25]">
                            Before you <span class="text-[#f41a4a]">hit send</span>
                        </h2>
                        <p class="font-montserrat text-[15px] text-[#495461] leading-[26px] max-w-[640px] mx-auto">
                            Six questions that arrive with almost every brief.
                        </p>
                    </div>

                    <div class="flex flex-wrap -mx-[12px]">
                        <?php foreach (array_chunk($contactFaqs, 2) as $columnIndex => $column): ?>
                        <div class="w-full md:w-1/2 px-[12px]">
                            <div class="bix-faq" data-aos="fade-up" data-aos-duration="1500" data-aos-delay="<?= (int)($columnIndex * 200) ?>">
                                <div class="bix-accordion">
                                    <?php foreach ($column as $faqIndex => $faq): ?>
                                    <div class="bix-accordion-item mb-[16px]">
                                        <h4 class="bix-accordion-header <?= $faqIndex === 0 && $columnIndex === 0 ? 'active-arrow' : '' ?> cursor-pointer relative mb-[0] border-[1px] border-solid border-[#eae8ef] rounded-[15px] bg-[#f7f5fb] text-[#111a24] font-montserrat text-[15px] font-medium leading-[24px] py-[16px] pr-[52px] pl-[20px]">
                                            <?= e($faq['question']) ?>
                                        </h4>
                                        <div class="bix-accordion-body <?= $faqIndex === 0 && $columnIndex === 0 ? 'show' : 'hidden' ?> rounded-b-[15px] border-x-[1px] border-b-[1px] border-solid border-[#eae8ef] p-[1.2rem] font-montserrat text-[14px] text-[#495461] leading-[26px]">
                                            <?= e($faq['answer']) ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
$ctaTitle    = 'Prefer to just start building?';
$ctaSubtitle = 'Skip the form and open the consultation modal straight from any page.';
require_once __DIR__ . '/includes/cta.php';

require_once __DIR__ . '/includes/footer.php';
