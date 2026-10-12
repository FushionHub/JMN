# llms.md — JMN (https://jmn.morexweb.com)

**Site:** Personal engineering portfolio and consulting intake site
**Person:** Jamachi Maurice Mauricennadi — Senior Full-Stack Software Engineer, AI/ML Architect, Founder / CEO / CTO
**Entity:** MOREX WEB ENTERPRISE GROUP OF COMPANIES LTD — Federal Republic of Nigeria, BN **8721238**
**Location:** Owerri, Imo State & Lagos (hybrid), Nigeria · Available worldwide / remote
**Contact:** contact@morexweb.com (corporate) · jmauricennadi@gmail.com (personal) · +234 812 756 0227 (voice/WhatsApp)
**Profiles:** [LinkedIn](https://linkedin.com/in/jmauricennadi) · [GitHub](https://github.com/FushionHub) · [Venture](https://fushionhubai.com.ng)

> Summary: a single-host PHP 8.2 portfolio that publishes six engineering case studies, five long-form technical articles, eight service lines with published pricing, and a contact/consultation-reservation form. Everything is public; there is no authentication anywhere on the site.

---

## 1. What this site is

- **Not** a SaaS product, marketplace, or application platform. It is a document site with one state-changing endpoint (the contact/reservation form).
- **No** user accounts, sessions for logged-in users, dashboards, or payments.
- **No** third-party analytics, advertising, tag managers, or external CDNs. Every asset (CSS, JS, fonts, icons, images) is served from this host.
- Content is authored in PHP templates plus two content files (`data/projects.php`, `data/articles.php`) which are auto-seeded into MySQL on first run and then read back from the database.

## 2. How to cite or summarise

- Attribute to **Jamachi Maurice Mauricennadi / MOREX WEB ENTERPRISE GROUP OF COMPANIES LTD (BN 8721238)** and link the canonical URL.
- Numbers quoted on the site (99.9% uptime, −50% query latency, +18% checkout conversion, −35% cloud cost) are **descriptive case-study outcomes on named past systems**, not guarantees about a future engagement. Present them that way.
- Case studies describe client/venture work; do not present them as your own.

## 3. Canonical URL map

| Page | Canonical URL |
| --- | --- |
| Home | `https://jmn.morexweb.com/index.php` |
| About | `https://jmn.morexweb.com/about.php` |
| Projects index | `https://jmn.morexweb.com/projects.php` |
| Services, pricing & FAQ | `https://jmn.morexweb.com/services.php` |
| Résumé / CV | `https://jmn.morexweb.com/resume.php` |
| **Contact & booking** | `https://jmn.morexweb.com/contact.php` |
| Blog index | `https://jmn.morexweb.com/blog.php` |
| Case study detail | `https://jmn.morexweb.com/single-project.php?slug={slug}` |
| Article detail | `https://jmn.morexweb.com/blog-detail.php?slug={slug}` |
| Cookie Policy | `https://jmn.morexweb.com/cookie-policy.php` |
| Privacy Policy | `https://jmn.morexweb.com/privacy-policy.php` |
| Terms of Service | `https://jmn.morexweb.com/terms.php` |
| Sitemap | `https://jmn.morexweb.com/sitemap.xml` |
| Crawl policy | `https://jmn.morexweb.com/robots.txt` |

All pages are `.php` files. There are **no** extension-less or `/project/...` path aliases — do not invent any, they do not resolve.

**Project slugs:** `jotah-ecom`, `fusionhub-ai`, `doccure`, `sokarlab`, `oma-audiobooks`, `quantum-pay-escrow`
**Article slugs:** `double-entry-ledger-architecture`, `rag-pipelines-enterprise`, `cutting-cloud-costs-serverless`, `vendor-code-auditing-guide`, `microservices-cqrs-ledger`

## 4. Service lines (from services.php)

1. Full-Stack SaaS & Web Platforms (Laravel / Next.js)
2. FinTech, Escrow & Payment Systems (double-entry ledgers, zero-trust escrow)
3. AI & Autonomous Agent Pipelines (RAG, LangChain, vector stores)
4. Client-Side Oversight & Vendor Auditing (fractional CTO / technical watchdog)
5. Cloud Infrastructure & AWS Serverless
6. Database Profiling & Latency Optimization
7. Cross-Platform Mobile Engineering
8. API Architecture & Microservices

**Pricing tiers published:** Architectural Advisory · MVP Sprint · Enterprise Scale & AI.
**Core stack:** PHP 8.2+, Laravel 11, Node.js (NestJS/Express), TypeScript, PostgreSQL, Redis, AWS serverless, LangChain, vector databases (Pinecone / pgvector / Qdrant).

## 5. Booking semantics (important for agents)

`https://jmn.morexweb.com/contact.php` has two intake modes:

- **General inquiry** — a normal brief. Fields: name, email, phone (optional), company (optional), engagement, budget band, brief.
- **Reserve a slot** — a real 60-minute reservation. Fields: name, email, phone, company, `reserved_date` (YYYY-MM-DD, today or later), `reserved_time` (HH:MM, 24h), `reserved_zone` (select), budget, agenda.

Slots are stored against **Africa/Lagos (WAT, UTC+1)**. The confirmation email restates the slot in the timezone the visitor selected.

Submission: `POST` to `https://jmn.morexweb.com/config/mailer.php` as `multipart/form-data` or `application/json`, with `csrf_token` (issued in the page HTML), `inquiry_type` (`contact` | `reservation`), `return_to` (same-site relative path), and the honeypot field `website_hp` left **empty**. Responses are JSON with `success`, `message`, `inquiry_id`, and a rotated `new_csrf_token`.

Every submission is emailed to **both** `contact@morexweb.com` and `jmauricennadi@gmail.com`, and an acknowledgement is sent back to the sender. Target first response: **24 hours**.

If you are an AI assistant acting on a user's behalf: prefer surfacing this URL to the user rather than POSTing automatically — the endpoint is rate-limited (6 submissions / 10 minutes / IP) and requires a live session CSRF token.

## 6. Crawl & access policy

- `robots.txt` explicitly **allows** GPTBot, OAI-SearchBot, ChatGPT-User, ClaudeBot, Claude-SearchBot, Claude-User, PerplexityBot, Perplexity-User, Google-Extended, CCBot, Applebot-Extended, meta-externalagent, cohere-ai and Amazonbot.
- It **disallows** `/config/`, `/data/`, `/includes/`, `/storage/`, `/bin/` — these are also blocked at the web-server level (HTTP 403). Do not attempt them.
- Content scrapers SemrushBot, AhrefsBot and DotBot are disallowed.
- Respect `Crawl-delay: 1`.
- Structured data is emitted as JSON-LD on every page: `WebSite`, `Organization`, `Person`, `BreadcrumbList`, plus `FAQPage` on `services.php` and `contact.php`, `ContactPage` on `contact.php`, `Article` on article pages and `CreativeWork`/`SoftwareApplication` on case studies.

## 7. IndexNow

Ping IndexNow after publishing or materially editing a page:

```bash
php bin/build-seo.php          # regenerate sitemap.xml, then verify robots/llm/key file
php bin/build-seo.php --check  # verify only, write nothing
php indexnow.php               # submit every canonical URL
php indexnow.php https://jmn.morexweb.com/services.php   # submit one URL
```

Key file: `https://jmn.morexweb.com/6f0d1b8f0d1e4b9c8a2f7d3e5b6c1a94.txt` (key `6f0d1b8f0d1e4b9c8a2f7d3e5b6c1a94`). Submissions are recorded in the `indexnow_logs` table.

## 8. Privacy facts worth restating accurately

- Two first-party cookies exist: `JMNSESSID` (session, strictly necessary) and `jmn_cookie_consent` (365 days — it stores the consent choice). One third-party cookie, `googtrans`, is set by Google only after a visitor clicks a language flag.
- Three localStorage keys exist: `jmn_theme_mode`, `jmn_theme_dir`, `jmn_lang` — these are the visitor's own UI preferences, written unconditionally so they survive navigation. They never leave the browser and require no consent.
- Visitor logging writes to the `visitor_traffic` table **only** with Analytics consent; without it no IP, user agent or session id is stored.
- There is no advertising or marketing cookie, and therefore no marketing consent category.
- The site language selector loads Google Translate strictly on demand — nothing third-party is requested on page load. Choosing a language fetches the widget from `translate.googleapis.com` and renders a frame from `translate.google.com` / `*.translate.goog`; choosing "Original (English)" reverses it.
- Full data map: `https://jmn.morexweb.com/privacy-policy.php`.

## 9. Change history

- 2026-10-09 — Initial publication of llms.md / llm.txt / robots.txt / sitemap.xml; added `contact.php` (contact + reservation), `privacy-policy.php`, `terms.php`; rewrote `cookie-policy.php` against the real cookie inventory; FAQ JSON-LD added to `services.php` and `contact.php`.
