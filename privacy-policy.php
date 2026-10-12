<?php
declare(strict_types=1);

/**
 * Privacy Policy - JMN Portfolio / Morex Web Enterprise
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12, Strict Typing, Nigeria Data Protection Regulation (NDPR)
 * and, for EU/UK visitors, the General Data Protection Regulation.
 *
 * This document is written against the actual implementation: every table,
 * column, file path and retention period named below exists in this codebase.
 */

require_once __DIR__ . '/config/config.php';

$pageTitle       = 'Privacy Policy | ' . PROFILE_NAME;
$pageDescription = 'How the ' . PROFILE_NAME . ' portfolio actually handles your data: the four MySQL tables it writes to, what is stored in each, how long it is kept, what is never collected, and how to exercise your NDPR/GDPR rights.';
$pageKeywords    = 'Privacy Policy, NDPR, GDPR, data controller, Morex Web Enterprise, Jamachi Maurice Mauricennadi';
$currentPage     = 'privacy-policy';
$canonicalUrl    = url('privacy-policy.php');

$jsonLdBlocks = [
    seo_breadcrumb_graph([
        ['name' => 'Home', 'url' => url('index.php')],
        ['name' => 'Privacy Policy', 'url' => $canonicalUrl],
    ]),
];

require_once __DIR__ . '/includes/header.php';

$breadcrumbTitle = 'Privacy Policy';
$breadcrumbItems = [
    ['label' => 'Home', 'url' => url('index.php')],
    ['label' => 'Privacy Policy', 'url' => ''],
];
require_once __DIR__ . '/includes/breadcrumb.php';

$lastReviewed = '9 October 2026';
?>

    <section class="section-legal py-[60px] max-[991px]:py-[40px] max-[767px]:py-[35px] bg-[#fff]">
        <div class="mx-auto min-[1400px]:max-w-[1140px] min-[1200px]:max-w-[1020px] min-[992px]:max-w-[900px] min-[768px]:max-w-[720px] px-[16px]">

            <div class="mb-[40px] p-[24px] sm:p-[28px] bg-[#111a24] rounded-[24px]" data-aos="fade-up" data-aos-duration="1500">
                <div class="flex flex-wrap items-center gap-[10px] mb-[10px]">
                    <span class="h-[10px] w-[10px] rounded-full bg-[#f41a4a]"></span>
                    <span class="text-[12px] font-montserrat font-bold uppercase tracking-wider text-[#ff4b72]">Controller statement</span>
                </div>
                <p class="font-montserrat text-[14px] text-[#cbd5e1] leading-[26px] m-0">
                    <strong class="text-[#fff]"><?= e(PROFILE_NAME) ?></strong>, trading through
                    <strong class="text-[#fff]"><?= e(PROFILE_COMPANY) ?></strong> (BN <?= e(PROFILE_REG_NO) ?>),
                    is the data controller for everything on this site. There is no parent company, no holding group
                    and no joint controller. Last reviewed <strong class="text-[#fff]"><?= e($lastReviewed) ?></strong>.
                </p>
            </div>

            <div class="prose max-w-none font-montserrat text-[#495461] leading-[28px]">

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">1</span>
                        What this site is, and what it is not
                    </h2>
                    <p class="mb-[14px] text-[15px]">
                        This is a single-host portfolio and consulting intake site. It has no user accounts, no login,
                        no payment checkout, no forum and no user-generated content. You cannot be "logged in" here
                        because there is no login.
                    </p>
                    <p class="text-[15px]">
                        It is also not a data business. Nothing you type into a form on this site is sold, licensed,
                        brokered, enriched with third-party profiles, or shared with advertisers. There is no Google
                        Analytics, no Meta Pixel, no Hotjar session replay, no advertising SDK and no tag manager on
                        this deployment. The one third-party service on this site is Google Translate, which loads
                        only when you explicitly choose a language &mdash; see
                        <a href="<?= url('cookie-policy.php') ?>#inventory" class="text-[#f41a4a] hover:underline">Cookie Policy</a>.
                    </p>
                </div>

                <div class="mb-[45px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">2</span>
                        Exactly what is stored, and where
                    </h2>
                    <p class="mb-[20px] text-[15px]">
                        The whole inventory fits on one table. Nothing else is collected that is not listed here.
                    </p>

                    <div class="overflow-x-auto rounded-[20px] border border-[#eae8ef] mb-[20px] shadow-sm">
                        <table class="w-full text-left text-[14px] font-montserrat">
                            <thead class="bg-[#111a24] text-[#fff] uppercase text-[12px] tracking-wider">
                                <tr>
                                    <th class="p-[16px]">Data</th>
                                    <th class="p-[16px]">Where it lives</th>
                                    <th class="p-[16px]">Why</th>
                                    <th class="p-[16px]">Kept for</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#eae8ef] bg-[#fff]">
                                <tr class="hover:bg-[#f7f5fb] transition-colors">
                                    <td class="p-[16px] font-bold text-[#111a24] align-top">Name, email, phone, company, brief, budget, requested slot</td>
                                    <td class="p-[16px] font-mono text-[13px] text-[#f41a4a] align-top">MySQL &rarr; contact_inquiries</td>
                                    <td class="p-[16px] text-[#495461] align-top">To answer you. Nothing else.</td>
                                    <td class="p-[16px] text-[#777] align-top">Until you ask us to delete it, or 24 months of no reply from you</td>
                                </tr>
                                <tr class="hover:bg-[#f7f5fb] transition-colors">
                                    <td class="p-[16px] font-bold text-[#111a24] align-top">Same, as a plain-text audit line</td>
                                    <td class="p-[16px] font-mono text-[13px] text-[#f41a4a] align-top">storage/logs/inquiries.log</td>
                                    <td class="p-[16px] text-[#495461] align-top">Debugging if MySQL write fails</td>
                                    <td class="p-[16px] text-[#777] align-top">90 days</td>
                                </tr>
                                <tr class="hover:bg-[#f7f5fb] transition-colors">
                                    <td class="p-[16px] font-bold text-[#111a24] align-top">Consent choice, timestamp, anonymised IP, user agent</td>
                                    <td class="p-[16px] font-mono text-[13px] text-[#f41a4a] align-top">MySQL &rarr; cookie_consents</td>
                                    <td class="p-[16px] text-[#495461] align-top">Proving consent was obtained</td>
                                    <td class="p-[16px] text-[#777] align-top">24 months</td>
                                </tr>
                                <tr class="hover:bg-[#f7f5fb] transition-colors">
                                    <td class="p-[16px] font-bold text-[#111a24] align-top">IP, user agent, page path, referrer, device class, session id</td>
                                    <td class="p-[16px] font-mono text-[13px] text-[#f41a4a] align-top">MySQL &rarr; visitor_traffic</td>
                                    <td class="p-[16px] text-[#495461] align-top">Traffic counts &mdash; <strong>only if you opted into Analytics</strong></td>
                                    <td class="p-[16px] text-[#777] align-top">90 days</td>
                                </tr>
                                <tr class="hover:bg-[#f7f5fb] transition-colors">
                                    <td class="p-[16px] font-bold text-[#111a24] align-top">PHP error traces</td>
                                    <td class="p-[16px] font-mono text-[13px] text-[#f41a4a] align-top">storage/logs/php-error.log</td>
                                    <td class="p-[16px] text-[#495461] align-top">Keeping the site error-free</td>
                                    <td class="p-[16px] text-[#777] align-top">30 days rolling</td>
                                </tr>
                                <tr class="hover:bg-[#f7f5fb] transition-colors">
                                    <td class="p-[16px] font-bold text-[#111a24] align-top">Session id, CSRF token, rate-limit counters, consent record</td>
                                    <td class="p-[16px] font-mono text-[13px] text-[#f41a4a] align-top">Server session (JMNSESSID cookie)</td>
                                    <td class="p-[16px] text-[#495461] align-top">Stopping form forgery and abuse</td>
                                    <td class="p-[16px] text-[#777] align-top">Session lifetime (ends when you close the browser)</td>
                                </tr>
                                <tr class="hover:bg-[#f7f5fb] transition-colors">
                                    <td class="p-[16px] font-bold text-[#111a24] align-top">Theme, direction and language choice</td>
                                    <td class="p-[16px] font-mono text-[13px] text-[#f41a4a] align-top">Your browser&rsquo;s localStorage</td>
                                    <td class="p-[16px] text-[#495461] align-top">Opening the site how you left it</td>
                                    <td class="p-[16px] text-[#777] align-top">Until you clear it. These are your own UI settings and never leave your browser, so they need no consent.</td>
                                </tr>
                                <tr class="hover:bg-[#f7f5fb] transition-colors">
                                    <td class="p-[16px] font-bold text-[#111a24] align-top">Page text sent for translation</td>
                                    <td class="p-[16px] font-mono text-[13px] text-[#f41a4a] align-top">Google Translate (third party)</td>
                                    <td class="p-[16px] text-[#495461] align-top">Rendering the site in your chosen language</td>
                                    <td class="p-[16px] text-[#777] align-top">Only after you click a language flag. Visible page text is sent to Google; form contents and any data you have typed are not.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-[15px]">
                        Submissions travel to <code class="bg-[#f7f5fb] px-[6px] py-[2px] rounded text-[#111a24] font-mono text-[13px]">contact_inquiries</code>,
                        which also stores your source IP. The IP is kept so we can block repeat abuse; it is never used to
                        build a profile of you, and it is the first field purged on any erasure request.
                    </p>
                </div>

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">3</span>
                        What is deliberately never collected
                    </h2>
                    <ul class="list-disc pl-[24px] space-y-2 mb-[14px] text-[15px]">
                        <li>No payment card or bank details &mdash; there is no checkout on this site.</li>
                        <li>No government identifiers, biometrics, health data or political opinions.</li>
                        <li>No precise geolocation &mdash; the IP is used only for rate limiting and is never resolved to coordinates.</li>
                        <li>No behavioural ad profile, no cross-site tracking cookie, no fingerprinting script.</li>
                        <li>No data from minors: this site is not directed at anyone under 16 and knowingly collects nothing from them.</li>
                        <li>Nothing is written to third-party analytics, CRM or marketing platforms, because none are installed.</li>
                    </ul>
                </div>

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">4</span>
                        Legal basis for each processing activity
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-[20px]">
                        <div class="p-[20px] rounded-[20px] bg-[#f7f5fb] border border-[#eae8ef]">
                            <h4 class="font-bold text-[#111a24] text-[16px] mb-[6px] flex items-center gap-2"><i class="ri-chat-1-line text-[#f41a4a]"></i> You asked us to</h4>
                            <p class="text-[13px] text-[#777] leading-[22px]">Contact and reservation submissions are processed on the basis of steps taken at your request prior to a contract (GDPR Art. 6(1)(b)) and consent (Art. 6(1)(a) / NDPR).</p>
                        </div>
                        <div class="p-[20px] rounded-[20px] bg-[#f7f5fb] border border-[#eae8ef]">
                            <h4 class="font-bold text-[#111a24] text-[16px] mb-[6px] flex items-center gap-2"><i class="ri-shield-check-line text-[#f41a4a]"></i> Legitimate interest</h4>
                            <p class="text-[13px] text-[#777] leading-[22px]">Security headers, CSRF tokens, rate limiting and error logging run to keep the service secure (GDPR Art. 6(1)(f)). They are strictly necessary and cannot be switched off without breaking the forms.</p>
                        </div>
                        <div class="p-[20px] rounded-[20px] bg-[#f7f5fb] border border-[#eae8ef]">
                            <h4 class="font-bold text-[#111a24] text-[16px] mb-[6px] flex items-center gap-2"><i class="ri-line-chart-line text-[#f41a4a]"></i> Consent</h4>
                            <p class="text-[13px] text-[#777] leading-[22px]">Visitor logging and theme persistence only run after an explicit opt-in. Refuse or withdraw it and the corresponding writes simply stop &mdash; the rest of the site is unaffected.</p>
                        </div>
                        <div class="p-[20px] rounded-[20px] bg-[#f7f5fb] border border-[#eae8ef]">
                            <h4 class="font-bold text-[#111a24] text-[16px] mb-[6px] flex items-center gap-2"><i class="ri-scales-3-line text-[#f41a4a]"></i> Legal obligation</h4>
                            <p class="text-[13px] text-[#777] leading-[22px]">Corporate records tying an engagement to its correspondence are retained as required by the Companies and Allied Matters Act (CAMA) 2020 and Nigerian tax law.</p>
                        </div>
                    </div>
                </div>

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">5</span>
                        Your rights, and how to actually use them
                    </h2>
                    <p class="mb-[14px] text-[15px]">
                        Under the NDPR and, where it applies to you, the GDPR, you can request access to, correction of,
                        or deletion of the rows listed in section 2, object to processing, and ask for a portable copy.
                    </p>
                    <div class="p-[24px] rounded-[20px] bg-[#111a24] text-[#fff] mt-[15px]">
                        <h4 class="font-bold text-[18px] text-[#fff] mb-[10px]">How to file a request</h4>
                        <p class="text-[14px] text-[#cbd5e1] mb-[8px]">
                            Email <a href="mailto:<?= e(PROFILE_CORP_EMAIL) ?>?subject=<?= rawurlencode('Data subject access request') ?>" class="text-[#ff4b72] hover:underline"><?= e(PROFILE_CORP_EMAIL) ?></a>
                            with the subject line <strong class="text-[#fff]">"Data subject access request"</strong>.
                        </p>
                        <p class="text-[14px] text-[#cbd5e1] mb-[8px]">
                            Include the email address you used in the form (or the approximate date of your visit) so the row can be located.
                        </p>
                        <p class="text-[14px] text-[#cbd5e1] mb-0">
                            We acknowledge within <strong class="text-[#fff]">72 hours</strong> and answer within
                            <strong class="text-[#fff]">30 days</strong>. Erasure is executed against
                            <code class="text-[#ff4b72]">contact_inquiries</code>, <code class="text-[#ff4b72]">storage/logs/inquiries.log</code>
                            and <code class="text-[#ff4b72]">cookie_consents</code> in a single pass, and we confirm in writing when it is done.
                            A complaint can also be lodged with the Nigeria Data Protection Commission.
                        </p>
                    </div>
                </div>

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">6</span>
                        Where the data physically sits
                    </h2>
                    <p class="mb-[14px] text-[15px]">
                        The database and log files live on the hosting infrastructure serving this domain. Emails are
                        handed to the local mail transport agent on the same host and delivered from there. No replica is
                        written to another region and no cloud SaaS is used as the system of record.
                    </p>
                    <p class="text-[15px]">
                        If you contact us from outside Nigeria, your submission is therefore transferred into Nigeria on
                        the basis of your explicit request.
                    </p>
                    <h3 class="text-[18px] font-bold text-[#111a24] mt-[20px] mb-[10px]">Sub-processors</h3>
                    <ul class="list-disc pl-[24px] space-y-2 mb-[14px] text-[15px]">
                        <li>
                            <strong>Google LLC</strong> &mdash; Google Translate, used only when you click a language
                            flag. Purpose: on-demand UI translation of visible page text. Data transferred: the text
                            of the page you are viewing. Location: United States. Basis: your explicit request, made
                            by the click. Google&rsquo;s own terms and privacy notice apply to that transfer.
                        </li>
                    </ul>
                    <p class="text-[15px]">
                        No other sub-processor touches data from this site. Analytics, inquiry storage, reservations
                        and email all run on this host and are not delegated to a third party.
                    </p>
                </div>

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">7</span>
                        Security measures actually in force
                    </h2>
                    <ul class="list-disc pl-[24px] space-y-2 mb-[14px] text-[15px]">
                        <li>Prepared PDO statements everywhere &mdash; no user value is ever concatenated into SQL.</li>
                        <li>CSRF tokens on every state-changing form, verified with <code class="bg-[#f7f5fb] px-[6px] py-[2px] rounded text-[#111a24] font-mono text-[13px]">hash_equals()</code> and rotated after each successful submit.</li>
                        <li>HTTP-only, SameSite sessions with one-time id regeneration to defeat fixation.</li>
                        <li>Content-Security-Policy, HSTS, X-Frame-Options, X-Content-Type-Options and Referrer-Policy on every response.</li>
                        <li>Two rate limiters (session and per-IP file bucket) in front of the contact endpoint.</li>
                        <li>Mail headers rebuilt from sanitised values, so a crafted name cannot inject a Bcc line.</li>
                        <li>Directory listing off; <code class="bg-[#f7f5fb] px-[6px] py-[2px] rounded text-[#111a24] font-mono text-[13px]">config/</code>, <code class="bg-[#f7f5fb] px-[6px] py-[2px] rounded text-[#111a24] font-mono text-[13px]">data/</code>, <code class="bg-[#f7f5fb] px-[6px] py-[2px] rounded text-[#111a24] font-mono text-[13px]">includes/</code> and <code class="bg-[#f7f5fb] px-[6px] py-[2px] rounded text-[#111a24] font-mono text-[13px]">storage/</code> are blocked at the web-server level.</li>
                        <li>Display of PHP errors is off in production; failures go to a server-side log only.</li>
                    </ul>
                    <p class="text-[15px]">
                        No system is perfect. If you find a vulnerability, email <?= e(PROFILE_CORP_EMAIL) ?> with the
                        reproduction steps and allow a reasonable window before public disclosure.
                    </p>
                </div>

                <div class="mb-[30px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">8</span>
                        Changes, and who to ask
                    </h2>
                    <p class="text-[15px]">
                        When the data map changes, this page changes with it &mdash; the review date at the top moves and the
                        table in section 2 is updated in the same commit. Material changes are announced on the site for
                        14 days.
                    </p>
                    <div class="p-[24px] rounded-[20px] bg-[#f7f5fb] border border-[#111a24] mt-[15px]">
                        <h4 class="font-bold text-[18px] text-[#111a24] mb-[4px]"><?= e(PROFILE_NAME) ?></h4>
                        <p class="text-[13px] text-[#777] mb-[12px]">Data Controller &middot; <?= e(PROFILE_COMPANY) ?> (BN <?= e(PROFILE_REG_NO) ?>)</p>
                        <p class="text-[14px] text-[#495461] mb-[6px]"><strong>Privacy:</strong> <a href="mailto:<?= e(PROFILE_CORP_EMAIL) ?>?subject=<?= rawurlencode('Privacy question') ?>" class="text-[#f41a4a] hover:underline"><?= e(PROFILE_CORP_EMAIL) ?></a></p>
                        <p class="text-[14px] text-[#495461] mb-[6px]"><strong>General:</strong> <a href="mailto:<?= e(PROFILE_EMAIL) ?>" class="text-[#f41a4a] hover:underline"><?= e(PROFILE_EMAIL) ?></a></p>
                        <p class="text-[14px] text-[#495461] m-0"><strong>Post:</strong> <?= e(PROFILE_LOCATION) ?></p>
                    </div>
                </div>

            </div>
        </div>
    </section>

<?php
$ctaTitle    = 'Questions about your data?';
$ctaSubtitle = 'Ask directly - a person reads it, not a ticketing system.';
require_once __DIR__ . '/includes/cta.php';

require_once __DIR__ . '/includes/footer.php';
