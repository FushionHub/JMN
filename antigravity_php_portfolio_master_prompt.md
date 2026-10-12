# MASTER PROMPT: FULL-STACK PHP PORTFOLIO ARCHITECTURE FOR JAMACHI MAURICE MAURICENNADI

---

### INSTRUCTIONS FOR THE AI / ANTIGRAVITY ENGINE:
You are an elite Senior Full-Stack PHP Architect and UI/UX Systems Specialist. Your task is to take my existing HTML/CSS/JavaScript frontend assets and architect a modern, secure, and modular PHP (PHP 8.2+) dynamic web application that serves as my definitive personal portfolio and engineering showcase.

You must build this with clean architecture, adhering to PSR-12 coding standards, strict type checking (`declare(strict_types=1);`), modular components (partials/templates), zero security vulnerabilities (XSS, CSRF, and SQL/header injection protection), and blazing-fast performance.

Do not use bloated frameworks unless requested; implement a lightweight, native, and maintainable MVC or router-based modular architecture.

---

## 1. CORE DEVELOPER & FOUNDER PROFILE (DATA TO INJECT)

Embed all of the following biographical, executive, and technical details throughout the application:

### Personal & Contact Information
* **Full Name:** Jamachi Maurice Mauricennadi
* **Professional Titles:** Senior Full-Stack Software Engineer | AI/ML Architect | Tech Founder, CEO & CTO
* **Email:** `jmauricennadi@gmail.com` / `contact@morexweb.com`
* **Phone / WhatsApp:** `+234 812 756 0227`
* **Location:** Owerri, Imo State & Lagos (Hybrid), Nigeria (Available Globally / Remote)
* **LinkedIn:** `linkedin.com/in/jmauricennadi`
* **GitHub:** `github.com/FushionHub`
* **Live Portfolio / Venture Domain:** `jmn.morexweb.com`
* **Corporate Entity:** Founder, CEO & CTO at **MOREX WEB ENTERPRISE GROUP OF COMPANIES LTD** (BN / RC No: 8721238, Federal Republic of Nigeria)

### Education & Honors
* **B.Sc. in Software Engineering** — Brigham Young University (BYU), 2024
* **B.Sc. in Computer Science** — Imo State University (IMSU), 2022 – Present
* **GDG Best Developer of the Year (2024)** — Google Developer Groups
* **Project Management Certification** — Professional Institute of Management (PIM)
* **AWS Cloud Practitioner Essentials** — Amazon Web Services (AWS)
* **CISCO Certificate in Advanced JavaScript** (2020) & freeCodeCamp Algorithms Certification

### Executive Summary & Professional Identity
A results-driven hybrid engineer-founder combining 5+ years of hands-on technical architecture with the strategic acumen of an executive leader. Specialized in designing high-availability backend engines, autonomous AI agent pipelines (RAG), financial transaction ledgers with zero-trust escrow models, and scalable AWS cloud-native systems. Proven record of cutting database query latencies by 50%, reducing cloud infrastructure operating costs by 35%, boosting checkout conversions by 18%, and maintaining 99.9% uptime across production platforms.

---

## 2. DETAILED PROJECT SHOWCASE & CASE STUDIES (DATA LAYER)

The PHP application must store project data in a structured, modular data store (such as a PHP associative array file `data/projects.php` or a lightweight SQLite/MySQL database layer with a clean repository interface) that populates the UI dynamically. Include these flagship projects:

### 1. JOTAH ECOM NG LTD (FinTech & Multi-Vendor E-Commerce)
* **Role:** Founder & Lead Engineer
* **Stack:** PHP (Laravel 11), Node.js, TypeScript, PostgreSQL, Redis, AWS
* **Key Achievements & Architecture:**
  * Engineered Nigeria’s premier escrow-integrated multi-vendor marketplace with automated fund custody.
  * Implemented an ACID-compliant double-entry virtual ledger in PostgreSQL to eliminate arbitrary balance modifications and prevent balance discrepancies.
  * Deployed distributed Redis locking states (`SET NX EX`) and atomic SQL updates (`UPDATE products SET stock = stock - :qty WHERE stock >= :qty`), eliminating race conditions during high-volume checkouts.
  * Boosted checkout transaction success rates by **18%**.
  * Automated data consolidation pipelines that parsed and validated vendor onboarding contracts from unstructured PDF/Word files.

### 2. FusionHub AI CMS (Headless AI Automation & Content Engine)
* **Role:** Founder & Lead Architect
* **Stack:** Next.js, React, Node.js, TypeScript, LangChain, OpenAI API, Google Gemini, Tailwind CSS
* **Key Achievements & Architecture:**
  * Architected an intelligent headless CMS that automates 80% of routine content drafting, synthesis, and scheduling workflows.
  * Built real-time Retrieval-Augmented Generation (RAG) pipelines for contextual document analysis and dynamic metadata generation.
  * Migrated legacy monolithic services to an **AWS Serverless infrastructure** (Lambda, API Gateway, S3, DynamoDB), cutting operational overhead by **35%** while locking system reliability at a **99.9% uptime** SLA for thousands of active users.

### 3. Doccure (Telemedicine & Healthcare Data Platform)
* **Role:** Founder & Lead Developer
* **Stack:** PHP (Laravel), TypeScript, MySQL/PostgreSQL, Redis, AWS S3, Tailwind CSS
* **Key Achievements & Architecture:**
  * Overhauled the relational database schema, index clustering, and Redis caching layers to reduce consultation query latency by **50%**.
  * Built HIPAA-compliant secure document handling pipelines featuring dynamic encrypted PDF medical report generation with validated practitioner digital signatures.
  * Enforced role-based access control (RBAC) and end-to-end transport layer security (TLS 1.3).

### 4. SOKARLAB LTD (Modular Frameworks & Backend Systems)
* **Role:** Software Architect
* **Stack:** TypeScript, Node.js, PHP, Express, PostgreSQL
* **Key Achievements & Architecture:**
  * Engineered a reusable modular backend framework that reduced standard boilerplate code by **60%**, slashing MVP setup time for client initiatives.
  * Built centralized monitoring and automated error diagnostics for distributed services.

### 5. Oma Audiobooks (AI-Driven Audio Streaming & Recommendation)
* **Role:** Senior Backend Engineer
* **Stack:** Node.js, Firebase, Python, Machine Learning Algorithms
* **Key Achievements & Architecture:**
  * Integrated a behavioral machine learning recommendation engine that boosted average user session duration by **40%**.

---

## 3. TECHNICAL MATRIX & CAPABILITIES TO DISPLAY

Organize the competencies into structured, filterable visual blocks:
* **Backend Architecture:** PHP (Laravel 11, Modern PHP 8.2+), Node.js (NestJS, Express), TypeScript, RESTful API Design, Microservices, Domain-Driven Design (DDD), Double-Entry Accounting Systems.
* **Frontend & UI/UX:** HTML5, Modern Semantic CSS, Tailwind CSS, JavaScript (ESNext), React, Next.js, Responsive Cross-Browser UI.
* **Mobile Engineering:** Cross-Platform Mobile Apps in **Flutter (Dart)** and **React Native**, Native Android modules in **Java** and **Kotlin**.
* **Databases & Caching:** PostgreSQL, MySQL, MongoDB, Redis (In-memory caching, distributed locks, session persistence).
* **Cloud & DevOps:** AWS (Lambda, API Gateway, S3, RDS, DynamoDB, CloudFront), Docker containerization, GitHub Actions CI/CD, Cloudflare edge security.
* **AI & Agentic Systems:** RAG Pipelines, Autonomous Agent Workflows, Prompt Engineering, Model Benchmarking/RLHF Evaluation (Gemini, DeepSeek, OpenAI), LangChain.
* **Governance & Project Leadership:** Agile/Scrum Sprint Facilitation (PIM Certified), Definition of Done (DoD) Gatekeeping, Technical Vendor/Agency Code Auditing.

---

## 4. PHP APPLICATION ARCHITECTURE & CODE REQUIREMENTS

Structure the converted project using a clean, modern PHP architecture:

```
portfolio/
├── public/
│   ├── index.php                  # Central entry point / Front Controller
│   ├── .htaccess                  # Clean URLs & security rewrite rules
│   ├── assets/
│   │   ├── css/                   # Compiled Tailwind / custom stylesheets
│   │   ├── js/                    # Client-side scripts (filtering, theme toggle, animations)
│   │   ├── images/                # Optimized project screenshots, avatars, company logos
│   │   └── docs/                  # Downloadable Resume (PDF)
├── config/
│   ├── config.php                 # App config (URLs, environment, mail credentials)
│   └── security.php               # CSRF helpers, CSP headers, rate-limiting configs
├── src/
│   ├── Controllers/
│   │   ├── HomeController.php     # Main page renderer
│   │   ├── ProjectController.php  # Detailed project case-study views
│   │   └── ContactController.php  # Form validation, rate limiting, and email delivery
│   ├── Services/
│   │   ├── MailerService.php      # SMTP / native mail handler
│   │   └── ProjectService.php     # Data retrieval and filtering logic
│   └── Helpers/
│       ├── Sanitizer.php          # XSS sanitization and output escaping helper
│       └── Session.php            # Session management and CSRF token validator
├── data/
│   ├── projects.php               # Rich associative array of all portfolio case studies
│   └── skills.php                 # Categorized skills matrix and proficiency ratings
├── views/
│   ├── layouts/
│   │   ├── header.php             # Meta tags, SEO, OpenGraph, navbar, dark mode toggle
│   │   └── footer.php             # Links, credentials, legal disclaimer, social links
│   ├── partials/
│   │   ├── hero.php               # High-impact introduction with executive stats
│   │   ├── about.php              # Bio, journey, and education credentials
│   │   ├── metrics.php            # 4 flagship stat cards (99.9% Uptime, -50% Latency, etc.)
│   │   ├── projects.php           # Filterable case study cards with tags and modal previews
│   │   ├── stack.php              # Categorized tech badges and architecture descriptions
│   │   ├── oversight.php          # Client-side oversight / consulting services section
│   │   └── contact.php            # Secure AJAX-enabled contact form
│   └── project-detail.php         # Dedicated deep-dive template for full architectural specs
└── .env.example                   # Environment variable template
```

---

## 5. FUNCTIONAL & SECURITY SPECIFICATIONS

1. **Security Standards:**
   * Every form submission must be protected by a cryptographically secure CSRF token stored in `$_SESSION`.
   * Add a hidden honeypot input field (`website_url_check`) to block automated spam bots silently.
   * Sanitize all inputs using `filter_var()` and escape all outputs with `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`.
   * Implement strict HTTP Security Headers (X-Content-Type-Options, X-Frame-Options, Content-Security-Policy).
   * Implement IP-based submission throttling or basic session rate limiting to avoid email spamming.

2. **Dynamic Features:**
   * **Project Filter:** Categorize projects by tags (`FinTech`, `AI & Automation`, `Cloud & DevOps`, `Mobile App`). Users can filter with smooth JavaScript animations without full page reloads.
   * **Contact Processing:** Support both asynchronous JSON fetch submissions and standard POST fallback, returning accessible success or error alert banners.
   * **Dynamic Case Study Routing:** Allow routes like `/?project=jotah-ecom` or `/project/jotah-ecom` to render a comprehensive, rich technical deep dive for that specific project.
   * **Dark / Light Mode Persistence:** Include a toggle switch with localStorage memory and system preference detection.

3. **Performance Optimization:**
   * Pure lightweight vanilla JS or lightweight Alpine.js/Tailwind CSS for animations.
   * Zero render-blocking libraries; inline critical CSS where applicable.
   * Clean semantic HTML5 elements for maximum accessibility (ARIA compliance) and SEO performance.

---

## 6. EXPECTED OUTPUT FROM ANTIGRAVITY

Now, review the existing HTML frontend project files, decompose them into this modular PHP architecture, and generate:
1. The complete directory layout and `.htaccess` file.
2. The core configuration and security helper scripts.
3. The dynamic `projects.php` dataset containing the full specs for JOTAH ECOM, FusionHub AI, Doccure, and SOKARLAB.
4. The clean view partials and main layout templates integrating Jamachi Maurice Mauricennadi's complete history, metrics, and branding.
5. The robust `ContactController.php` and `MailerService.php` files ready for deployment.