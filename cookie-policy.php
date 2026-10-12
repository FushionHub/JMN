<?php
declare(strict_types=1);

/**
 * Cookie Policy - JMN Portfolio / Morex Web Enterprise
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12, Strict Typing, GDPR & NDPR Compliance.
 *
 * This page is generated against the real inventory: two cookies and three
 * localStorage keys exist on this site as its baseline, plus one third-party
 * cookie (googtrans) that Google sets only after a visitor explicitly picks a
 * language. Every first-party name below is the literal name the browser will
 * show in devtools.
 */

require_once __DIR__ . '/config/config.php';

$pageTitle       = 'Cookie Policy | ' . PROFILE_NAME;
$pageDescription = 'The complete cookie inventory for the ' . PROFILE_NAME . ' portfolio: exactly two first-party cookies, what each one does, how long it lasts, why one of them is non-essential, and how to change your mind.';
$pageKeywords    = 'Cookie Policy, cookie inventory, consent management, NDPR, GDPR, Jamachi Maurice Mauricennadi';
$currentPage     = 'cookie-policy';
$canonicalUrl    = url('cookie-policy.php');

$jsonLdBlocks = [
    seo_breadcrumb_graph([
        ['name' => 'Home', 'url' => url('index.php')],
        ['name' => 'Cookie Policy', 'url' => $canonicalUrl],
    ]),
];

require_once __DIR__ . '/includes/header.php';

$breadcrumbTitle = 'Cookie Policy';
$breadcrumbItems = [
    ['label' => 'Home', 'url' => url('index.php')],
    ['label' => 'Cookie Policy', 'url' => ''],
];
require_once __DIR__ . '/includes/breadcrumb.php';

$lastReviewed = '9 October 2026';
?>

    <section class="section-cookie-policy py-[60px] max-[991px]:py-[40px] max-[767px]:py-[35px] bg-[#fff]">
        <div class="mx-auto min-[1400px]:max-w-[1140px] min-[1200px]:max-w-[1020px] min-[992px]:max-w-[900px] min-[768px]:max-w-[720px] px-[16px]">

            <!-- Live consent status panel -->
            <div class="mb-[45px] p-[28px] sm:p-[36px] bg-[#f7f5fb] border-[1px] border-solid border-[#111a24] rounded-[28px] shadow-sm flex flex-col md:flex-row items-center justify-between gap-6" data-aos="fade-up" data-aos-duration="1500">
                <div class="max-w-[620px]">
                    <div class="inline-flex items-center gap-2 mb-[10px]">
                        <span class="w-[10px] h-[10px] rounded-full bg-[#f41a4a] animate-ping"></span>
                        <span class="text-[12px] font-montserrat font-bold uppercase tracking-wider text-[#f41a4a]">Live consent status</span>
                    </div>
                    <h3 class="font-montserrat text-[22px] sm:text-[26px] font-bold text-[#111a24] leading-[1.3] mb-[8px]">
                        Change your mind at any time
                    </h3>
                    <p class="font-montserrat text-[14px] text-[#495461] leading-[24px] mb-[12px]">
                        This site asks for one switch, and only because one switch describes everything it actually does
                        that needs permission. The second switch on the banner is reserved and currently wired to
                        nothing. Your choice is written to this project&rsquo;s own <code class="bg-[#fff] px-[6px] py-[2px] rounded text-[#111a24] font-mono text-[13px]">cookie_consents</code>
                        table so we can prove consent was given, and mirrored into a cookie so you are not asked again.
                    </p>
                    <div id="cookie-status-display" class="font-montserrat text-[13px] font-semibold text-[#111a24] bg-[#fff] py-[8px] px-[14px] rounded-[12px] border border-[#eae8ef] inline-block">
                        Reading your stored consent&hellip;
                    </div>
                </div>
                <div class="shrink-0">
                    <button type="button" onclick="if(window.openCookiePreferences) window.openCookiePreferences();" class="py-[14px] px-[28px] font-montserrat text-[14px] font-bold bg-[#f41a4a] text-[#fff] border-[1px] border-solid border-[#f41a4a] rounded-[18px] hover:bg-[#111a24] hover:border-[#111a24] transition-all shadow-md inline-flex items-center gap-2 cursor-pointer">
                        <i class="ri-settings-4-line text-[18px]"></i>
                        <span>Adjust Preferences</span>
                    </button>
                </div>
            </div>

            <div class="prose max-w-none font-montserrat text-[#495461] leading-[28px]">

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">1</span>
                        The complete inventory
                    </h2>
                    <p class="mb-[20px] text-[15px]">
                        This is the whole list &mdash; two first-party cookies, three local UI keys, and one
                        third-party cookie Google sets only after you ask for a translation. Nothing is hidden here.
                    </p>

                    <div class="overflow-x-auto rounded-[20px] border border-[#eae8ef] mb-[20px] shadow-sm">
                        <table class="w-full text-left text-[14px] font-montserrat">
                            <thead class="bg-[#111a24] text-[#fff] uppercase text-[12px] tracking-wider">
                                <tr>
                                    <th class="p-[16px]">Name</th>
                                    <th class="p-[16px]">Type</th>
                                    <th class="p-[16px]">Lifetime</th>
                                    <th class="p-[16px]">What it actually does</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#eae8ef] bg-[#fff]">
                                <tr class="hover:bg-[#f7f5fb] transition-colors">
                                    <td class="p-[16px] font-mono text-[13px] text-[#f41a4a] align-top">JMNSESSID</td>
                                    <td class="p-[16px] align-top"><span class="inline-block px-[8px] py-[2px] bg-green-100 text-green-800 rounded-[6px] text-[11px] font-bold uppercase">Strictly necessary</span></td>
                                    <td class="p-[16px] text-[#777] align-top">Session &mdash; deleted when you close the browser</td>
                                    <td class="p-[16px] text-[#495461] align-top">
                                        Carries your CSRF token (without it the contact and reservation forms cannot be
                                        submitted), the per-session and per-IP rate-limit counters that stop spam, and the
                                        server-side copy of your consent choice. HTTP-only and SameSite=Lax, so scripts on
                                        the page cannot read it.
                                    </td>
                                </tr>
                                <tr class="hover:bg-[#f7f5fb] transition-colors">
                                    <td class="p-[16px] font-mono text-[13px] text-[#f41a4a] align-top">jmn_cookie_consent</td>
                                    <td class="p-[16px] align-top"><span class="inline-block px-[8px] py-[2px] bg-green-100 text-green-800 rounded-[6px] text-[11px] font-bold uppercase">Strictly necessary</span></td>
                                    <td class="p-[16px] text-[#777] align-top">365 days</td>
                                    <td class="p-[16px] text-[#495461] align-top">
                                        Stores the answer you gave the consent banner (all / custom / declined, plus the
                                        per-category flags and a timestamp) so you are not interrupted on every visit.
                                        Written by the server after your choice is accepted; readable by this page&rsquo;s
                                        scripts so the switches show your real state.
                                    </td>
                                </tr>
                                <tr class="hover:bg-[#f7f5fb] transition-colors">
                                    <td class="p-[16px] font-mono text-[13px] text-[#f41a4a] align-top">jmn_theme_mode<br>jmn_theme_dir<br>jmn_lang</td>
                                    <td class="p-[16px] align-top"><span class="inline-block px-[8px] py-[2px] bg-slate-100 text-slate-700 rounded-[6px] text-[11px] font-bold uppercase">UI preference</span></td>
                                    <td class="p-[16px] text-[#777] align-top">Until you clear browser storage</td>
                                    <td class="p-[16px] text-[#495461] align-top">
                                        localStorage keys, not cookies. Remember your dark/light mode, LTR/RTL direction and
                                        chosen language from the tools sidebar. These are your own interface settings, not
                                        tracking, so they are written unconditionally and <strong>do not require
                                        consent</strong> &mdash; they never leave your browser and are never sent to the
                                        server.
                                    </td>
                                </tr>
                                <tr class="hover:bg-[#f7f5fb] transition-colors">
                                    <td class="p-[16px] font-mono text-[13px] text-[#f41a4a] align-top">googtrans</td>
                                    <td class="p-[16px] align-top"><span class="inline-block px-[8px] py-[2px] bg-amber-100 text-amber-800 rounded-[6px] text-[11px] font-bold uppercase">Third party</span></td>
                                    <td class="p-[16px] text-[#777] align-top">Session</td>
                                    <td class="p-[16px] text-[#495461] align-top">
                                        Set by <strong>Google</strong>, not by this site, and only after you click a language
                                        flag. It tells Google&rsquo;s widget which language you picked so the page stays
                                        translated as you navigate. Refusing it is a matter of clearing it or picking
                                        &ldquo;Original (English)&rdquo;; this site never reads it.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-[45px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">2</span>
                        The one switch that needs permission
                    </h2>
                    <p class="mb-[20px] text-[15px]">
                        A consent banner that asks for things the site never does is theatre. This site does exactly one
                        thing that needs permission, so it asks for exactly one permission. The second switch on the
                        banner exists to reserve room for features that would need it later &mdash; it currently does
                        nothing, and we would rather tell you that than invent a purpose for it.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-[20px]">
                        <div class="p-[22px] rounded-[20px] bg-[#f7f5fb] border border-[#eae8ef]">
                            <div class="flex items-center justify-between mb-[8px]">
                                <h4 class="font-bold text-[#111a24] text-[16px] m-0 flex items-center gap-2"><i class="ri-line-chart-line text-[#f41a4a]"></i> Analytics</h4>
                                <span class="text-[10px] font-bold font-montserrat uppercase px-[6px] py-[2px] bg-[#fdf2f4] text-[#f41a4a] rounded-[6px]">Optional</span>
                            </div>
                            <p class="text-[13px] text-[#777] leading-[22px] m-0">
                                Lets the site write one row per visited page into the <strong class="text-[#111a24]">visitor_traffic</strong>
                                table: IP, user agent, path, referrer, device class and session id, capped at one row per path
                                per session. That is what powers the traffic figures. It sets no cookie of its own.
                                Refuse it and the table simply stops receiving rows from your browser &mdash; the site keeps
                                working exactly the same.
                            </p>
                        </div>
                        <div class="p-[22px] rounded-[20px] bg-[#f7f5fb] border border-[#eae8ef]">
                            <div class="flex items-center justify-between mb-[8px]">
                                <h4 class="font-bold text-[#111a24] text-[16px] m-0 flex items-center gap-2"><i class="ri-compass-3-line text-[#f41a4a]"></i> Preferences</h4>
                                <span class="text-[10px] font-bold font-montserrat uppercase px-[6px] py-[2px] bg-slate-100 text-slate-600 rounded-[6px]">Reserved</span>
                            </div>
                            <p class="text-[13px] text-[#777] leading-[22px] m-0">
                                Reserved for features that would need permission in future. <strong class="text-[#111a24]">It is
                                wired to nothing today</strong> &mdash; flipping it changes no behaviour and sets no storage.
                                The dark mode, direction and language controls in the tools sidebar are your own interface
                                settings, stored locally in your browser, and they do not run through this switch.
                            </p>
                        </div>
                    </div>

                    <p class="text-[15px]">
                        There is no third switch for advertising, because no advertising technology is installed. A
                        &ldquo;Marketing&rdquo; category would have been a lie, which is why it is not shown to you.
                    </p>
                </div>

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">3</span>
                        What is set on this device right now
                    </h2>
                    <ul class="list-disc pl-[24px] space-y-2 mb-[14px] text-[15px]">
                        <li>No Google Analytics, no Matomo, no Plausible, no Fathom &mdash; traffic is counted by this project&rsquo;s own PHP.</li>
                        <li>No Facebook Pixel, no Google Ads, no LinkedIn Insight Tag, no remarketing tag of any kind.</li>
                        <li>No third-party fonts, no CDN scripts and no embeds on page load &mdash; every asset needed to render the page is served from this host.</li>
                        <li>No fingerprinting, no canvas harvesting, no device-graph matching.</li>
                        <li>No social widgets that fire before you click them.</li>
                        <li><strong>Google Translate loads only when you choose a language.</strong> Until then, nothing third-party is requested. Once you click a flag, the browser fetches the widget from <code class="text-[#f41a4a]">translate.googleapis.com</code> and renders a frame from <code class="text-[#f41a4a]">translate.google.com</code> / <code class="text-[#f41a4a]">*.translate.goog</code>; Google then sets its own <code class="text-[#f41a4a]">googtrans</code> cookie. Choosing &ldquo;Original (English)&rdquo; reverses it.</li>
                    </ul>
                    <p class="text-[15px]">
                        Nothing third-party is requested on page load, and no cookie is set before you have answered
                        the banner. The one optional third-party integration &mdash; page translation &mdash; is off
                        by default, gated behind an explicit click, and named openly above rather than buried.
                    </p>
                </div>

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">4</span>
                        The server-side audit trail
                    </h2>
                    <p class="mb-[14px] text-[15px]">
                        When you press Accept All, Save Preferences or Decline, a CSRF-protected POST goes to
                        <code class="bg-[#f7f5fb] px-[6px] py-[2px] rounded text-[#111a24] font-mono text-[13px]">config/cookie_consent.php</code>,
                        which is rate-limited per IP and rejects any request without a valid session token.
                    </p>
                    <ul class="list-disc pl-[24px] space-y-2 mb-[14px] text-[15px]">
                        <li>A row lands in <code class="text-[#f41a4a]">cookie_consents</code> with the type, the flags, the timestamp, your IP and user agent.</li>
                        <li>The server session gets a copy under <code class="text-[#f41a4a]">jmn_consent</code> &mdash; this is what actually gates the tracker, and it cannot be faked from browser storage.</li>
                        <li>The browser gets the <code class="text-[#f41a4a]">jmn_cookie_consent</code> cookie for the next visit.</li>
                        <li>If you declined, your IP and user agent are replaced with <code class="text-[#f41a4a]">0.0.0.0</code> and <code class="text-[#f41a4a]">Withheld</code> before the audit row is written &mdash; a refusal must not itself become tracking data.</li>
                    </ul>
                    <p class="text-[15px]">
                        Consent rows are kept for 24 months as evidence of compliance, then purged.
                    </p>
                </div>

                <div class="mb-[40px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">5</span>
                        Withdrawing consent
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-[20px]">
                        <div class="p-[20px] rounded-[20px] bg-[#f7f5fb] border border-[#eae8ef]">
                            <h4 class="font-bold text-[#111a24] text-[16px] mb-[6px] flex items-center gap-2"><i class="ri-refresh-line text-[#f41a4a]"></i> Revoke in one click</h4>
                            <p class="text-[13px] text-[#777] leading-[22px]">Use the <strong>Preferences</strong> link in the footer, or the button above, and flip any switch. The change takes effect on the next page load.</p>
                        </div>
                        <div class="p-[20px] rounded-[20px] bg-[#f7f5fb] border border-[#eae8ef]">
                            <h4 class="font-bold text-[#111a24] text-[16px] mb-[6px] flex items-center gap-2"><i class="ri-delete-bin-line text-[#f41a4a]"></i> Clear it entirely</h4>
                            <p class="text-[13px] text-[#777] leading-[22px]">Deleting <code>jmn_cookie_consent</code> and the <code>jmn_theme_mode</code>, <code>jmn_theme_dir</code> and <code>jmn_lang</code> keys from browser storage returns you to the first-visit state, and the banner reappears.</p>
                        </div>
                        <div class="p-[20px] rounded-[20px] bg-[#f7f5fb] border border-[#eae8ef]">
                            <h4 class="font-bold text-[#111a24] text-[16px] mb-[6px] flex items-center gap-2"><i class="ri-database-2-line text-[#f41a4a]"></i> Purge the server copy</h4>
                            <p class="text-[13px] text-[#777] leading-[22px]">Email <?= e(PROFILE_CORP_EMAIL) ?> and ask for erasure of your consent row and any <code>visitor_traffic</code> rows &mdash; done in one pass, confirmed in writing.</p>
                        </div>
                        <div class="p-[20px] rounded-[20px] bg-[#f7f5fb] border border-[#eae8ef]">
                            <h4 class="font-bold text-[#111a24] text-[16px] mb-[6px] flex items-center gap-2"><i class="ri-global-line text-[#f41a4a]"></i> Browser-level control</h4>
                            <p class="text-[13px] text-[#777] leading-[22px]">Blocking all cookies also blocks <code>JMNSESSID</code>, which stops the contact and reservation forms from submitting. Allow first-party cookies for this domain only.</p>
                        </div>
                    </div>
                </div>

                <div class="mb-[30px]" data-aos="fade-up" data-aos-duration="1500">
                    <h2 class="text-[24px] sm:text-[28px] font-bold text-[#111a24] mb-[16px] pb-[10px] border-b border-[#eae8ef] flex items-center gap-3">
                        <span class="h-[36px] w-[36px] shrink-0 rounded-[10px] bg-[#fdf2f4] text-[#f41a4a] flex items-center justify-center text-[18px] font-bold">6</span>
                        Questions
                    </h2>
                    <p class="text-[15px]">
                        Reviewed <strong><?= e($lastReviewed) ?></strong>. Anything about cookies, storage or consent goes to
                        the same inbox as everything else &mdash; see the
                        <a href="<?= url('privacy-policy.php') ?>" class="text-[#f41a4a] hover:underline">Privacy Policy</a>
                        for the full data map and your rights under the NDPR and GDPR.
                    </p>
                    <div class="p-[24px] rounded-[20px] bg-[#111a24] text-[#fff] mt-[15px]">
                        <h4 class="font-bold text-[18px] text-[#fff] mb-[4px]"><?= e(PROFILE_NAME) ?></h4>
                        <p class="text-[13px] text-[#94a3b8] mb-[12px]">Data Controller &middot; <?= e(PROFILE_COMPANY) ?> (BN <?= e(PROFILE_REG_NO) ?>)</p>
                        <p class="text-[14px] text-[#cbd5e1] mb-[6px]"><strong>Consent &amp; privacy:</strong> <a href="mailto:<?= e(PROFILE_CORP_EMAIL) ?>?subject=<?= rawurlencode('Cookie / consent question') ?>" class="text-[#ff4b72] hover:underline"><?= e(PROFILE_CORP_EMAIL) ?></a></p>
                        <p class="text-[14px] text-[#cbd5e1] mb-0"><strong>Direct:</strong> <a href="mailto:<?= e(PROFILE_EMAIL) ?>" class="text-[#ff4b72] hover:underline"><?= e(PROFILE_EMAIL) ?></a></p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var display = document.getElementById('cookie-status-display');
            if (!display) return;

            try {
                var raw = localStorage.getItem('jmn_cookie_consent');
                if (!raw) {
                    var m = document.cookie.match(/(^| )jmn_cookie_consent=([^;]+)/);
                    raw = m ? decodeURIComponent(m[2]) : null;
                }
                if (raw) {
                    var data = JSON.parse(raw);
                    var type = data.consent_type || data.type || 'custom';
                    var html;
                    if (type === 'all') {
                        html = 'Recorded: <span class="text-green-600 font-bold">Accept All</span> &mdash; Analytics + Preferences on';
                    } else if (type === 'declined') {
                        html = 'Recorded: <span class="text-red-500 font-bold">Declined</span> &mdash; strictly necessary only';
                    } else {
                        var a = Number(data.analytics_enabled !== undefined ? data.analytics_enabled : data.analytics) === 1;
                        var p = Number(data.preferences_enabled !== undefined ? data.preferences_enabled : data.preferences) === 1;
                        html = 'Recorded: <span class="text-blue-600 font-bold">Custom</span> &mdash; Analytics ' +
                            (a ? 'on' : 'off') + ', Preferences ' + (p ? 'on' : 'off');
                    }
                    display.innerHTML = html;
                } else {
                    display.innerHTML = 'No choice recorded yet &mdash; <span class="text-[#777]">treated as strictly necessary only</span>';
                }
            } catch (e) {
                display.innerHTML = 'Strictly necessary cookies active';
            }
        });
    </script>

<?php
$ctaTitle    = 'Have a project in mind?';
$ctaSubtitle = 'Send a brief or reserve a discovery slot - replies within 24 hours.';
require_once __DIR__ . '/includes/cta.php';

require_once __DIR__ . '/includes/footer.php';
