<?php
declare(strict_types=1);

/**
 * Terms of Service - JMN Portfolio / Morex Web Enterprise
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12 and Strict Typing.
 *
 * Written for this specific site: a portfolio with a consulting intake funnel.
 * It covers browsing, the contact/reservation flow, how an engagement actually
 * becomes a contract, and the licensing position on the template underneath it.
 */

require_once __DIR__ . '/config/config.php';

$pageTitle       = 'Terms of Service | ' . PROFILE_NAME;
$pageDescription = 'Terms governing use of the ' . PROFILE_NAME . ' engineering portfolio, the contact and consultation reservation flow, and how a consulting engagement with ' . PROFILE_COMPANY . ' legally begins.';
$pageKeywords    = 'Terms of Service, consulting terms, engagement terms, Morex Web Enterprise, Jamachi Maurice Mauricennadi';
$currentPage     = 'terms';
$canonicalUrl    = url('terms.php');

$jsonLdBlocks = [
    seo_breadcrumb_graph([
        ['name' => 'Home', 'url' => url('index.php')],
        ['name' => 'Terms of Service', 'url' => $canonicalUrl],
    ]),
];

require_once __DIR__ . '/includes/header.php';

$breadcrumbTitle = 'Terms of Service';
$breadcrumbItems = [
    ['label' => 'Home', 'url' => url('index.php')],
    ['label' => 'Terms of Service', 'url' => ''],
];
require_once __DIR__ . '/includes/breadcrumb.php';

$lastReviewed = '9 October 2026';
?>

    <section class="section-legal py-[60px] max-[991px]:py-[40px] max-[767px]:py-[35px] bg-[#fff]">
        <div class="mx-auto min-[1400px]:max-w-[1140px] min-[1200px]:max-w-[1020px] min-[992px]:max-w-[900px] min-[768px]:max-w-[720px] px-[16px]">

            <div class="mb-[40px] p-[24px] sm:p-[28px] bg-[#111a24] rounded-[24px]" data-aos="fade-up" data-aos-duration="1500">
                <div class="flex flex-wrap items-center gap-[10px] mb-[10px]">
                    <span class="h-[10px] w-[10px] rounded-full bg-[#f41a4a]"></span>
                    <span class="text-[12px] font-montserrat font-bold uppercase tracking-wider text-[#ff4b72]">Plain-language summary</span>
                </div>
                <p class="font-montserrat text-[14px] text-[#cbd5e1] leading-[26px] m-0">
                    Browsing here is free and needs no account. Sending a form starts a conversation, not a contract.
                    A contract only exists once a written proposal, scope and fee are agreed and countersigned.
                    Don&rsquo;t scrape the site, don&rsquo;t attack it, and don&rsquo;t represent the case studies as your own work.
                    Last reviewed <strong class="text-[#fff]"><?= e($lastReviewed) ?></strong>.
                </p>
            </div>

            <div class="prose max-w-none font-montserrat text-[#495461] leading-[28px]">

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">1</span>
                        Who you are dealing with
                    </h2>
                    <p class="mb-[14px] text-[15px]">
                        "we", "us" and "the Studio" mean <strong><?= e(PROFILE_NAME) ?></strong> acting through
                        <strong><?= e(PROFILE_COMPANY) ?></strong>, company registration number <?= e(PROFILE_REG_NO) ?>,
                        Federal Republic of Nigeria. "You" means the person browsing the site or the organisation on whose
                        behalf they are acting.
                    </p>
                    <p class="text-[15px]">
                        Contact for anything contractual: <a href="mailto:<?= e(PROFILE_CORP_EMAIL) ?>" class="text-[#f41a4a] hover:underline"><?= e(PROFILE_CORP_EMAIL) ?></a>.
                        Sending a message to <?= e(PROFILE_EMAIL) ?> is equally valid.
                    </p>
                </div>

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">2</span>
                        Licence to use this site
                    </h2>
                    <p class="mb-[14px] text-[15px]">
                        You may view, read and print pages for personal, non-commercial research and for evaluating
                        whether to engage us. That includes summarising pages with an AI assistant and citing them,
                        provided you attribute the source to this domain.
                    </p>
                    <p class="mb-[14px] text-[15px]">You may not, without written permission:</p>
                    <ul class="list-disc pl-[24px] space-y-2 mb-[14px] text-[15px]">
                        <li>Republish the portfolio copy, case studies, screenshots or the resume PDF as your own.</li>
                        <li>Run bulk scraping, mirroring, or dataset extraction against this host.</li>
                        <li>Use the content to train or evaluate a commercial model without a licence.</li>
                        <li>Remove authorship, copyright or &copy; notices from any asset.</li>
                        <li>Frame the site inside another property, or pass it off as a live client deliverable of yours.</li>
                    </ul>
                    <p class="text-[15px]">
                        Automated agents are welcome to crawl this domain at a respectful rate. Honour
                        <code class="bg-[#f7f5fb] px-[6px] py-[2px] rounded text-[#111a24] font-mono text-[13px]">robots.txt</code>
                        and the crawl-delay it carries; <code class="bg-[#f7f5fb] px-[6px] py-[2px] rounded text-[#111a24] font-mono text-[13px]">llms.txt</code>
                        and <code class="bg-[#f7f5fb] px-[6px] py-[2px] rounded text-[#111a24] font-mono text-[13px]">llms.md</code>
                        describe the site&rsquo;s structure for you.
                    </p>
                </div>

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">3</span>
                        Prohibited conduct
                    </h2>
                    <ul class="list-disc pl-[24px] space-y-2 mb-[14px] text-[15px]">
                        <li>No injection attempts: SQL injection, XSS probing, credential stuffing, brute force, or CSRF token replay.</li>
                        <li>No denial-of-service, load generation, port scanning or reconnaissance against this host.</li>
                        <li>No attempt to reach files under <code class="bg-[#f7f5fb] px-[6px] py-[2px] rounded text-[#111a24] font-mono text-[13px]">config/</code>, <code class="bg-[#f7f5fb] px-[6px] py-[2px] rounded text-[#111a24] font-mono text-[13px]">data/</code>, <code class="bg-[#f7f5fb] px-[6px] py-[2px] rounded text-[#111a24] font-mono text-[13px]">includes/</code> or <code class="bg-[#f7f5fb] px-[6px] py-[2px] rounded text-[#111a24] font-mono text-[13px]">storage/</code>.</li>
                        <li>No submitting of another person&rsquo;s personal data through the forms.</li>
                        <li>No unsolicited sales, recruitment spam or crypto/malware pitches through the contact form.</li>
                    </ul>
                    <p class="text-[15px]">
                        We enforce these with per-IP rate limiting, honeypot fields and server-side throttling. Breach
                        results in immediate blocking and, where warranted, a report to your network provider.
                    </p>
                </div>

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">4</span>
                        When a form becomes a contract
                    </h2>
                    <p class="mb-[14px] text-[15px]">
                        Submitting the contact form, or reserving a slot, is an enquiry. It creates no obligation on
                        either side, no exclusivity and no commitment to a price.
                    </p>
                    <p class="mb-[14px] text-[15px]">An engagement is contracted only when all four exist:</p>
                    <ol class="list-decimal pl-[24px] space-y-2 mb-[14px] text-[15px]">
                        <li>a written proposal naming the scope, deliverables and assumptions;</li>
                        <li>an agreed fee, currency and payment schedule;</li>
                        <li>an agreed start date and milestone dates;</li>
                        <li>acceptance in writing (email is sufficient) by both parties, plus any NDA or MSA we have circulated.</li>
                    </ol>
                    <p class="text-[15px]">
                        Until then, the only thing a submitted form binds you to is these terms and the Privacy Policy.
                    </p>
                </div>

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">5</span>
                        Consultation slots
                    </h2>
                    <ul class="list-disc pl-[24px] space-y-2 mb-[14px] text-[15px]">
                        <li>Slots are 60 minutes and are held in <strong>Africa/Lagos (WAT, UTC+1)</strong>; the form and the confirmation email both show your chosen timezone.</li>
                        <li>A reservation is a hold, not a signed engagement. It is confirmed to your inbox and to <?= e(PROFILE_CORP_EMAIL) ?> the moment you submit it.</li>
                        <li>Reschedule or cancel at no cost with at least 12 hours&rsquo; notice, by replying to the confirmation email or using the Contact page.</li>
                        <li>Arriving more than 15 minutes late, with no notice, counts as a used slot.</li>
                        <li>Discovery calls are complimentary once per organisation. Detailed audits, code reviews and written architecture opinions are chargeable and quoted separately.</li>
                        <li>If we have to move a slot, you are offered two alternatives of equal or better timing, at no charge.</li>
                    </ul>
                </div>

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">6</span>
                        Fees, invoicing and tax
                    </h2>
                    <p class="mb-[14px] text-[15px]">
                        Fees are quoted in USD or NGN and are exclusive of VAT, withholding tax and bank charges, which
                        are for your account where applicable. Invoices are due within 14 calendar days unless the
                        proposal says otherwise.
                    </p>
                    <p class="text-[15px]">
                        Work may be paused on overdue invoices, and the resulting timeline slip is not our liability.
                        Retainers roll forward only within the month they were purchased.
                    </p>
                </div>

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">7</span>
                        Intellectual property
                    </h2>
                    <p class="mb-[14px] text-[15px]">
                        The visual template underlying this site is &copy; 2024 Maraviya Infotech (Biox HTML template),
                        used under its original licence. The copywriting, case studies, architecture write-ups,
                        screenshots of client systems, resume and logos are &copy; <?= e(PROFILE_NAME) ?> and
                        <?= e(PROFILE_COMPANY) ?>.
                    </p>
                    <p class="text-[15px]">
                        On a paid engagement, source code you commission transfers to you on receipt of final payment,
                        excluding: our pre-existing libraries and boilerplate, third-party open-source components (which
                        keep their own licences), and anything we expressly list as retained in the proposal.
                    </p>
                </div>

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">8</span>
                        Disclaimers and liability
                    </h2>
                    <p class="mb-[14px] text-[15px]">
                        The portfolio is provided &ldquo;as is&rdquo;. Availability figures, latency improvements, conversion
                        uplifts and cost savings quoted on this site describe outcomes on specific past systems under
                        specific conditions &mdash; they are a track record, not a promise about your project.
                    </p>
                    <p class="mb-[14px] text-[15px]">
                        We do not warrant uninterrupted access to this site, and we are not responsible for content on
                        external properties reached through LinkedIn, GitHub, WhatsApp or any other outbound link.
                    </p>
                    <p class="text-[15px]">
                        To the maximum extent permitted by Nigerian law, our total aggregate liability arising out of or
                        in connection with this site is limited to NGN 100,000, and we are not liable for indirect,
                        incidental or consequential loss. Nothing in these terms limits liability that cannot be limited
                        by law, including for death or personal injury caused by negligence.
                    </p>
                </div>

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">9</span>
                        Suspension, governing law and disputes
                    </h2>
                    <p class="mb-[14px] text-[15px]">
                        We may suspend access to this site at any time for maintenance, security or abuse reasons,
                        without notice and without liability.
                    </p>
                    <p class="mb-[14px] text-[15px]">
                        These terms are governed by the laws of the Federal Republic of Nigeria. The parties will first
                        attempt to resolve any dispute in good faith within 30 days of written notice. Failing that, the
                        courts of Imo State or Lagos State have exclusive jurisdiction, subject to any mandatory
                        arbitration clause in a signed engagement agreement.
                    </p>
                    <p class="text-[15px]">
                        If any clause here is held unenforceable, the rest continues in full force. Our failure to
                        enforce a clause is not a waiver of it.
                    </p>
                </div>

                <div class="mb-[30px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">10</span>
                        Changes and contact
                    </h2>
                    <p class="text-[15px]">
                        Changes are published on this page with an updated review date. Continued use after a change
                        means you accept it; if you do not, stop using the site. Terms agreed in a signed proposal
                        prevail over anything written here.
                    </p>
                    <div class="p-[24px] rounded-[20px] bg-[#f7f5fb] border border-[#111a24] mt-[15px]">
                        <h4 class="font-bold text-[18px] text-[#111a24] mb-[4px]"><?= e(PROFILE_NAME) ?></h4>
                        <p class="text-[13px] text-[#777] mb-[12px]"><?= e(PROFILE_COMPANY) ?> (BN <?= e(PROFILE_REG_NO) ?>)</p>
                        <p class="text-[14px] text-[#495461] mb-[6px]"><strong>Contracts:</strong> <a href="mailto:<?= e(PROFILE_CORP_EMAIL) ?>?subject=<?= rawurlencode('Contract / terms question') ?>" class="text-[#f41a4a] hover:underline"><?= e(PROFILE_CORP_EMAIL) ?></a></p>
                        <p class="text-[14px] text-[#495461] mb-[6px]"><strong>General:</strong> <a href="mailto:<?= e(PROFILE_EMAIL) ?>" class="text-[#f41a4a] hover:underline"><?= e(PROFILE_EMAIL) ?></a></p>
                        <p class="text-[14px] text-[#495461] m-0"><strong>Registered office:</strong> <?= e(PROFILE_LOCATION) ?></p>
                    </div>
                </div>

            </div>
        </div>
    </section>

<?php
$ctaTitle    = 'Ready to move from terms to scope?';
$ctaSubtitle = 'Reserve a discovery slot and let us put numbers against it.';
require_once __DIR__ . '/includes/cta.php';

require_once __DIR__ . '/includes/footer.php';
