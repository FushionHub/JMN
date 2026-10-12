<?php
declare(strict_types=1);

/**
 * Service Catalogue Registry
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Reads from MySQL Database when available, with resilient fallback array.
 */

require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/cache.php';

/**
 * Seed services into MySQL if the table is empty.
 */
function seed_services_if_needed(PDO $db, array $fallbackServices): void
{
    try {
        $count = (int)$db->query("SELECT COUNT(*) FROM `services`")->fetchColumn();
        if ($count === 0) {
            $stmt = $db->prepare("
                INSERT INTO `services` (`slug`,`title`,`tagline`,`bg_color`,`icon`,`summary`,`description`,`deliverables`,`sort_order`)
                VALUES (:slug,:title,:tagline,:bg_color,:icon,:summary,:description,:deliverables,:sort_order)
            ");
            $order = 0;
            foreach ($fallbackServices as $svc) {
                $order += 10;
                $stmt->execute([
                    ':slug'         => $svc['id'],
                    ':title'        => $svc['title'],
                    ':tagline'      => $svc['tagline'] ?? '',
                    ':bg_color'     => $svc['bg_color'] ?? '',
                    ':icon'         => $svc['icon'] ?? '',
                    ':summary'      => $svc['summary'],
                    ':description'  => $svc['description'],
                    ':deliverables' => json_encode($svc['deliverables'] ?? [], JSON_UNESCAPED_UNICODE),
                    ':sort_order'   => $order,
                ]);
            }
        }
    } catch (Throwable $e) {
        error_log('Failed to seed services: ' . $e->getMessage());
    }
}

/**
 * Cached wrapper for the service catalogue (file cache + in-request static).
 * Admin service saves call cache_clear() to invalidate.
 */
function get_all_services(): array
{
    static $memServices = null;
    if ($memServices !== null) {
        return $memServices;
    }
    $hit = cache_get('services_all');
    if (is_array($hit)) {
        $memServices = $hit;
        return $memServices;
    }
    $memServices = fetch_all_services_uncached();
    cache_set('services_all', $memServices);
    return $memServices;
}

function fetch_all_services_uncached(): array
{
    static $cachedServices = null;
    if ($cachedServices !== null) {
        return $cachedServices;
    }

    $fallback = [
        'full-stack-saas' => [
            'id'          => 'full-stack-saas',
            'title'       => 'Full-Stack SaaS & Web Platforms',
            'tagline'     => 'High-Availability Architectures Built for Enterprise Scale',
            'bg_color'    => '#edf4b3',
            'icon'        => 'img/services/1.png',
            'summary'     => 'Engineering resilient, scalable SaaS applications using modern PHP (Laravel 11), Node.js (NestJS), TypeScript, and React/Next.js.',
            'description' => 'From initial schema modeling to production deployment, I architect custom web applications designed for multi-tenant scalability, strict role-based access control (RBAC), and bulletproof API contracts.',
            'deliverables' => [
                'Microservices and modular monolith architecture',
                'Comprehensive RESTful and GraphQL API design',
                'Strict automated testing suites (PHPUnit, Jest)',
                'Responsive, pixel-perfect frontend interfaces'
            ]
        ],

        'fintech-escrow' => [
            'id'          => 'fintech-escrow',
            'title'       => 'FinTech, Escrow & Payment Systems',
            'tagline'     => 'ACID-Compliant Ledgers & Zero-Trust Escrow Engines',
            'bg_color'    => '#ffd5df',
            'icon'        => 'img/services/2.png',
            'summary'     => 'Designing double-entry accounting engines, secure wallet custody, idempotent webhook listeners, and zero-race-condition payment flows.',
            'description' => 'Eliminate balance drift and financial discrepancy risks with mathematical certainty. I build transactional systems with PostgreSQL double-entry bookkeeping, distributed Redis locks, and PCI-DSS-aligned security.',
            'deliverables' => [
                'ACID double-entry ledger implementation',
                'Automated multi-party escrow custody workflows',
                'Idempotent payment webhook orchestration',
                'Regulatory audit trail logging and fraud checks'
            ]
        ],

        'ai-rag-pipelines' => [
            'id'          => 'ai-rag-pipelines',
            'title'       => 'AI & Autonomous Agent Pipelines',
            'tagline'     => 'Retrieval-Augmented Generation (RAG) & Intelligent Workflows',
            'bg_color'    => '#d5f5ff',
            'icon'        => 'img/services/3.png',
            'summary'     => 'Constructing enterprise-grade RAG systems, autonomous agent workflows, LangChain orchestration, and multi-model LLM benchmarking.',
            'description' => 'Supercharge corporate knowledge workflows with customized AI automation. I build vector search pipelines, prompt synthesis agents, and real-time inference streaming with OpenAI, Google Gemini, and Anthropic APIs.',
            'deliverables' => [
                'Enterprise document ingestion and vector embeddings',
                'Contextual RAG with hybrid search (dense + sparse)',
                'Autonomous multi-step agents and tool calling',
                'Evaluation benchmarks and hallucination guardrails'
            ]
        ],

        'technical-oversight' => [
            'id'          => 'technical-oversight',
            'title'       => 'Client-Side Oversight & Vendor Auditing',
            'tagline'     => 'The Non-Technical Founder’s Executive Engineering Watchdog',
            'bg_color'    => '#d6ffd5',
            'icon'        => 'img/services/4.png',
            'summary'     => 'Auditing external software agencies, reviewing code repositories, and gatekeeping milestone approvals before final payouts.',
            'description' => 'Never let an outsourced development shop deliver bloated, unmaintainable, or insecure code again. Acting as your executive technical proxy, I scrutinize pull requests, audit architectural decisions, and verify true Definition of Done (DoD).',
            'deliverables' => [
                'Independent code quality and security vulnerability audits',
                'Milestone verification before investor or client payouts',
                'Sprint facilitation and technical vendor management',
                'Tech stack evaluation and architecture roadmaps'
            ]
        ],

        'cloud-serverless' => [
            'id'          => 'cloud-serverless',
            'title'       => 'Cloud Infrastructure & AWS Serverless',
            'tagline'     => 'Slashing Infrastructure Costs While Guaranteeing 99.9% Uptime',
            'bg_color'    => '#e1e2ff',
            'icon'        => 'img/services/5.png',
            'summary'     => 'Architecting AWS serverless setups (Lambda, API Gateway, S3, DynamoDB, CloudFront), Docker containerization, and CI/CD pipelines.',
            'description' => 'Cut massive monthly hosting bills without sacrificing throughput or reliability. I specialize in migrating resource-heavy monoliths to event-driven serverless platforms that scale down to zero when idle.',
            'deliverables' => [
                'AWS Serverless migration (Lambda, RDS, DynamoDB)',
                'Automated GitHub Actions CI/CD pipelines',
                'Redis clustering and CDN cache optimization',
                'Cloud security hardening and DDoS mitigation'
            ]
        ],

        'database-tuning' => [
            'id'          => 'database-tuning',
            'title'       => 'Database Profiling & Latency Optimization',
            'tagline'     => 'Cutting Query Latency by Up to 50% Through Precision Engineering',
            'bg_color'    => '#c6fbf4',
            'icon'        => 'img/services/6.png',
            'summary'     => 'Optimizing PostgreSQL and MySQL schemas, index clustering, slow query refactoring, and multi-tier Redis caching.',
            'description' => 'Slow databases destroy user experiences and checkout conversions. I profile execution plans, eliminate N+1 query traps, construct composite indices, and introduce distributed in-memory cache strategies.',
            'deliverables' => [
                'EXPLAIN ANALYZE profiling of problematic queries',
                'Composite indexing and table partitioning',
                'In-memory Redis caching and invalidation schemes',
                'Connection pooling and replica read/write splitting'
            ]
        ],

        'mobile-engineering' => [
            'id'          => 'mobile-engineering',
            'title'       => 'Cross-Platform Mobile Engineering',
            'tagline'     => 'High-Performance Apps in Flutter & React Native',
            'bg_color'    => '#ffe5d5',
            'icon'        => 'img/services/7.png',
            'summary'     => 'Building responsive iOS & Android mobile applications in Flutter (Dart) and React Native with native Java/Kotlin modules.',
            'description' => 'Deliver fluid 60fps mobile experiences with unified codebases. I build native device bridges, offline-first SQLite sync, push notification pipelines, and biometric security.',
            'deliverables' => [
                'Flutter and React Native cross-platform apps',
                'Offline storage and background synchronization',
                'Native camera, biometric, and payment gateway bridges',
                'App Store and Google Play publishing and CI/CD'
            ]
        ],

        'api-microservices' => [
            'id'          => 'api-microservices',
            'title'       => 'API Architecture & Microservices',
            'tagline'     => 'Decoupled, Versioned, and Scalable Backend Networks',
            'bg_color'    => '#fde4ff',
            'icon'        => 'img/services/8.png',
            'summary'     => 'Constructing robust, well-documented RESTful and event-driven microservices with OpenAPI/Swagger specifications.',
            'description' => 'Ensure seamless interoperability across mobile apps, frontend clients, and third-party partners with strictly typed and rate-limited API gateways.',
            'deliverables' => [
                'RESTful and event-driven microservices design',
                'OpenAPI/Swagger interactive documentation',
                'JWT, OAuth2, and API Key authentication schemes',
                'Granular rate limiting and distributed request logging'
            ]
        ],
    ];

    $db = get_db();
    if ($db === null) {
        $cachedServices = $fallback;
        return $cachedServices;
    }

    try {
        seed_services_if_needed($db, $fallback);

        $stmt = $db->query("SELECT * FROM `services` ORDER BY `sort_order` ASC, `id` ASC");
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            $cachedServices = $fallback;
            return $cachedServices;
        }

        $services = [];
        foreach ($rows as $row) {
            $id = $row['slug'];
            $services[$id] = [
                'id'          => $row['slug'],
                'title'       => $row['title'],
                'tagline'     => $row['tagline'] ?? '',
                'bg_color'    => $row['bg_color'] ?? '',
                'icon'        => $row['icon'] ?? '',
                'summary'     => $row['summary'],
                'description' => $row['description'],
                'deliverables' => json_decode($row['deliverables'] ?? '[]', true) ?: [],
            ];
        }

        $cachedServices = $services;
        return $cachedServices;
    } catch (Throwable $e) {
        error_log('Failed to query services from database: ' . $e->getMessage());
        $cachedServices = $fallback;
        return $cachedServices;
    }
}

function get_pricing_tiers(): array
{
    return [
        [
            'name'        => 'Architectural Advisory',
            'price'       => '$1,500',
            'period'      => 'per sprint / project',
            'badge'       => 'Consulting & Audit',
            'description' => 'Ideal for founders requiring independent code audits, architectural roadmaps, or agency oversight.',
            'features'    => [
                'Comprehensive Codebase & Architecture Audit',
                'Vendor Deliverable & PR Review Gatekeeping',
                'Database Schema & Latency Optimization Plan',
                'Security Assessment & Vulnerability Report',
                '2 Weekly Executive Advisory Calls',
                'Direct Slack / WhatsApp Priority Access'
            ],
            'cta_text'    => 'Book Advisory'
        ],
        [
            'name'        => 'MVP Sprint',
            'price'       => '$4,500',
            'period'      => 'starting project fee',
            'badge'       => 'Most Popular',
            'description' => 'Complete end-to-end development of your scalable MVP or flagship core platform.',
            'features'    => [
                'Full-Stack Architecture (Laravel / Node / React)',
                'ACID Database & Secure Auth Pipeline',
                'Payment Gateway / Escrow Integration',
                'AWS Serverless / Cloud Hosting Deployment',
                '60 Days Free Post-Launch Warranty Support',
                '100% Full Intellectual Property & Source Code'
            ],
            'cta_text'    => 'Launch Your MVP'
        ],
        [
            'name'        => 'Enterprise Scale & AI',
            'price'       => '$8,500',
            'period'      => 'custom engagement',
            'badge'       => 'Enterprise Grade',
            'description' => 'Tailored for high-scale platforms, AI agent pipelines, and complex FinTech transaction systems.',
            'features'    => [
                'Production RAG & Autonomous Agent Pipelines',
                'High-Concurrency Double-Entry Escrow Ledger',
                'Multi-Region AWS Cloud Infrastructure & Disaster Recovery',
                'Sub-100ms Query Optimization & Redis Cluster',
                'Dedicated 24/7 Production SLA Monitoring',
                'Technical Team Mentorship & Documentation'
            ],
            'cta_text'    => 'Initiate Enterprise Contract'
        ]
    ];
}

function get_service_faqs(): array
{
    return [
        [
            'question' => 'How does your Client-Side Technical Oversight service work?',
            'answer'   => 'I act as your executive technical proxy (Fractional CTO / Technical Watchdog). Before you approve milestones or release payments to external agencies, I review their pull requests, inspect code architecture, verify security best practices, and ensure the delivered software matches genuine business requirements and Definition of Done (DoD).'
        ],
        [
            'question' => 'How do you prevent race conditions during high-volume checkouts?',
            'answer'   => 'I implement distributed Redis locking mechanisms (SET NX EX) with microsecond expiration states, combined with atomic SQL inventory decrement statements (e.g., UPDATE products SET stock = stock - :qty WHERE stock >= :qty). This guarantees that two simultaneous checkouts cannot claim the same stock unit.'
        ],
        [
            'question' => 'What technologies do you use for AI and autonomous agent pipelines?',
            'answer'   => 'I build enterprise RAG pipelines using LangChain, vector databases (Pinecone, pgvector, Qdrant), and top-tier foundational models (OpenAI GPT-4o, Google Gemini Pro). Workflows are equipped with strict hallucination checks, dynamic prompt chunking, and streaming responses.'
        ],
        [
            'question' => 'Can you work with our existing codebase or do you only build from scratch?',
            'answer'   => 'I frequently take over legacy codebases to remediate security flaws, optimize database queries, migrate hosting to cost-effective AWS serverless setups, or untangle monolithic code into maintainable modular services.'
        ],
        [
            'question' => 'What guarantees and post-launch support do you provide?',
            'answer'   => 'All custom development projects include comprehensive automated testing suites, clean documentation, and complimentary warranty support to ensure continuous, worry-free platform stability.'
        ],
        [
            'question' => 'How quickly can an architectural consultation or project kickoff begin?',
            'answer'   => 'Following an initial technical discovery session to review requirements and scope, advisory audits can begin within 48 to 72 hours.'
        ]
    ];
}
