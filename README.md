# JMN — Portfolio & Consulting Platform

Engineering portfolio and consulting intake site for **Jamachi Maurice Mauricennadi**, Founder/CEO/CTO of MOREX WEB ENTERPRISE GROUP OF COMPANIES LTD (Nigerian BN 8721238).

Plain PHP 8.+, no framework, no router, no build step. `declare(strict_types=1)` on every file.

---

## 1. Requirements

| Requirement | Notes |
|---|---|
| PHP **8.2+** (tested on 8.5.9) | Extensions: `pdo_mysql`, `curl`, `mbstring`, `json`, `fileinfo` |
| **MySQL 8.x** (tested on 8.4.3) or MariaDB 10.4+ | InnoDB, `utf8mb4` |
| Apache 2.4 with `mod_rewrite` **or** nginx | `.htaccess` is written for Apache; nginx equivalent shown below |
| A working `mail()` transport on the host | See [Known limitations](#10-known-limitations) |

No Composer, no Node, no npm. All CSS is pre-compiled into `assets/css/style.css` and `assets/css/dark.css`; `assets/scss/` is the source of truth but there is **no sass binary in this environment** — new styles must be hand-compiled into both the SCSS partial and `style.css`.

---

## 2. Setup procedure

### 2.1 Clone and place the files

Copy the project into your web root. For Laragon this is `C:\laragon\www\JMN`; for a Linux host it is typically `/var/www/jmn`.

```bash
cd /var/www
git clone <repo> jmn
cd jmn
```

### 2.2 Create the database

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS jmn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

There are **no SQL migration files to run by hand.** `config/db.php` bootstraps the full schema lazily on first request: it creates the tables if missing and adds any missing columns/defaults via `ensureColumn()` / `ensureColumnDefault()`. The tables it provisions are:

| Table | Purpose |
|---|---|
| `visitor_traffic` | Page-view log (IP, UA, path, referrer, device class, session id) — written **only** with Analytics consent |
| `cookie_consents` | Server-side consent audit trail |
| `contact_inquiries` | Contact + reservation submissions |
| `projects` | Case-study rows; falls back to `data/projects.php` when the table is empty |
| `blogs` | Article rows; falls back to `data/articles.php` when the table is empty |
| `services` | Service catalogue; falls back to `data/services.php` when the table is empty |
| `testimonials` | Client endorsements; falls back to `data/testimonials.php` when the table is empty |
| `skills` | Skills matrix; falls back to `data/skills.php` when the table is empty |
| `indexnow_logs` | IndexNow submission history |
| `admin_users` | Back-office accounts (single seeded owner) |

The `data/*.php` files are hardcoded fallbacks so the site renders fully even on an empty database. Seeding is optional — see [§6](#6-seeding-content).

#### Option B: import the bundled SQL dump

A comprehensive, re-importable dump lives at **`database/jmn.sql`** — the full schema for all 10 tables plus the real seed content (5 blog posts, 6 projects, 8 services, 3 testimonials, 7 skills, and the seeded `owner` admin account). Import it to get a fully populated database in one step, bypassing the lazy-bootstrap seeding:

```bash
mysql -u root -p jmn < database/jmn.sql
```

The file `DROP`s then `CREATE`s every table, so it safely rebuilds an existing `jmn` database from scratch. Runtime tables (`visitor_traffic`, `cookie_consents`, `contact_inquiries`, `indexnow_logs`) ship schema-only — that data is user-generated and intentionally excluded. The admin account it seeds is `admin` / `ChangeMe!Admin2026` (bcrypt); change it via **Settings → Change password** or the `ADMIN_PASSWORD` env before going live.

To regenerate the dump after changing content through the admin:

```bash
php bin/generate-sql.php   # rewrites database/jmn.sql from the live DB
```

### 2.3 Configure credentials

The app reads everything from **environment variables**, with safe defaults for local development. Set them in your vhost, `.htaccess`, or a systemd unit — never commit secrets.

| Variable | Default | Purpose |
|---|---|---|
| `APP_ENV` | `production` | Set to `development` to enable `APP_DEBUG` |
| `SITE_DOMAIN` | `jmn.morexweb.com` | Canonical host; drives all canonical/OG/structured-data URLs |
| `BASE_URL` | origin of the current request | Explicit origin override; always wins when set. Auto-derived as protocol + host only (never includes a path — needed so `url('admin/...')` resolves correctly from back-office pages). A subdirectory install must set this explicitly |
| `DB_HOST` | `127.0.0.1` | |
| `DB_PORT` | `3306` | |
| `DB_DATABASE` | `jmn` | |
| `DB_USERNAME` | `root` | |
| `DB_PASSWORD` | *(empty)* | |
| `INDEXNOW_KEY` | `6f0d1b8f0d1e4b9c8a2f7d3e5b6c1a94` | Must match the key-file name at the web root |
| `GOOGLE_SITE_VERIFICATION` | *(empty)* | Emitted as a `<meta>` tag when non-empty |
| `MSN_SITE_VERIFICATION` | *(empty)* | Same |
| `YANDEX_SITE_VERIFICATION` | *(empty)* | Same |
| `ADMIN_USERNAME` | `admin` | Back-office login name (seeded on first run) |
| `ADMIN_PASSWORD` | `ChangeMe!Admin2026` | **Change this before going live.** Seeded as a bcrypt hash on first run |
| `ADMIN_NAME` | `Site Owner` | Display name for the seeded admin |
| `ADMIN_EMAIL` | `contact@morexweb.com` | Contact email for the seeded admin (used as Reply-To on admin-sent mail) |

Example Apache vhost with env vars:

```apache
SetEnv APP_ENV production
SetEnv SITE_DOMAIN jmn.morexweb.com
SetEnv DB_HOST 127.0.0.1
SetEnv DB_PORT 3306
SetEnv DB_DATABASE jmn
SetEnv DB_USERNAME jmn_app
SetEnv DB_PASSWORD "change-me"
SetEnv ADMIN_USERNAME admin
SetEnv ADMIN_PASSWORD "a-strong-unique-password"
```

Example nginx + php-fpm (`fastcgi_param`):

```nginx
fastcgi_param APP_ENV production;
fastcgi_param SITE_DOMAIN jmn.morexweb.com;
fastcgi_param DB_HOST 127.0.0.1;
fastcgi_param DB_DATABASE jmn;
fastcgi_param DB_USERNAME jmn_app;
fastcgi_param DB_PASSWORD "change-me";
fastcgi_param ADMIN_PASSWORD "a-strong-unique-password";
```

`config/config.php` also has a **Host-header allowlist**: `localhost`, `127.0.0.1`, `::1`, the canonical host and `www.` + canonical. Any other `Host` is discarded and the canonical origin is used, so an attacker cannot poison generated links by spoofing the Host header. Pages served from a permitted local host emit local canonicals; production emits `https://jmn.morexweb.com/...`.

### 2.4 Web-server routing

`.htaccess` is already in place. It:

- forces HTTPS, sets `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, a strict CSP and HSTS via `config/security.php::send_security_headers()`
- **blocks `/config/`, `/data/`, `/includes/`, `/storage/`, `/bin/`, `/tools/`** and dotfiles
- serves `.svg` with `Content-Type: image/svg+xml`
- has **no clean-URL rewrite rules** — deliberately. Every resource keeps its `.php` extension in canonical URLs, sitemap and `llm.txt`, so one resource = one URL and no redirect chain can dilute crawl budget or split link equity.

nginx equivalent of the directory blocks:

```nginx
location ~ ^/(config|data|includes|storage|bin|tools)/ { deny all; return 404; }
location ~ /\. { deny all; }
```

### 2.5 Permissions

```bash
chmod -R 755 .
chmod -R 775 storage
chown -R www-data:www-data storage
```

`storage/logs/` must be writable (PHP error log + `inquiries.log` audit trail) and `storage/ratelimit/` must be writable (per-IP/per-session rate-limit counters). `config/db.php` will create both on demand if they are missing.

### 2.6 First request

Hit the site once. `config/db.php::ensureSchemaExists()` provisions every table and column. Confirm `storage/logs/php-error.log` is empty afterwards.

---

## 3. Architecture

```
index.php, about.php, projects.php, services.php, resume.php,
contact.php, blog.php, blog-detail.php, single-project.php,
privacy-policy.php, cookie-policy.php, terms.php
        │  each page: require config → optional data/ → includes/header.php
        │             page body → includes/footer.php
        ▼
includes/   header.php, footer.php, cookie_modal.php   (shared chrome)
config/     config.php   constants, env, host allowlist
            db.php       PDO singleton + lazy schema bootstrap
            cache.php    file-based data cache (TTL + admin invalidation)
            security.php CSP, security headers, CSRF, sanitisation
            cookie_consent.php   consent endpoint (CSRF + rate-limited)
            tracker.php  page-view logger (Analytics-consent gated)
            mailer.php   dual-dispatch mail (owner + sender acknowledgement)
            email_templates.php
            seo.php      canonical/OG/Twitter/JSON-LD helpers
admin/      login.php, logout.php, index.php (dashboard)
            inquiries.php, inquiry.php (list + detail + email reply)
            blog.php, blog-edit.php (TinyMCE 8), blog-delete.php
            projects.php, project-edit.php, project-delete.php
            services.php, service-edit.php, service-delete.php
            testimonials.php, testimonial-edit.php, testimonial-delete.php
            skills.php, skill-edit.php, skill-delete.php
            settings.php (password change)
            includes/    auth.php, helpers.php, layout-header.php, layout-footer.php
            assets/      admin.css, admin.js
data/       hardcoded fallback content (projects, articles, services, …)
database/   jmn.sql           comprehensive re-importable schema + seed dump
assets/     css/ scss/ js/ img/ docs/
storage/    logs/ ratelimit/ cache/     (writable, never web-served)
bin/        build-seo.php, generate-sql.php, optimize-images.php   CLI-only tooling
tools/      e2e_smoke.php, e2e_mailer.php, e2e_admin.php, lang_dd_test.js   CLI-only test suites
```

There is no front controller. Each `.php` page is a real entry point.

### Caching & performance

Three layers, all cross-browser (standard HTTP + HTML attributes, no browser-specific APIs):

1. **Data cache** (`config/cache.php`, `storage/cache/`) — the five public content getters (`get_all_projects/articles/services/testimonials`, `get_skills_matrix`) serve from a file cache (5-min TTL) instead of running seed-`COUNT` + `SELECT` + JSON-decode per request. Measured: homepage TTFB 539ms → 237ms warm. Every admin save/delete calls `cache_clear()`, so edits go live immediately; TTL is only a backstop. Content rows only — never sessions, CSRF, consent or tracker state, and never cached HTML (per-session CSRF tokens would break forms if shared).
2. **Static assets** (`.htaccess`) — `Cache-Control: public, max-age=31536000, immutable` for CSS/JS/images/fonts/PDF (all browsers serve repeat visits from cache without revalidation); `no-store` for HTML/PHP; `mod_deflate` gzip for text assets; `mod_expires` 1-year for versioned assets.
3. **Loading** — `font-display: swap` on all 18 Montserrat faces (no invisible-text wait); `loading="lazy" decoding="async"` on 19 below-fold/loop images; `fetchpriority="high"` on the hero LCP image (heroes and logos stay eager).
4. **Images** (`bin/optimize-images.php`) — pre-generated WebP variants (q80) alongside originals: 30 files, 29.4MB → 2.9MB (-90%), originals untouched. Templates use `picture_img()` (`config/config.php`), which emits `<picture><source webp><img original></picture>` when a variant exists and falls back to plain `<img>` otherwise — every browser renders identically. Re-run the script after adding/replacing source images; outputs are skipped when up-to-date. Naming is append-style (`hero.jpg` → `hero.jpg.webp`) so same-basename sources never collide. Favicon/ and uploads/ are excluded (direct-fetch and dynamic content respectively).

---

## 4. Admin back office

A single-owner admin lives at **`/admin/`**. It mirrors the public homepage design language — self-hosted Montserrat, the `#111a24` / `#f41a4a` / `#f7f5fb` palette, 20px rounded metric-cards and dark→accent hover buttons — and is `noindex,nofollow` at both the header and robots.txt level. Fully responsive: sidebar collapses off-canvas with a burger toggle below 780px, charts stack single-column, tables scroll internally.

### Features

| Area | What it does |
|---|---|
| **Dashboard** (`admin/index.php`) | **Analytics overview** with 4 self-hosted Chart.js 4 visualisations (30-day inquiries + page-views trend, status doughnut, type doughnut, content bar), inquiry counts, content totals, recent activity |
| **Inquiries** (`admin/inquiries.php`) | Filterable, searchable, paginated list of contact + reservation submissions |
| **Inquiry detail** (`admin/inquiry.php`) | Full submission view, status changes, **reply by email** (branded HTML template, recorded on the row) |
| **Blog** (`admin/blog.php`) | List all posts with status filters, edit/delete quick actions |
| **Blog editor** (`admin/blog-edit.php`) | Create/edit posts with **TinyMCE 8** (registered API key), auto-slug with lock, cover-image upload (finfo-validated), draft/publish toggle |
| **Projects** (`admin/projects.php`, `project-edit.php`) | Full CRUD for portfolio projects: all fields including JSON-backed metrics/stack/highlights/gallery edited as one-per-line textareas, featured toggle, image upload, live URL |
| **Services** (`admin/services.php`, `service-edit.php`) | CRUD for the service catalogue: tagline, colour, icon upload, summary/description, deliverables list, sort order |
| **Testimonials** (`admin/testimonials.php`, `testimonial-edit.php`) | CRUD for client endorsements: name/role/company, photo upload, quote, sort order |
| **Skills** (`admin/skills.php`, `skill-edit.php`) | CRUD for the skills matrix: category, percentage, summary, badges list, sort order |
| **Settings** (`admin/settings.php`) | Change the admin password (bcrypt, current-password verified) |

Every content type follows the same resilient pattern as the blog: the public pages read from MySQL when available and fall back to the hardcoded `data/*.php` arrays if the table is empty or the DB is down, so the site never breaks. Each type is seeded from its fallback array on first access.

**TinyMCE 8** (registered API key) is loaded on the blog editor only — it is the one surface whose output is rendered as raw HTML (`blog-detail.php` echoes `$article['content']` unescaped). Project/service/testimonial/skill text is escaped on output (`e()`), so those editors use plain textareas to avoid displaying literal markup.

**Chart.js 4** is self-hosted at `admin/assets/chart.umd.min.js` (no CDN, no CSP change) and loads only on pages that set `$adminCharts = true`. Dashboard charts use the site palette (`#f41a4a` accent, `#111a24` ink) with Montserrat labels.

### Authentication

- Session-based, using the hardened `JMNSESSID` bootstrap from `config/security.php`.
- Passwords are stored as **bcrypt** (`password_hash` / `password_verify`).
- Session ID is regenerated on login (fixation mitigation) and on logout.
- CSRF tokens are enforced on every state-changing POST.
- Failed logins are throttled per IP (10 per 15 minutes).
- All admin POSTs are rate-limited (session-scoped, 2–3 s minimum interval).

### Initial credentials

On the first request that touches the schema, `config/db.php::seedAdminUser()` creates one `owner` account:

- **Username:** `ADMIN_USERNAME` env (default `admin`)
- **Password:** `ADMIN_PASSWORD` env (default `ChangeMe!Admin2026`)

**Set `ADMIN_PASSWORD` before the first request in production**, or change the password immediately after the first login via **Settings → Change password**. The setup event is logged to `storage/logs/admin-setup.log`.

### Reply-by-email flow

1. Open an inquiry → the reply composer is pre-filled with a suggested template.
2. Edit the message and click **Send reply**.
3. The server wraps it in the branded HTML template (`jmn_build_reply_email()` in `config/email_templates.php`), sends it via `mail()` to the inquiry's address, then records `status=replied`, `replied_at`, `reply_message` and `replied_by` on the row.
4. A disk audit line is appended to `storage/logs/inquiries.log`.
5. If `mail()` fails (no MTA on the host), the reply is still saved on the row and the admin sees a warning pointing at `storage/logs/php-error.log` — nothing is silently lost.

### TinyMCE 8

The blog editor loads **TinyMCE 8 (registered API key `v0xlnluysr3rgxaap7agv1l90tlfnu2zwu8rve04fubvv7lw`)** from the official CDN (`cdn.tiny.cloud`). The CSP allowances live in `config/security.php` (`script-src`/`style-src`/`font-src`/`img-src`/`media-src`/`connect-src` += `https://cdn.tiny.cloud`; `frame-src` += `about:blank`). The public site never contacts this origin. If the CDN is unreachable the textarea degrades to a plain HTML editor — saving still works.

### Image uploads

Cover images can be uploaded directly from the editor. Uploads are validated with `finfo` (real MIME sniffing, never the client-supplied type), capped at 5 MB, renamed to a random safe filename, and stored in `assets/img/blog/uploads/` (created on demand, `0755`, files `0644`). Allowed types: JPG, PNG, WEBP, GIF, SVG.

### Responsive design (public + admin)

Both the public site and the back office are verified at 375px (mobile), 768px (tablet), 1440px and 1920px (desktop) with **zero horizontal page overflow**:
- **Public:** Tailwind responsive prefixes + `viewport` meta; hero, metric cards, sections and cookie banner stack cleanly; full-viewport-width sections with centered `max-w` containers on desktop.
- **Admin (full-screen):** `.admin-content` has no max-width cap — tables, charts and forms use 100% of the available viewport width on desktop (verified 1631px content at 1920px viewport). Sidebar collapses to off-canvas with a burger toggle below 780px; chart grid collapses to single-column below 1080px; grid children carry `min-width: 0` and chart wrappers `overflow: hidden` + canvas `max-width: 100%` so Chart.js pixel widths can never force page overflow; tables scroll internally via `.admin-table-wrap`.

---

## 4. Security model

- **CSRF**: per-session token, rotated on every successful state-changing POST. Any POST without a valid token is rejected with `419`.
- **CSP**: default-src `'self'`; script/style `'unsafe-inline' 'unsafe-eval'` (required by the Tailwind runtime) plus `https://translate.googleapis.com`; `https://cdn.tiny.cloud` (admin blog editor only); `frame-src`/`child-src` allow `https://translate.google.com`, `https://*.translate.goog` and `about:blank` (TinyMCE iframes); `object-src 'none'`; `base-uri 'self'`; `form-action 'self'`.
- **Input**: every echoed value goes through `e()` (htmlspecialchars). No raw `echo` of request data anywhere.
- **Rate limiting**: contact/reservation and consent endpoints are rate-limited per IP (and per session for forms). Counters live in `storage/ratelimit/`.
- **Honeypot**: an invisible field on both forms; a filled honeypot is silently accepted, writes no row, and sends no mail.
- **Host-header hardening**: unlisted hosts fall back to the canonical origin (see §2.3).
- **Directory blocks**: application internals are unreachable over HTTP (see §2.4).

### Consent model

The banner asks **one** permission that does something (**Analytics**) plus a **Preferences** switch that is **reserved and wired to nothing**. There is no Marketing category because no advertising technology is installed.

| Storage | Type | Consent |
|---|---|---|
| `JMNSESSID` | session cookie | Always on (CSRF, rate limits, form security) |
| `jmn_cookie_consent` | cookie, 365 days | Always on (stores your choice) |
| `jmn_theme_mode`, `jmn_theme_dir`, `jmn_lang` | localStorage | **None required** — these are the visitor's own UI settings (dark mode, RTL/LTR, language). They never leave the browser and are never sent to the server. |
| `googtrans` | third-party cookie, session | Set by **Google**, only after the visitor clicks a language flag |
| `visitor_traffic` row | server DB | **Requires Analytics consent** |

The server-side gate is `config/tracker.php`, which reads the consent from the **session** (`jmn_consent`) and not from browser storage, so it cannot be forged by editing localStorage. Refusing Analytics means no IP, user agent or session id is stored at all.

Legal pages: `privacy-policy.php` (full data map + sub-processors), `cookie-policy.php` (live inventory + live consent-status panel), `terms.php`.

---

## 5. SEO, AEO and AI indexability

### Files at the web root

| File | What it is |
|---|---|
| `robots.txt` | Allow-all; explicit allowances for GPTBot, ClaudeBot, PerplexityBot, Google-Extended, CCBot, Applebot-Extended, meta-externalagent, cohere-ai, Amazonbot; `SemrushBot`/`AhrefsBot`/`DotBot` disallowed; `Crawl-delay: 1`; `Sitemap:` + `Host:` lines |
| `sitemap.xml` | 21 URLs (10 static pages, 6 projects, 5 articles), generated |
| `llm.txt` | LLMsTxt-format index — 21 page links with one-line descriptions |
| `llms.md` | Long-form machine-readable spec: identity, URLs, booking semantics, consent facts |
| `6f0d1b8f0d1e4b9c8a2f7d3e5b6c1a94.txt` | IndexNow key file (name **must** equal the key) |

### Regenerating

```bash
# regenerate sitemap.xml from the data source and cross-check every file
php bin/build-seo.php

# verify only, write nothing — exits non-zero on any mismatch
php bin/build-seo.php --check
```

The `--check` mode verifies: every `jmn.morexweb.com` URL mentioned in `robots.txt` / `llm.txt` / `llms.md` exists in the sitemap; every sitemap URL resolves to a real PHP file; the IndexNow key file exists; the `robots.txt` `Sitemap:` directive points at the right origin; and the page `$canonicalUrl` of every sitemap URL matches.

### IndexNow

```bash
# submit one URL
php indexnow.php https://jmn.morexweb.com/services.php

# submit several
php indexnow.php https://jmn.morexweb.com/index.php https://jmn.morexweb.com/blog.php

# dry-run: build and print the payload, send nothing
php indexnow.php --dry-run https://jmn.morexweb.com/index.php
```

Submissions are recorded in `indexnow_logs`. The key file must be publicly reachable at `https://<domain>/<INDEXNOW_KEY>.txt`.

> **Not yet run live.** No live ping has been sent — do it deliberately once DNS for `jmn.morexweb.com` is confirmed pointing at the host.

### Structured data

`config/seo.php` emits JSON-LD per page: `Person`/`WebSite` on the home page, `BreadcrumbList` on interior pages, `FAQPage` on `services.php` and `contact.php`. Open Graph and Twitter card tags are emitted on every page.

---

## 6. Seeding content

The `projects` and `blogs` tables are populated lazily. With an empty table the site renders from `data/projects.php` and `data/articles.php`. To manage content in the database instead, insert rows matching the column shape `config/db.php` provisions; the templates read from the DB first and only fall back to `data/` when the query returns nothing.

Current slugs:

| Projects | Articles |
|---|---|
| `jotah-ecom`, `fusionhub-ai`, `doccure`, `sokarlab`, `oma-audiobooks`, `quantum-pay-escrow` | `double-entry-ledger-architecture`, `rag-pipelines-enterprise`, `cutting-cloud-costs-serverless`, `vendor-code-auditing-guide`, `microservices-cqrs-ledger` |

---

## 7. Site language

The tools sidebar has a **Site Language** block: a normal **Google-Translate-style dropdown** (`#jmn-lang-dd`) whose trigger shows a **flag identifying the currently selected language** (English → `gb.svg` by default) plus a chevron. Clicking the trigger opens a listbox (`#jmn-lang-menu`) of 12 options (English, French, Spanish, German, Portuguese, Italian, Russian, Arabic, Chinese, Japanese, Hindi, Swahili). Flags are self-hosted SVGs in `assets/img/flags/` — **no third-party flag CDN**.

Google Translate is loaded **strictly on demand** by `assets/js/main.js`. Nothing third-party is requested on page load. The first pick of a non-English language fetches `translate.googleapis.com/translate_a/element.js` (Google's widget API, rendered invisibly via `InlineLayout.SIMPLE`), which performs the translation. The choice is persisted in `localStorage.jmn_lang` **and** in Google's `googtrans` cookie (domain-scoped), then the page **reloads** so the widget re-applies the language on boot. Picking `English` clears the cookie, resets `jmn_lang` to `en`, and reloads back to the original text.

Three invariants that must not be regressed:

1. **`#jmn-translate-mount` must never be `display:none`.** Google's `element.js` refuses to render its combo into a hidden container, and translation silently no-ops. The mount uses `class="sr-only"` plus explicit `clip`/`clip-path` rules in `style.css` — visually hidden, still laid out.
2. **The CSP must allow** `https://translate.googleapis.com` (script/style/img) and `https://translate.google.com` + `https://*.translate.goog` (frame/child).
3. **The dropdown trigger/option styles must stay mirrored** across `assets/css/style.css`, `assets/css/dark.css` (dark theme) and `assets/scss/components/_tools-sidebar.scss` — there is no sass binary in this environment, so edits are hand-applied to all three.

---

## 8. Testing

Both suites are CLI-only, drive the real HTTP server, and exit non-zero on failure. Start the server first:

```bash
php -S 127.0.0.1:8099 -t /path/to/jmn
```

```bash
# consent + tracker gating (17 assertions)
php tools/e2e_smoke.php

# mailer + forms (35 assertions)
php tools/e2e_mailer.php

# admin back office (100 assertions, incl. 7 analytics/chart checks)
php tools/e2e_admin.php
```

Clear the rate-limit counters between runs — `check_rate_limit('contact_form', 5)` enforces a **5-second minimum interval** per IP, so the mailer suite needs a short `sleep` between POSTs and will fail if you re-run it back-to-back:

```bash
rm -f storage/ratelimit/*.json
```

The mailer suite covers: method + CSRF guards, token extraction, honeypot (silent accept, no DB row), 422 validation with an error list, a valid general inquiry (including a **hostile `return_to`** that must be ignored), a valid reservation with `reserved_at`/`reserved_zone` stored, a past-date reservation rejected with 422, rotated-token enforcement, and the classic form-post path returning a `302` that stays on-site.

### Admin suite

`tools/e2e_admin.php` drives the full back-office lifecycle over HTTP: login page render, unauthenticated redirects (all pages), bad/good login, every authenticated page (dashboard, inquiries, blog, projects, services, testimonials, skills, settings), dashboard **analytics/chart assertions** (Analytics overview section, self-hosted Chart.js script, all 4 canvases, embedded JSON payload), inquiry status change, **reply-by-email** (verifies the reply is recorded on the row and the status flips to `replied`), and a **full CRUD round-trip for each content type** — blog (draft hidden from public, published visible on list + detail, unknown slug 404s, delete incl. draft cleanup), projects (create → appears on the public projects page and detail → delete → gone), services (create → public services page → delete), testimonials (create → public about page → delete), and skills (create → public about page → delete). It uses a real cookie jar and waits out the 2-second admin rate limits between POSTs. The admin password defaults to `ChangeMe!Admin2026` (override with the `ADMIN_PASSWORD` env var).

### Browser suite (Site Language dropdown)

`tools/lang_dd_test.js` is a CDP (Chrome DevTools Protocol) smoke test that drives a real headless **Edge** browser against the running server. It exercises the full dropdown lifecycle: trigger/menu state, flag+label painting, `jmn_lang` persistence, `googtrans` cookie write/clear, keyboard Enter selection, and reload-based language switching. It also captures a screenshot of the open dropdown (opening the tools sidebar first so the shot shows the real in-page state).

```bash
# start the server, then in another shell:
node tools/lang_dd_test.js
# optional: pass a URL + output path
node tools/lang_dd_test.js http://127.0.0.1:8099/index.php %TEMP%/jmn-lang.png
```

**Known local limitation:** Google's translation service cannot translate a `http://127.0.0.1` page — the widget's combo renders with 0 options and no translation occurs. This is environmental (Google will not fetch a loopback origin), not a bug in the integration; it works on the live domain. The test detects this and auto-skips the text-assertions (logging a NOTE) while still validating all of our own dropdown state, persistence and cookie logic. All 15 assertions pass locally.

`/tools/` and `/bin/` are blocked in `.htaccess`, so none of the three suites is reachable over HTTP.

---

## 9. Deployment checklist

1. `APP_ENV=production` and `APP_DEBUG` off.
2. Real `DB_*` credentials; the app user needs `SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX` on the `jmn` schema (the lazy migrator runs `CREATE TABLE`/`ALTER TABLE`).
3. `SITE_DOMAIN` set to the live host. Confirm the canonical on `/` reads `https://<domain>/index.php`.
4. **Set `ADMIN_PASSWORD` to a strong unique value** (or change the password via `/admin/settings.php` immediately after the first login). The default `ChangeMe!Admin2026` must never reach production.
5. `storage/` writable by the web user; `storage/logs/php-error.log` empty.
6. Working `mail()` on the host — otherwise inquiries land in the DB and `inquiries.log` but nobody is notified, and admin replies are recorded but not delivered.
7. `php bin/build-seo.php --check` → `OK`.
8. Fetch `/robots.txt`, `/sitemap.xml`, `/llm.txt`, `/<INDEXNOW_KEY>.txt` — all must be `200`.
9. Confirm the CSP header on `/` includes `translate.googleapis.com`, `*.translate.goog` and `cdn.tiny.cloud`.
10. Drop the resume PDF at `assets/docs/Jamachi_Maurice_Resume.pdf` (currently a placeholder).
11. Run all three e2e suites against the live host (`e2e_smoke`, `e2e_mailer`, `e2e_admin`).
12. Log in to `/admin/`, confirm the dashboard renders, and send a test reply from an inquiry to verify the mail path end-to-end.
13. Send the first live IndexNow ping deliberately, once DNS is confirmed: `php indexnow.php https://<domain>/index.php`.

---

## 10. Known limitations

- **`mail()` dependency.** Delivery depends entirely on the host's MTA. Failures are only `error_log`'d to `storage/logs/php-error.log` and are **not** surfaced to the visitor — the form still reports success because the row is safely in `contact_inquiries`. On a host without a working MTA (common on Windows dev boxes) nothing is delivered.
- **Resume PDF is a placeholder** — replace `assets/docs/Jamachi_Maurice_Resume.pdf` before going live.
- **`data/*.php` helpers are dead in practice** once the DB is seeded; they exist as a render fallback only.
- **`assets/css/color-1.css` … `color-9.css` are dead.** The colour-palette selector was removed from the tools sidebar (replaced by Site Language). The files are intentionally left in place but nothing references them; delete them when convenient.
- **Live IndexNow ping has not been sent** — see §5.
- **Tailwind is the Play-CDN runtime**, which is why the CSP keeps `'unsafe-inline' 'unsafe-eval'`. Swapping to a compiled Tailwind build would let both be dropped.
- **Admin is single-owner by design.** One `owner` account, no RBAC, no multi-user support. Add more roles by extending the `admin_users.role` enum and the guard in `admin/includes/auth.php`.

---

## 11. Quick reference

```bash
# local dev server
php -S 127.0.0.1:8099 -t C:\laragon\www\JMN

# lint everything
php -l <file>

# regenerate + verify SEO artefacts
php bin/build-seo.php
php bin/build-seo.php --check

# regenerate the comprehensive SQL dump from the live DB
php bin/generate-sql.php

# IndexNow
php indexnow.php https://jmn.morexweb.com/index.php
php indexnow.php --dry-run https://jmn.morexweb.com/index.php

# tests
rm -f storage/ratelimit/*.json
php tools/e2e_smoke.php
rm -f storage/ratelimit/*.json
php tools/e2e_mailer.php
rm -f storage/ratelimit/*.json
php tools/e2e_admin.php

# browser test for the Site Language dropdown (needs Node 18+ for global WebSocket)
node tools/lang_dd_test.js

# admin back office
# http://127.0.0.1:8099/admin/login.php  (default: admin / ChangeMe!Admin2026)
```
