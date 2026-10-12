-- ============================================================================
-- JMN Portfolio - comprehensive database schema + seed content
-- Generated: 2026-10-10 19:34:18 UTC
--
-- Re-importable: each table is DROPped then CREATEd, so running this file
-- against an existing `jmn` database rebuilds it from scratch.
--
-- Content tables (blogs, projects, services, testimonials, skills) include
-- their real seed rows so a fresh import renders the live site immediately.
-- admin_users includes the seeded owner account (password is bcrypt of the
-- ADMIN_PASSWORD env, default "ChangeMe!Admin2026").
--
-- Runtime tables (visitor_traffic, cookie_consents, contact_inquiries,
-- indexnow_logs) ship schema only - their data is user-generated.
-- ==========================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

-- ---------------------------------------------------------------------------
-- Table: visitor_traffic
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `visitor_traffic`;
CREATE TABLE `visitor_traffic` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `page_url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referrer` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'desktop',
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visited_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_visited_at` (`visited_at`),
  KEY `idx_ip` (`ip_address`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Table: cookie_consents
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `cookie_consents`;
CREATE TABLE `cookie_consents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `consent_type` enum('all','necessary','custom','declined') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `essential_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `analytics_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `marketing_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `preferences_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `consented_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_consented_at` (`consented_at`),
  KEY `idx_ip` (`ip_address`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Table: contact_inquiries
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `contact_inquiries`;
CREATE TABLE `contact_inquiries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `budget` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('new','read','replied','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `inquiry_type` enum('contact','reservation') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'contact',
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reserved_at` datetime DEFAULT NULL,
  `reserved_zone` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `replied_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `reply_message` text COLLATE utf8mb4_unicode_ci,
  `replied_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Table: blogs
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `blogs`;
CREATE TABLE `blogs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_time` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '5 min read',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('published','draft') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `published_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_status` (`status`),
  KEY `idx_published_at` (`published_at`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data for blogs (5 rows)
INSERT INTO `blogs` (`id`, `slug`, `title`, `subtitle`, `category`, `read_time`, `image`, `excerpt`, `content`, `status`, `published_at`, `created_at`, `updated_at`) VALUES (1, 'double-entry-ledger-architecture', 'Architecting ACID-Compliant Double-Entry Ledgers in PostgreSQL', 'Eliminating balance discrepancies and race conditions in high-volume FinTech escrow marketplaces.', 'FinTech Architecture', '7 min read', 'img/blog/1.jpg', 'How to design a mathematical double-entry virtual ledger using PostgreSQL and distributed Redis locks to guarantee 100% financial transaction integrity.', '                <p class=\"mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]\">
                    In multi-vendor e-commerce and peer-to-peer escrow platforms, relying on a naive <code>users.balance</code> column updated with standard arithmetic is an existential architectural risk. Under concurrent traffic drops, race conditions and partial failures invariably produce phantom balances, lost funds, and agonizing reconciliation disputes.
                </p>
                <h4 class=\"font-montserrat text-[22px] font-bold text-[#111a24] mt-[24px] mb-[12px] leading-[1.3]\">The Principle of Zero-Sum Balancing</h4>
                <p class=\"mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]\">
                    Double-entry bookkeeping dictates that every single transaction consists of at least two balanced entries: a debit and a credit. The sum of all debits must equal the sum of all credits at all times. By enforcing this at the schema layer with database constraints and immutable transaction logs, modifying balance state without an explicit audit trail becomes mathematically impossible.
                </p>
                <div class=\"my-[20px] p-[20px] bg-[#111a24] text-[#fff] rounded-[15px] font-mono text-[13px] overflow-x-auto leading-[22px]\">
                    -- Simplified PostgreSQL Ledger Transaction<br>
                    BEGIN TRANSACTION ISOLATION LEVEL SERIALIZABLE;<br><br>
                    INSERT INTO ledger_entries (account_id, entry_type, amount, currency, transaction_ref)<br>
                    VALUES <br>
                    &nbsp;&nbsp;(:buyer_wallet, \'DEBIT\', 25000.00, \'NGN\', :tx_hash),<br>
                    &nbsp;&nbsp;(:escrow_vault,  \'CREDIT\', 25000.00, \'NGN\', :tx_hash);<br><br>
                    COMMIT;
                </div>
                <h4 class=\"font-montserrat text-[22px] font-bold text-[#111a24] mt-[24px] mb-[12px] leading-[1.3]\">Distributed Redis Locking for Atomic Inventory</h4>
                <p class=\"mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]\">
                    Beyond ledger consistency, preventing inventory overselling requires coordinated distributed locks. By utilizing Redis <code>SET resource_name my_random_value NX PX 30000</code> alongside atomic SQL conditional updates (<code>UPDATE products SET stock = stock - :qty WHERE id = :id AND stock >= :qty</code>), checkout drops achieve deterministic thread-safe execution with zero order collisions.
                </p>
            ', 'published', '2026-10-04 20:54:14', '2026-10-04 20:54:14', '2026-10-08 16:38:06');
INSERT INTO `blogs` (`id`, `slug`, `title`, `subtitle`, `category`, `read_time`, `image`, `excerpt`, `content`, `status`, `published_at`, `created_at`, `updated_at`) VALUES (2, 'rag-pipelines-enterprise', 'Building High-Precision Enterprise RAG Pipelines with LangChain & Google Gemini', 'Overcoming hallucinations and contextual latency in production generative AI applications.', 'AI & Autonomous Agents', '6 min read', 'img/blog/2.jpg', 'A deep dive into chunking strategies, hybrid vector retrieval, and reranking pipelines to deliver reliable autonomous AI agents for corporate workflows.', '                <p class=\"mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]\">
                    While prototyping an LLM wrapper requires fewer than 50 lines of code, deploying a production Retrieval-Augmented Generation (RAG) system that corporate clients can trust demands surgical engineering around document chunking, semantic overlap, and vector similarity thresholds.
                </p>
                <h4 class=\"font-montserrat text-[22px] font-bold text-[#111a24] mt-[24px] mb-[12px] leading-[1.3]\">The Triad of Precision Retrieval</h4>
                <p class=\"mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]\">
                    Dense vector embeddings alone often miss domain-specific identifiers, SKU numbers, or exact legal clauses. In FusionHub AI CMS, we implemented hybrid search: combining BM25 keyword matching with dense cosine distance embeddings, then passing the top 20 candidate passages through a cross-encoder reranker before feeding them into Google Gemini Pro’s context window.
                </p>
                <div class=\"my-[20px] p-[20px] bg-[#f7f5fb] border-l-[4px] border-[#f41a4a] rounded-[10px]\">
                    <p class=\"m-[0] font-montserrat text-[14px] italic text-[#111a24] leading-[24px]\">
                        “A RAG system is only as intelligent as the relevance of its retrieved context. Feeding noisy tokens to frontier models doesn’t improve comprehension—it accelerates hallucination.”
                    </p>
                </div>
            ', 'published', '2026-10-04 20:54:14', '2026-10-04 20:54:14', '2026-10-08 16:38:06');
INSERT INTO `blogs` (`id`, `slug`, `title`, `subtitle`, `category`, `read_time`, `image`, `excerpt`, `content`, `status`, `published_at`, `created_at`, `updated_at`) VALUES (3, 'cutting-cloud-costs-serverless', 'Slashing Infrastructure Overhead by 35% with AWS Serverless Architecture', 'How we migrated containerized monoliths to event-driven serverless while preserving 99.9% uptime.', 'Cloud Engineering', '5 min read', 'img/blog/3.jpg', 'Practical techniques for replacing expensive idle EC2 compute instances with AWS Lambda, DynamoDB on-demand, and CloudFront edge caching.', '                <p class=\"mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]\">
                    Many startups and growing enterprises pay thousands of dollars each month for idle cloud capacity. Servers provisioned for peak traffic sit at 4% CPU utilization during overnight lulls, bleeding financial runway.
                </p>
                <h4 class=\"font-montserrat text-[22px] font-bold text-[#111a24] mt-[24px] mb-[12px] leading-[1.3]\">The Serverless Shift</h4>
                <p class=\"mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]\">
                    By decomposing synchronous batch jobs and monolithic endpoints into event-driven AWS Lambda functions fronted by API Gateway and cached aggressively via CloudFront, we slashed compute overhead by 35% across our production deployments. When zero requests arrive, compute cost drops to exactly zero.
                </p>
            ', 'published', '2026-10-04 20:54:14', '2026-10-04 20:54:14', '2026-10-08 16:38:06');
INSERT INTO `blogs` (`id`, `slug`, `title`, `subtitle`, `category`, `read_time`, `image`, `excerpt`, `content`, `status`, `published_at`, `created_at`, `updated_at`) VALUES (4, 'vendor-code-auditing-guide', 'The Non-Technical Founder’s Guide to Auditing Agency Codebases', 'Critical checkpoints to verify before signing off on software agency deliverables and releasing final milestones.', 'Technical Oversight', '8 min read', 'img/blog/4.jpg', 'How non-technical founders can prevent agency scope fraud, identify technical debt early, and enforce strict Definition of Done gatekeeping.', '                <p class=\"mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]\">
                    Every month, non-technical founders hand over millions to outsourced software development agencies, only to receive brittle prototypes held together by hardcoded secrets, duplicate dependencies, and unindexed database queries that collapse the moment real users sign up.
                </p>
                <h4 class=\"font-montserrat text-[22px] font-bold text-[#111a24] mt-[24px] mb-[12px] leading-[1.3]\">The 5-Point Milestone Audit Checklist</h4>
                <p class=\"mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]\">
                    Before approving any deliverable invoice, verify: (1) Source code ownership and clean Git commit histories, (2) Automated unit and integration test coverage above 80%, (3) Complete absence of hardcoded API keys and credentials, (4) Index coverage on foreign key columns, and (5) Stress-tested deployment documentation that compiles cleanly on a fresh container.
                </p>
            ', 'published', '2026-10-04 20:54:14', '2026-10-04 20:54:14', '2026-10-08 16:38:06');
INSERT INTO `blogs` (`id`, `slug`, `title`, `subtitle`, `category`, `read_time`, `image`, `excerpt`, `content`, `status`, `published_at`, `created_at`, `updated_at`) VALUES (5, 'microservices-cqrs-ledger', 'High-Throughput CQRS & Event Sourcing in FinTech', 'Eliminating Database Deadlocks Under Concurrency', 'Distributed Systems', '8 min read', 'img/blog/5.png', 'Architectural blueprint for separating read and write models in multi-tenant financial ledgers.', '<p>Event sourcing guarantees tamper-proof immutable audit records in modern escrow applications.</p>', 'published', '2026-10-04 21:07:32', '2026-10-04 21:07:32', '2026-10-09 19:10:13');

-- ---------------------------------------------------------------------------
-- Table: projects
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_period` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `category_filter` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categories` json DEFAULT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_large` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metrics` json DEFAULT NULL,
  `stack` json DEFAULT NULL,
  `architectural_highlights` json DEFAULT NULL,
  `problem_statement` text COLLATE utf8mb4_unicode_ci,
  `solution` text COLLATE utf8mb4_unicode_ci,
  `gallery` json DEFAULT NULL,
  `live_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_featured` (`featured`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data for projects (6 rows)
INSERT INTO `projects` (`id`, `slug`, `title`, `subtitle`, `role`, `client`, `date_period`, `featured`, `category_filter`, `category_name`, `categories`, `summary`, `image`, `image_large`, `metrics`, `stack`, `architectural_highlights`, `problem_statement`, `solution`, `gallery`, `live_url`, `created_at`) VALUES (1, 'jotah-ecom', 'JOTAH ECOM NG LTD', 'FinTech & Escrow Multi-Vendor Marketplace', 'Founder & Lead Engineer', 'Jotah Ecom NG Ltd', '2023 - 2024', 1, 'web applications fintech', 'FinTech & Escrow', '[\"FinTech\", \"E-Commerce\", \"Web Platform\"]', 'Nigeria’s premier escrow-integrated multi-vendor marketplace with automated fund custody, double-entry ledger, and distributed Redis locking.', 'img/project/1.jpg', 'img/project/11.jpg', '{\"Ledger\": \"100% ACID\", \"Status\": \"Production Ready\", \"Conversion\": \"+18%\", \"Concurrency\": \"Zero Race Drops\"}', '[\"PHP (Laravel 11)\", \"Node.js\", \"TypeScript\", \"PostgreSQL\", \"Redis\", \"AWS (SQS, S3, CloudFront)\", \"Docker\"]', '[\"ACID Double-Entry Ledger: Designed and deployed a strict virtual ledger schema in PostgreSQL with debit/credit balance pairing to eliminate arbitrary balance modifications and resolve discrepancy risks at the database kernel level.\", \"Concurrency Control & Atomic Inventory: Deployed distributed Redis locking mechanisms (SET NX EX) combined with atomic SQL decrement queries (UPDATE products SET stock = stock - :qty WHERE stock >= :qty) to guarantee zero overselling during rapid flash-sales.\", \"Automated Fund Custody: Engineered an automated multi-party escrow custody engine that holds buyer payments until cryptographic or buyer-authorized delivery confirmations are verified.\", \"Vendor Onboarding Pipeline: Automated asynchronous parsing pipelines for merchant contracts from unstructured PDFs and Word documents, decreasing onboarding verification delays by 65%.\", \"Performance Surge: Elevated overall successful checkout transactions by +18% through resilient asynchronous queue processing via AWS SQS.\"]', 'Traditional Nigerian multi-vendor marketplaces suffer from severe merchant mistrust, payment fraud, race conditions during high-demand inventory flash drops, and painful bookkeeping reconciliation gaps. The challenge was building an unalterable financial transactions layer while sustaining high throughput with zero order collision.', 'Architected an end-to-end cloud platform featuring Laravel 11 micro-services, an isolated double-entry accounting engine in PostgreSQL, distributed Redis locks, and an AWS CloudFront CDN delivery system. Every buyer naira is held in a segregated cryptographic escrow vault until milestone delivery conditions are satisfied.', '[\"img/project/1.jpg\", \"img/project/2.jpg\", \"img/project/3.jpg\", \"img/project/4.jpg\"]', 'https://jotahecom.com', '2026-10-04 20:54:14');
INSERT INTO `projects` (`id`, `slug`, `title`, `subtitle`, `role`, `client`, `date_period`, `featured`, `category_filter`, `category_name`, `categories`, `summary`, `image`, `image_large`, `metrics`, `stack`, `architectural_highlights`, `problem_statement`, `solution`, `gallery`, `live_url`, `created_at`) VALUES (2, 'fusionhub-ai', 'FusionHub AI CMS', 'Headless AI Automation & Content Engine', 'Founder & Lead Architect', 'FusionHub AI Systems', '2023 - Present', 1, 'ai applications cloud', 'AI & Automation', '[\"AI & ML\", \"Headless CMS\", \"Cloud Architecture\"]', 'Intelligent headless CMS automating 80% of routine content drafting, synthesis, and scheduling through real-time RAG pipelines and AWS Serverless.', 'img/project/2.jpg', 'img/project/22.jpg', '{\"Latency\": \"< 800ms Stream\", \"Automation\": \"80% Reduction\", \"Uptime SLA\": \"99.9%\", \"Cloud Savings\": \"-35% Overhead\"}', '[\"Next.js\", \"React\", \"Node.js\", \"TypeScript\", \"LangChain\", \"OpenAI API\", \"Google Gemini\", \"AWS (Lambda, S3, DynamoDB)\", \"Tailwind CSS\"]', '[\"Retrieval-Augmented Generation (RAG): Built resilient vector search pipelines that ingest multi-format corporate knowledge repositories and inject contextual reference frames into dynamic prompts.\", \"AWS Serverless Migration: Transitioned legacy monolithic containerized services to event-driven AWS Lambda, DynamoDB, and Amazon S3, slashing monthly operational cloud costs by 35%.\", \"Reliability & High Availability: Maintained a verified 99.9% uptime SLA across continuous agentic generative tasks for thousands of active multi-tenant users.\", \"Dynamic Semantic Synthesis: Built autonomous agents utilizing OpenAI GPT-4o and Google Gemini Pro for real-time editorial reviews, SEO metadata generation, and cross-platform publishing.\"]', 'Editorial and media teams spend hundreds of manual hours every week drafting boilerplate articles, cross-referencing brand guidelines, and formatting structured metadata across fragmented CMS instances, resulting in ballooning editorial costs and delayed time-to-market.', 'Constructed a modern headless AI platform with high-speed Next.js frontend, Node.js agent microservices, LangChain orchestration, and serverless compute on AWS. Writers upload raw source documents, and the system automatically synthesizes verified multi-channel copy while maintaining full editorial control.', '[\"img/project/2.jpg\", \"img/project/3.jpg\", \"img/project/1.jpg\", \"img/project/5.jpg\"]', 'https://fushionhubai.com.ng', '2026-10-04 20:54:14');
INSERT INTO `projects` (`id`, `slug`, `title`, `subtitle`, `role`, `client`, `date_period`, `featured`, `category_filter`, `category_name`, `categories`, `summary`, `image`, `image_large`, `metrics`, `stack`, `architectural_highlights`, `problem_statement`, `solution`, `gallery`, `live_url`, `created_at`) VALUES (3, 'doccure', 'Doccure Telemedicine Platform', 'Healthcare Data & Consultation Platform', 'Founder & Lead Developer', 'Doccure Health Tech', '2023 - 2024', 1, 'web healthtech applications', 'HealthTech', '[\"HealthTech\", \"Telemedicine\", \"Data Security\"]', 'HIPAA-compliant telemedicine platform with Redis query acceleration cutting database latency by 50% and secure digital medical report generation.', 'img/project/3.jpg', 'img/project/33.jpg', '{\"Security\": \"HIPAA / TLS 1.3\", \"DB Latency\": \"-50% Faster\", \"Signatures\": \"Cryptographic\", \"Availability\": \"High Concurrency\"}', '[\"PHP (Laravel)\", \"TypeScript\", \"MySQL / PostgreSQL\", \"Redis\", \"AWS S3 (KMS Encrypted)\", \"Tailwind CSS\", \"WebRTC\"]', '[\"Database Index Clustering: Overhauled the relational database normalization, query profiling, composite index clustering, and Redis key-value caching to cut doctor-patient lookup query latencies by 50%.\", \"HIPAA-Compliant Document Pipeline: Built automated encrypted PDF generation for clinical diagnostic reports featuring validated practitioner cryptographic signatures and tamper-evident audit logs.\", \"Zero-Trust RBAC & Encryption: Enforced granular role-based access control (RBAC), KMS envelope encryption for resting health records on AWS S3, and strict TLS 1.3 in-transit protections.\", \"Real-Time Tele-Consultations: Configured resilient WebRTC signaling and session tokens to support real-time audio/video consultations with automated fallbacks.\"]', 'Healthcare portals often suffer from sluggish clinical history lookups, high consultation room latency, and precarious file storage that fails regulatory compliance for patient confidentiality and digital practitioner signatures.', 'Engineered a high-performance clinical platform utilizing Laravel, Redis caching layers, PostgreSQL composite indices, and an AWS KMS-encrypted document repository. Designed the architecture to satisfy rigorous data privacy mandates while maintaining instantaneous UI responsiveness.', '[\"img/project/3.jpg\", \"img/project/4.jpg\", \"img/project/6.jpg\", \"img/project/2.jpg\"]', 'https://doccure.morexweb.com', '2026-10-04 20:54:14');
INSERT INTO `projects` (`id`, `slug`, `title`, `subtitle`, `role`, `client`, `date_period`, `featured`, `category_filter`, `category_name`, `categories`, `summary`, `image`, `image_large`, `metrics`, `stack`, `architectural_highlights`, `problem_statement`, `solution`, `gallery`, `live_url`, `created_at`) VALUES (4, 'sokarlab', 'SOKARLAB LTD Frameworks', 'Modular Backend & Distributed Diagnostic Systems', 'Software Architect', 'Sokarlab Ltd', '2022 - 2023', 0, 'cloud applications web', 'Cloud & Systems', '[\"Software Architecture\", \"Modular Frameworks\", \"DevOps\"]', 'Reusable modular backend framework that reduced standard boilerplate code by 60%, slashing MVP setup time for enterprise client initiatives.', 'img/project/4.jpg', 'img/project/44.jpg', '{\"Monitoring\": \"Automated Diagnostics\", \"Boilerplate\": \"-60% Reduction\", \"MVP Delivery\": \"2x Speed\", \"Test Coverage\": \"94%\"}', '[\"TypeScript\", \"Node.js\", \"PHP 8.2\", \"Express\", \"PostgreSQL\", \"Docker\", \"GitHub Actions\"]', '[\"Reusable Architectural Blueprint: Created a modular scaffolding architecture that decoupled domain business logic from infrastructure adapters, reducing new project initialization code by 60%.\", \"Centralized Health Diagnostics: Built automated error reporting, request tracing, and latency monitoring hooks to alert distributed engineering teams before clients detect service degradations.\", \"Strict CI/CD Pipelines: Enforced automated linting, security scanning, and unit/integration test gates through GitHub Actions before any artifact promotion to staging or production.\"]', 'Frequent technical client launches suffered from redundant boilerplate setup, inconsistent error handling, divergent database migration strategies, and scattered logging across different micro-services.', 'Engineered a unified enterprise boilerplate framework that standardized authentication, database abstraction, logging, and continuous deployment workflows, halving time-to-market across multiple client products.', '[\"img/project/4.jpg\", \"img/project/5.jpg\", \"img/project/1.jpg\", \"img/project/6.jpg\"]', 'https://sokarlab.com', '2026-10-04 20:54:14');
INSERT INTO `projects` (`id`, `slug`, `title`, `subtitle`, `role`, `client`, `date_period`, `featured`, `category_filter`, `category_name`, `categories`, `summary`, `image`, `image_large`, `metrics`, `stack`, `architectural_highlights`, `problem_statement`, `solution`, `gallery`, `live_url`, `created_at`) VALUES (5, 'oma-audiobooks', 'Oma Audiobooks Platform', 'AI-Driven Audio Streaming & Behavioral Recommendation', 'Senior Backend Engineer', 'Oma Media Group', '2022 - 2023', 0, 'ai applications', 'AI & Streaming', '[\"AI / ML\", \"Audio Streaming\", \"Mobile API\"]', 'Integrated a behavioral machine learning recommendation engine that boosted average user session duration by 40% with adaptive bitrate audio delivery.', 'img/project/5.jpg', 'img/project/55.jpg', '{\"Latency\": \"< 150ms Chunk Start\", \"Retention\": \"+28% Monthly\", \"Engagement\": \"+40% Session Time\", \"Throughput\": \"10k+ Concurrents\"}', '[\"Node.js\", \"Firebase\", \"Python\", \"Machine Learning Algorithms\", \"Google Cloud\", \"RESTful API\"]', '[\"Behavioral ML Recommender: Integrated a user listening habit vector model in Python that dynamically recommended chapters, authors, and genres, elevating user engagement duration by 40%.\", \"Optimized Audio Chunk Streaming: Implemented byte-range chunk streaming that eliminated buffering stutter on spotty 3G/4G cellular connections across Africa.\", \"Real-Time Playhead Sync: Engineered Firebase real-time synchronization so users seamlessly switch between mobile apps and web players with zero loss of bookmark position.\"]', 'Users experienced high audio buffering latency and poor personalized discovery, leading to high drop-off rates within the first 5 minutes of listening.', 'Engineered an adaptive byte-range audio streaming pipeline paired with a collaborative-filtering recommendation model, drastically improving retention and discovery.', '[\"img/project/5.jpg\", \"img/project/6.jpg\", \"img/project/2.jpg\", \"img/project/3.jpg\"]', 'https://omaaudio.com', '2026-10-04 20:54:14');
INSERT INTO `projects` (`id`, `slug`, `title`, `subtitle`, `role`, `client`, `date_period`, `featured`, `category_filter`, `category_name`, `categories`, `summary`, `image`, `image_large`, `metrics`, `stack`, `architectural_highlights`, `problem_statement`, `solution`, `gallery`, `live_url`, `created_at`) VALUES (6, 'quantum-pay-escrow', 'QuantumPay: Real-Time B2B Multi-Currency Escrow Platform', 'Instantaneous Settlement with Cryptographic Verification', 'Chief Architect & Technical Director', 'QuantumPay Global', '2026', 1, 'fintech', 'FinTech Infrastructure', '[\"FinTech\", \"Cloud Architecture\", \"Escrow Systems\"]', 'Engineered an institutional-grade B2B payments orchestrator with real-time settlement.', 'img/portfolio/item-1.png', 'img/portfolio/item-1.png', '[{\"label\": \"Gross Volume Processed\", \"value\": \"$85M+\"}, {\"label\": \"Settlement Accuracy\", \"value\": \"99.99%\"}]', '[\"PHP 8.3\", \"Laravel 11\", \"Redis Cluster\", \"PostgreSQL\", \"AWS ECS\"]', '[\"Idempotent transactional pipeline with distributed Redis locking.\", \"Zero-trust cryptographic tokenization for banking partner webhooks.\"]', 'Previous gateway experienced double-spend vulnerabilities under network partitions.', 'Architected a distributed two-phase commit escrow pipeline with automated reconciliation.', '[\"img/portfolio/item-1.png\"]', 'https://quantumpay.example.com', '2026-10-04 21:07:32');

-- ---------------------------------------------------------------------------
-- Table: services
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tagline` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bg_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `deliverables` json DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_sort` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data for services (8 rows)
INSERT INTO `services` (`id`, `slug`, `title`, `tagline`, `bg_color`, `icon`, `summary`, `description`, `deliverables`, `sort_order`, `created_at`, `updated_at`) VALUES (1, 'full-stack-saas', 'Full-Stack SaaS & Web Platforms', 'High-Availability Architectures Built for Enterprise Scale', '#edf4b3', 'img/services/1.png', 'Engineering resilient, scalable SaaS applications using modern PHP (Laravel 11), Node.js (NestJS), TypeScript, and React/Next.js.', 'From initial schema modeling to production deployment, I architect custom web applications designed for multi-tenant scalability, strict role-based access control (RBAC), and bulletproof API contracts.', '[\"Microservices and modular monolith architecture\", \"Comprehensive RESTful and GraphQL API design\", \"Strict automated testing suites (PHPUnit, Jest)\", \"Responsive, pixel-perfect frontend interfaces\"]', 10, '2026-10-10 19:28:04', '2026-10-10 19:28:04');
INSERT INTO `services` (`id`, `slug`, `title`, `tagline`, `bg_color`, `icon`, `summary`, `description`, `deliverables`, `sort_order`, `created_at`, `updated_at`) VALUES (2, 'fintech-escrow', 'FinTech, Escrow & Payment Systems', 'ACID-Compliant Ledgers & Zero-Trust Escrow Engines', '#ffd5df', 'img/services/2.png', 'Designing double-entry accounting engines, secure wallet custody, idempotent webhook listeners, and zero-race-condition payment flows.', 'Eliminate balance drift and financial discrepancy risks with mathematical certainty. I build transactional systems with PostgreSQL double-entry bookkeeping, distributed Redis locks, and PCI-DSS-aligned security.', '[\"ACID double-entry ledger implementation\", \"Automated multi-party escrow custody workflows\", \"Idempotent payment webhook orchestration\", \"Regulatory audit trail logging and fraud checks\"]', 20, '2026-10-10 19:28:04', '2026-10-10 19:28:04');
INSERT INTO `services` (`id`, `slug`, `title`, `tagline`, `bg_color`, `icon`, `summary`, `description`, `deliverables`, `sort_order`, `created_at`, `updated_at`) VALUES (3, 'ai-rag-pipelines', 'AI & Autonomous Agent Pipelines', 'Retrieval-Augmented Generation (RAG) & Intelligent Workflows', '#d5f5ff', 'img/services/3.png', 'Constructing enterprise-grade RAG systems, autonomous agent workflows, LangChain orchestration, and multi-model LLM benchmarking.', 'Supercharge corporate knowledge workflows with customized AI automation. I build vector search pipelines, prompt synthesis agents, and real-time inference streaming with OpenAI, Google Gemini, and Anthropic APIs.', '[\"Enterprise document ingestion and vector embeddings\", \"Contextual RAG with hybrid search (dense + sparse)\", \"Autonomous multi-step agents and tool calling\", \"Evaluation benchmarks and hallucination guardrails\"]', 30, '2026-10-10 19:28:04', '2026-10-10 19:28:04');
INSERT INTO `services` (`id`, `slug`, `title`, `tagline`, `bg_color`, `icon`, `summary`, `description`, `deliverables`, `sort_order`, `created_at`, `updated_at`) VALUES (4, 'technical-oversight', 'Client-Side Oversight & Vendor Auditing', 'The Non-Technical Founder’s Executive Engineering Watchdog', '#d6ffd5', 'img/services/4.png', 'Auditing external software agencies, reviewing code repositories, and gatekeeping milestone approvals before final payouts.', 'Never let an outsourced development shop deliver bloated, unmaintainable, or insecure code again. Acting as your executive technical proxy, I scrutinize pull requests, audit architectural decisions, and verify true Definition of Done (DoD).', '[\"Independent code quality and security vulnerability audits\", \"Milestone verification before investor or client payouts\", \"Sprint facilitation and technical vendor management\", \"Tech stack evaluation and architecture roadmaps\"]', 40, '2026-10-10 19:28:04', '2026-10-10 19:28:04');
INSERT INTO `services` (`id`, `slug`, `title`, `tagline`, `bg_color`, `icon`, `summary`, `description`, `deliverables`, `sort_order`, `created_at`, `updated_at`) VALUES (5, 'cloud-serverless', 'Cloud Infrastructure & AWS Serverless', 'Slashing Infrastructure Costs While Guaranteeing 99.9% Uptime', '#e1e2ff', 'img/services/5.png', 'Architecting AWS serverless setups (Lambda, API Gateway, S3, DynamoDB, CloudFront), Docker containerization, and CI/CD pipelines.', 'Cut massive monthly hosting bills without sacrificing throughput or reliability. I specialize in migrating resource-heavy monoliths to event-driven serverless platforms that scale down to zero when idle.', '[\"AWS Serverless migration (Lambda, RDS, DynamoDB)\", \"Automated GitHub Actions CI/CD pipelines\", \"Redis clustering and CDN cache optimization\", \"Cloud security hardening and DDoS mitigation\"]', 50, '2026-10-10 19:28:04', '2026-10-10 19:28:04');
INSERT INTO `services` (`id`, `slug`, `title`, `tagline`, `bg_color`, `icon`, `summary`, `description`, `deliverables`, `sort_order`, `created_at`, `updated_at`) VALUES (6, 'database-tuning', 'Database Profiling & Latency Optimization', 'Cutting Query Latency by Up to 50% Through Precision Engineering', '#c6fbf4', 'img/services/6.png', 'Optimizing PostgreSQL and MySQL schemas, index clustering, slow query refactoring, and multi-tier Redis caching.', 'Slow databases destroy user experiences and checkout conversions. I profile execution plans, eliminate N+1 query traps, construct composite indices, and introduce distributed in-memory cache strategies.', '[\"EXPLAIN ANALYZE profiling of problematic queries\", \"Composite indexing and table partitioning\", \"In-memory Redis caching and invalidation schemes\", \"Connection pooling and replica read/write splitting\"]', 60, '2026-10-10 19:28:04', '2026-10-10 19:28:04');
INSERT INTO `services` (`id`, `slug`, `title`, `tagline`, `bg_color`, `icon`, `summary`, `description`, `deliverables`, `sort_order`, `created_at`, `updated_at`) VALUES (7, 'mobile-engineering', 'Cross-Platform Mobile Engineering', 'High-Performance Apps in Flutter & React Native', '#ffe5d5', 'img/services/7.png', 'Building responsive iOS & Android mobile applications in Flutter (Dart) and React Native with native Java/Kotlin modules.', 'Deliver fluid 60fps mobile experiences with unified codebases. I build native device bridges, offline-first SQLite sync, push notification pipelines, and biometric security.', '[\"Flutter and React Native cross-platform apps\", \"Offline storage and background synchronization\", \"Native camera, biometric, and payment gateway bridges\", \"App Store and Google Play publishing and CI/CD\"]', 70, '2026-10-10 19:28:04', '2026-10-10 19:28:04');
INSERT INTO `services` (`id`, `slug`, `title`, `tagline`, `bg_color`, `icon`, `summary`, `description`, `deliverables`, `sort_order`, `created_at`, `updated_at`) VALUES (8, 'api-microservices', 'API Architecture & Microservices', 'Decoupled, Versioned, and Scalable Backend Networks', '#fde4ff', 'img/services/8.png', 'Constructing robust, well-documented RESTful and event-driven microservices with OpenAPI/Swagger specifications.', 'Ensure seamless interoperability across mobile apps, frontend clients, and third-party partners with strictly typed and rate-limited API gateways.', '[\"RESTful and event-driven microservices design\", \"OpenAPI/Swagger interactive documentation\", \"JWT, OAuth2, and API Key authentication schemes\", \"Granular rate limiting and distributed request logging\"]', 80, '2026-10-10 19:28:04', '2026-10-10 19:28:04');

-- ---------------------------------------------------------------------------
-- Table: testimonials
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE `testimonials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data for testimonials (3 rows)
INSERT INTO `testimonials` (`id`, `name`, `role`, `company`, `image`, `content`, `sort_order`, `created_at`, `updated_at`) VALUES (1, 'Chinedu Eze', 'Managing Director', 'Jotah Ecom NG Ltd', 'assets/img/testimonials/1.jpg', 'Jamachi engineered our core escrow and multi-vendor ledger from the ground up. His double-entry database design eliminated balance calculation discrepancies completely. He is a rare hybrid who possesses elite software engineering skills while thinking like a founder.', 10, '2026-10-10 19:28:04', '2026-10-10 19:28:04');
INSERT INTO `testimonials` (`id`, `name`, `role`, `company`, `image`, `content`, `sort_order`, `created_at`, `updated_at`) VALUES (2, 'Adebayo Ogunlesi', 'Head of Product', 'FusionHub AI Systems', 'assets/img/testimonials/2.jpg', 'Migrating our AI platform to AWS Serverless under Jamachi’s architecture reduced our hosting overhead by 35% while keeping our uptime at 99.9%. His mastery of RAG pipelines and model benchmarking set our engineering standards.', 20, '2026-10-10 19:28:04', '2026-10-10 19:28:04');
INSERT INTO `testimonials` (`id`, `name`, `role`, `company`, `image`, `content`, `sort_order`, `created_at`, `updated_at`) VALUES (3, 'Dr. Amina Bello', 'Chief Medical Officer', 'Doccure Health Tech', 'assets/img/testimonials/3.jpg', 'By refactoring our database indexing and caching layers, Jamachi cut consultation query latency in half. His attention to HIPAA data privacy, encrypted diagnostic reports, and practitioner signatures was extraordinary.', 30, '2026-10-10 19:28:04', '2026-10-10 19:28:04');

-- ---------------------------------------------------------------------------
-- Table: skills
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `skills`;
CREATE TABLE `skills` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage` tinyint unsigned NOT NULL DEFAULT '0',
  `summary` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `badges` json DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_sort` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data for skills (7 rows)
INSERT INTO `skills` (`id`, `slug`, `category`, `percentage`, `summary`, `badges`, `sort_order`, `created_at`, `updated_at`) VALUES (1, 'backend', 'Backend & Distributed Systems', 95, 'Modern PHP 8.2+, Laravel 11, Node.js (NestJS, Express), TypeScript, RESTful API Design, Microservices, Domain-Driven Design (DDD), Double-Entry Accounting Systems.', '[\"PHP 8.2+\", \"Laravel 11\", \"Node.js\", \"NestJS\", \"Express\", \"TypeScript\", \"DDD\", \"Microservices\", \"RESTful API\"]', 10, '2026-10-10 19:28:04', '2026-10-10 19:28:04');
INSERT INTO `skills` (`id`, `slug`, `category`, `percentage`, `summary`, `badges`, `sort_order`, `created_at`, `updated_at`) VALUES (2, 'databases', 'Databases & In-Memory Caching', 94, 'PostgreSQL, MySQL, MongoDB, Redis (In-memory caching, distributed locking states SET NX EX, session persistence, index clustering).', '[\"PostgreSQL\", \"MySQL\", \"Redis\", \"MongoDB\", \"ACID Transactions\", \"Distributed Locks\", \"Query Optimization\"]', 20, '2026-10-10 19:28:04', '2026-10-10 19:28:04');
INSERT INTO `skills` (`id`, `slug`, `category`, `percentage`, `summary`, `badges`, `sort_order`, `created_at`, `updated_at`) VALUES (3, 'ai_ml', 'AI & Autonomous Agent Systems', 90, 'Retrieval-Augmented Generation (RAG) Pipelines, Autonomous Agent Workflows, Prompt Engineering, Model Benchmarking / RLHF Evaluation (Gemini, OpenAI, DeepSeek), LangChain.', '[\"RAG Pipelines\", \"LangChain\", \"OpenAI API\", \"Google Gemini\", \"Vector Databases\", \"Autonomous Agents\"]', 30, '2026-10-10 19:28:04', '2026-10-10 19:28:04');
INSERT INTO `skills` (`id`, `slug`, `category`, `percentage`, `summary`, `badges`, `sort_order`, `created_at`, `updated_at`) VALUES (4, 'cloud_devops', 'Cloud Infrastructure & DevOps', 88, 'AWS (Lambda, API Gateway, S3, RDS, DynamoDB, CloudFront), Docker containerization, GitHub Actions CI/CD pipelines, Cloudflare edge security.', '[\"AWS Serverless\", \"AWS Lambda\", \"Amazon S3\", \"Docker\", \"GitHub Actions\", \"CloudFront\", \"CI/CD\"]', 40, '2026-10-10 19:28:04', '2026-10-10 19:28:04');
INSERT INTO `skills` (`id`, `slug`, `category`, `percentage`, `summary`, `badges`, `sort_order`, `created_at`, `updated_at`) VALUES (5, 'frontend', 'Frontend Engineering & UI/UX', 92, 'HTML5, Modern Semantic CSS, Tailwind CSS, JavaScript (ESNext), React, Next.js, Responsive Cross-Browser UI Systems.', '[\"React\", \"Next.js\", \"Tailwind CSS\", \"JavaScript (ESNext)\", \"HTML5 / CSS3\", \"UI/UX Architecture\"]', 50, '2026-10-10 19:28:04', '2026-10-10 19:28:04');
INSERT INTO `skills` (`id`, `slug`, `category`, `percentage`, `summary`, `badges`, `sort_order`, `created_at`, `updated_at`) VALUES (6, 'mobile', 'Cross-Platform Mobile Engineering', 86, 'Cross-platform mobile apps in Flutter (Dart) and React Native; native Android modules in Java and Kotlin.', '[\"Flutter (Dart)\", \"React Native\", \"Java\", \"Kotlin\", \"Mobile APIs\", \"Offline-First Sync\"]', 60, '2026-10-10 19:28:04', '2026-10-10 19:28:04');
INSERT INTO `skills` (`id`, `slug`, `category`, `percentage`, `summary`, `badges`, `sort_order`, `created_at`, `updated_at`) VALUES (7, 'governance', 'Technical Oversight & Governance', 93, 'Client-Side Oversight for Non-Technical Founders, Technical Vendor Auditing, Agile/Scrum Sprint Facilitation (PIM Certified), Definition of Done (DoD) Gatekeeping.', '[\"Vendor Code Auditing\", \"Fractional CTO\", \"Agile / Scrum\", \"PIM Certified\", \"Definition of Done\", \"Security Audits\"]', 70, '2026-10-10 19:28:04', '2026-10-10 19:28:04');

-- ---------------------------------------------------------------------------
-- Table: indexnow_logs
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `indexnow_logs`;
CREATE TABLE `indexnow_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key_location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `urls` mediumtext COLLATE utf8mb4_unicode_ci,
  `http_status` smallint DEFAULT NULL,
  `response_body` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_submitted_at` (`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Table: admin_users
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('owner','editor') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'owner',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data for admin_users (1 rows)
INSERT INTO `admin_users` (`id`, `username`, `password_hash`, `display_name`, `email`, `role`, `last_login_at`, `created_at`) VALUES (1, 'admin', '$2y$12$qsfCkknqZFvj9HfpXnks2O12LnAo7cZag1WeeeuTCiJTvzircF1.i', 'Site Owner', 'contact@morexweb.com', 'owner', '2026-10-10 20:20:45', '2026-10-10 17:26:45');

SET FOREIGN_KEY_CHECKS = 1;

