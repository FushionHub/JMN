<?php
declare(strict_types=1);

/**
 * Cookie Policy & Real-Time Consent Modal Box
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12, GDPR, NDPR & Zero-Trust Privacy.
 *
 * Three categories only, because three categories are all this project uses:
 *   Necessary   - JMNSESSID session cookie (CSRF token, rate limiting, consent record)
 *                 + the jmn_cookie_consent cookie that remembers this very choice
 *   Analytics   - opt-in server-side visitor logs in the `visitor_traffic` table
 *   Preferences - opt-in persistence of theme / layout choices in the browser
 *
 * There is no advertising, retargeting or third-party tag on this site, so a
 * "marketing" category would be a lie and is deliberately not shown.
 *
 * All controls are icon inputs (RemixIcon) rendered without depending on the
 * Tailwind runtime, so they render correctly even if the runtime is blocked.
 */

require_once dirname(__DIR__) . '/config/config.php';

$csrfToken = generate_csrf_token();
?>

<!-- ============================================================== -->
<!-- Icon-input styles (scoped to the consent UI, Tailwind-free)     -->
<!-- ============================================================== -->
<style>
.jmn-sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.jmn-icon-slot{position:relative;display:inline-flex;flex:0 0 auto}
.jmn-icon-input{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.jmn-icon-label{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:13px;background:#f7f5fb;border:1px solid #eae8ef;color:#b9b4c7;font-size:23px;line-height:1;cursor:pointer;-webkit-user-select:none;user-select:none;transition:background-color .2s ease,border-color .2s ease,color .2s ease,box-shadow .2s ease}
.jmn-icon-label:hover{border-color:#111a24;color:#495461}
.jmn-icon-label .jmn-ico-on{display:none}
.jmn-icon-label .jmn-ico-off{display:block}
.jmn-icon-input:checked+.jmn-icon-label{background:#fdf2f4;border-color:rgba(244,26,74,.4);color:#f41a4a;box-shadow:0 0 0 3px rgba(244,26,74,.10)}
.jmn-icon-input:checked+.jmn-icon-label .jmn-ico-on{display:block}
.jmn-icon-input:checked+.jmn-icon-label .jmn-ico-off{display:none}
.jmn-icon-input:focus-visible+.jmn-icon-label{outline:2px solid #f41a4a;outline-offset:2px}
.jmn-icon-input:disabled+.jmn-icon-label{cursor:not-allowed;background:#e9f8ef;border-color:#c4ead3;color:#16a34a;box-shadow:none}
.jmn-icon-input:disabled+.jmn-icon-label:hover{border-color:#c4ead3;color:#16a34a}
.jmn-consent-row{padding:14px;border-radius:18px;border:1px solid #eae8ef;background:#fff;display:flex;align-items:center;justify-content:space-between;gap:15px;transition:border-color .2s ease}
.jmn-consent-row:hover{border-color:#111a24}
.jmn-consent-row--locked{background:#f7f5fb}
.jmn-consent-row--locked:hover{border-color:#eae8ef}
.jmn-consent-row__text{min-width:0}
.jmn-consent-row__head{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:3px}
.jmn-consent-row__head h5{font-family:Montserrat,Montserrat,sans-serif;font-size:14px;font-weight:700;color:#111a24;line-height:1.3;margin:0}
.jmn-consent-row__desc{font-family:Montserrat,sans-serif;font-size:12px;color:#777;line-height:18px;margin:0}
.jmn-badge{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;padding:2px 7px;border-radius:6px;font-family:Montserrat,sans-serif}
.jmn-badge--req{background:#e9f8ef;color:#16a34a}
.jmn-badge--opt{background:#fdf2f4;color:#f41a4a}
.jmn-cookie-list{margin:0 0 18px;padding:0;list-style:none;border:1px solid #eae8ef;border-radius:14px;overflow:hidden}
.jmn-cookie-list li{display:flex;align-items:flex-start;gap:10px;padding:10px 14px;font-family:Montserrat,sans-serif;font-size:12px;color:#495461;line-height:18px;border-bottom:1px solid #f2f0f6}
.jmn-cookie-list li:last-child{border-bottom:0}
.jmn-cookie-list b{display:block;color:#111a24;font-weight:700;word-break:break-all}
.jmn-cookie-list .jmn-cookie-type{margin-left:auto;flex:0 0 auto;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;padding:2px 7px;border-radius:6px;white-space:nowrap}
.jmn-cookie-type--req{background:#e9f8ef;color:#16a34a}
.jmn-cookie-type--opt{background:#fdf2f4;color:#f41a4a}
@media (max-width:420px){.jmn-consent-row{flex-direction:row}}
</style>

<!-- ============================================================== -->
<!-- 1. Floating Cookie Notification Banner                         -->
<!-- ============================================================== -->
<div id="jmn-cookie-banner" class="fixed bottom-[20px] left-[20px] right-[20px] sm:left-[30px] sm:right-auto sm:max-w-[460px] z-[9999] bg-[#fff] border-[1px] border-solid border-[#111a24] rounded-[24px] p-[24px] shadow-2xl transition-all duration-[0.4s] ease-in-out hidden">
    <div class="flex items-start gap-3 mb-[12px]">
        <div class="h-[40px] w-[40px] rounded-[12px] bg-[#fdf2f4] flex items-center justify-center shrink-0 border border-[#f41a4a]/20">
            <i class="ri-shield-check-line text-[#f41a4a] text-[22px]"></i>
        </div>
        <div>
            <h4 class="font-montserrat text-[16px] font-bold text-[#111a24] leading-[1.2]">Privacy &amp; Cookie Policy</h4>
            <p class="font-montserrat text-[12px] text-[#777] leading-[18px] mt-[2px]">GDPR &amp; NDPR compliant &middot; No advertising cookies</p>
        </div>
    </div>
    <p class="font-montserrat text-[13px] text-[#495461] leading-[22px] mb-[18px]">
        This site sets one strictly necessary session cookie so forms cannot be forged. With your permission it also records anonymous visitor counts. Your theme, direction and language choices are stored locally in your browser only and need no permission. Review our <a href="<?= url('cookie-policy.php') ?>" class="text-[#f41a4a] font-semibold underline hover:text-[#111a24]">Cookie Policy</a> and <a href="<?= url('privacy-policy.php') ?>" class="text-[#f41a4a] font-semibold underline hover:text-[#111a24]">Privacy Policy</a>.
    </p>
    <div class="flex flex-wrap items-center gap-2">
        <button type="button" id="jmn-cookie-accept-all" class="grow py-[9px] px-[16px] font-montserrat text-[13px] font-bold bg-[#111a24] text-[#fff] rounded-[14px] hover:bg-[#f41a4a] transition-all text-center">
            Accept All
        </button>
        <button type="button" id="jmn-cookie-customize-btn" class="py-[9px] px-[14px] font-montserrat text-[13px] font-semibold bg-[#f7f5fb] text-[#111a24] border border-[#eae8ef] rounded-[14px] hover:border-[#111a24] transition-all">
            Customize
        </button>
        <button type="button" id="jmn-cookie-decline-btn" class="py-[9px] px-[12px] font-montserrat text-[13px] font-semibold text-[#777] hover:text-[#ff0000] transition-colors">
            Decline
        </button>
    </div>
</div>

<!-- ============================================================== -->
<!-- 2. Detailed Cookie Customization Modal (icon inputs)           -->
<!-- ============================================================== -->
<div id="jmn-cookie-custom-overlay" class="fixed inset-0 bg-[#00000080] backdrop-blur-[3px] z-[10000] hidden flex items-center justify-center p-[15px]">
    <div class="bg-[#fff] border-[1px] border-solid border-[#111a24] rounded-[28px] max-w-[540px] w-full p-[26px] shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center pb-[16px] border-b border-[#eae8ef] mb-[18px]">
            <div class="flex items-center gap-2">
                <i class="ri-settings-4-line text-[#f41a4a] text-[22px]"></i>
                <h3 class="font-montserrat text-[18px] font-bold text-[#111a24]">Cookie Consent Preferences</h3>
            </div>
            <button type="button" id="jmn-cookie-custom-close" class="text-[#777] hover:text-[#ff0000] text-[24px] leading-none" aria-label="Close cookie preferences">&times;</button>
        </div>

        <p class="font-montserrat text-[13px] text-[#495461] leading-[22px] mb-[16px]">
            Two switches. One does something (analytics); the other is reserved and currently wired to nothing, because
            this site has nothing else that needs permission. Your decision is written to a server-side audit table only
            when you opt into analytics.
        </p>

        <!-- Exact inventory of every cookie this site can set -->
        <ul class="jmn-cookie-list">
            <li>
                <div>
                    <b>JMNSESSID</b>
                    Session cookie &middot; httponly &middot; SameSite=Lax &middot; expires on browser close. Stores your CSRF token, contact-form rate limits and this consent choice.
                </div>
                <span class="jmn-cookie-type jmn-cookie-type--req">Required</span>
            </li>
            <li>
                <div>
                    <b>jmn_cookie_consent</b>
                    First-party cookie &middot; 1 year &middot; SameSite=Lax. Remembers the choices below so you are not asked on every visit.
                </div>
                <span class="jmn-cookie-type jmn-cookie-type--req">Required</span>
            </li>
        </ul>

        <form id="jmn-cookie-preferences-form" autocomplete="off">
            <input type="hidden" id="jmn-cookie-csrf" value="<?= e($csrfToken) ?>">
            <div class="space-y-4 mb-[24px]">

                <!-- Strictly necessary (locked on) -->
                <div class="jmn-consent-row jmn-consent-row--locked">
                    <div class="jmn-consent-row__text">
                        <div class="jmn-consent-row__head">
                            <h5>Strictly Necessary</h5>
                            <span class="jmn-badge jmn-badge--req">Always On</span>
                        </div>
                        <p class="jmn-consent-row__desc">Cryptographic CSRF session protection, form abuse rate limiting and secure routing. Without these the contact form cannot be submitted safely.</p>
                    </div>
                    <span class="jmn-icon-slot">
                        <input type="checkbox" id="cookie_pref_necessary" class="jmn-icon-input" checked disabled aria-label="Strictly necessary cookies (always on)">
                        <label class="jmn-icon-label" for="cookie_pref_necessary" title="Always on">
                            <i class="ri-lock-2-fill" aria-hidden="true"></i>
                            <span class="jmn-sr-only">Always on</span>
                        </label>
                    </span>
                </div>

                <!-- Analytics (optional) -->
                <div class="jmn-consent-row">
                    <div class="jmn-consent-row__text">
                        <div class="jmn-consent-row__head">
                            <h5>Analytics &amp; Performance</h5>
                            <span class="jmn-badge jmn-badge--opt">Optional</span>
                        </div>
                        <p class="jmn-consent-row__desc">Anonymous page-view counts, referrer, device class and session id written to this project&rsquo;s own <code>visitor_traffic</code> table. No cookie is set and no data leaves the server.</p>
                    </div>
                    <span class="jmn-icon-slot">
                        <input type="checkbox" id="cookie_pref_analytics" class="jmn-icon-input" name="analytics" value="1" checked>
                        <label class="jmn-icon-label" for="cookie_pref_analytics" title="Allow analytics logging">
                            <i class="ri-checkbox-circle-fill jmn-ico-on" aria-hidden="true"></i>
                            <i class="ri-checkbox-blank-circle-line jmn-ico-off" aria-hidden="true"></i>
                            <span class="jmn-sr-only">Allow analytics logging</span>
                        </label>
                    </span>
                </div>

                <!-- Preferences (optional, reserved) -->
                <div class="jmn-consent-row">
                    <div class="jmn-consent-row__text">
                        <div class="jmn-consent-row__head">
                            <h5>Preferences</h5>
                            <span class="jmn-badge jmn-badge--opt">Reserved</span>
                        </div>
                        <p class="jmn-consent-row__desc">Reserved for features that would need permission later. It is wired to nothing today &mdash; flipping it changes no behaviour. Theme, direction and language are stored locally in your browser and need no permission.</p>
                    </div>
                    <span class="jmn-icon-slot">
                        <input type="checkbox" id="cookie_pref_preferences" class="jmn-icon-input" name="preferences" value="1" checked>
                        <label class="jmn-icon-label" for="cookie_pref_preferences" title="Allow preference storage">
                            <i class="ri-checkbox-circle-fill jmn-ico-on" aria-hidden="true"></i>
                            <i class="ri-checkbox-blank-circle-line jmn-ico-off" aria-hidden="true"></i>
                            <span class="jmn-sr-only">Allow preference storage</span>
                        </label>
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-[14px] border-t border-[#eae8ef]">
                <a href="<?= url('cookie-policy.php') ?>" class="font-montserrat text-[12px] font-semibold text-[#777] hover:text-[#f41a4a] transition-colors underline">
                    Read the full Cookie Policy
                </a>
                <button type="button" id="jmn-cookie-save-custom" class="py-[10px] px-[22px] font-montserrat text-[13px] font-bold bg-[#111a24] text-[#fff] rounded-[14px] hover:bg-[#f41a4a] transition-all shadow-md">
                    Save Preferences
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- 3. Client-Side Storage & Synchronization Script                -->
<!-- ============================================================== -->
<script>
(function () {
    var banner = document.getElementById('jmn-cookie-banner');
    var modal = document.getElementById('jmn-cookie-custom-overlay');
    var consentEndpoint = '<?= url("config/cookie_consent.php") ?>';
    var csrfField = document.getElementById('jmn-cookie-csrf');

    function readStoredConsent() {
        try {
            var local = localStorage.getItem('jmn_cookie_consent');
            if (local) { return local; }
        } catch (e) {}
        var match = document.cookie.match(/(^| )jmn_cookie_consent=([^;]+)/);
        return match ? match[2] : null;
    }

    function showBanner() { if (banner) { banner.classList.remove('hidden'); } }
    function hideBanner() { if (banner) { banner.classList.add('hidden'); } }
    function hideModal()   { if (modal)  { modal.classList.add('hidden'); } }

    if (!readStoredConsent()) { showBanner(); }

    function recordConsent(type, analytics, preferences) {
        var payload = {
            consent_type: type,
            analytics_enabled: analytics ? 1 : 0,
            preferences_enabled: preferences ? 1 : 0,
            csrf_token: csrfField ? csrfField.value : ''
        };

        // Mirror locally for instant UI state on the next page load
        try { localStorage.setItem('jmn_cookie_consent', JSON.stringify(payload)); } catch (e) {}
        hideBanner();
        hideModal();

        // Register server-side (writes the audit row + session + cookie)
        fetch(consentEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': payload.csrf_token
            },
            body: JSON.stringify(payload),
            credentials: 'same-origin'
        })
        .then(function (res) {
            if (res.status === 419) {
                // Token rotated since page load - reload so a fresh one is issued
                window.location.reload();
                return null;
            }
            return res.json();
        })
        .then(function (data) { if (data) { console.log('Cookie consent registered:', data); } })
        .catch(function (err) { console.warn('Consent logging notice:', err); });
    }

    var acceptAllBtn = document.getElementById('jmn-cookie-accept-all');
    if (acceptAllBtn) {
        acceptAllBtn.addEventListener('click', function () { recordConsent('all', 1, 1); });
    }

    var declineBtn = document.getElementById('jmn-cookie-decline-btn');
    if (declineBtn) {
        declineBtn.addEventListener('click', function () { recordConsent('declined', 0, 0); });
    }

    var customizeBtn = document.getElementById('jmn-cookie-customize-btn');
    if (customizeBtn && modal) {
        customizeBtn.addEventListener('click', function () { modal.classList.remove('hidden'); });
    }

    var closeCustomBtn = document.getElementById('jmn-cookie-custom-close');
    if (closeCustomBtn && modal) {
        closeCustomBtn.addEventListener('click', function () { modal.classList.add('hidden'); });
    }
    if (modal) {
        modal.addEventListener('click', function (ev) {
            if (ev.target === modal) { modal.classList.add('hidden'); }
        });
        document.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape' && !modal.classList.contains('hidden')) { modal.classList.add('hidden'); }
        });
    }

    var saveCustomBtn = document.getElementById('jmn-cookie-save-custom');
    if (saveCustomBtn) {
        saveCustomBtn.addEventListener('click', function () {
            var analyticsEl = document.getElementById('cookie_pref_analytics');
            var prefsEl = document.getElementById('cookie_pref_preferences');
            var analytics = analyticsEl && analyticsEl.checked ? 1 : 0;
            var preferences = prefsEl && prefsEl.checked ? 1 : 0;
            recordConsent('custom', analytics, preferences);
        });
    }

    // Manual reopen trigger (footer "Cookie Preferences" link / policy page)
    window.openCookiePreferences = function () {
        if (modal) { modal.classList.remove('hidden'); } else { showBanner(); }
    };
})();
</script>
