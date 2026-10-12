<?php
declare(strict_types=1);

/**
 * Flagship Projects & Case Studies Registry
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Reads from MySQL Database when available, with resilient fallback array.
 */

require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/cache.php';

/**
 * Seed projects into MySQL if table is empty
 */
function seed_projects_if_needed(PDO $db, array $fallbackProjects): void
{
    try {
        $count = (int)$db->query("SELECT COUNT(*) FROM `projects`")->fetchColumn();
        if ($count === 0) {
            $stmt = $db->prepare("
                INSERT INTO `projects` (
                    `slug`, `title`, `subtitle`, `role`, `client`, `date_period`,
                    `featured`, `category_filter`, `category_name`, `categories`,
                    `summary`, `image`, `image_large`, `metrics`, `stack`,
                    `architectural_highlights`, `problem_statement`, `solution`, `gallery`, `live_url`
                ) VALUES (
                    :slug, :title, :subtitle, :role, :client, :date_period,
                    :featured, :category_filter, :category_name, :categories,
                    :summary, :image, :image_large, :metrics, :stack,
                    :architectural_highlights, :problem_statement, :solution, :gallery, :live_url
                )
            ");

            foreach ($fallbackProjects as $p) {
                $stmt->execute([
                    ':slug'                     => $p['slug'],
                    ':title'                    => $p['title'],
                    ':subtitle'                 => $p['subtitle'] ?? '',
                    ':role'                     => $p['role'],
                    ':client'                   => $p['client'] ?? '',
                    ':date_period'              => $p['date'] ?? '',
                    ':featured'                 => !empty($p['featured']) ? 1 : 0,
                    ':category_filter'          => $p['category_filter'],
                    ':category_name'            => $p['category_name'],
                    ':categories'               => json_encode($p['categories'] ?? []),
                    ':summary'                  => $p['summary'],
                    ':image'                    => $p['image'],
                    ':image_large'              => $p['image_large'],
                    ':metrics'                  => json_encode($p['metrics'] ?? []),
                    ':stack'                    => json_encode($p['stack'] ?? []),
                    ':architectural_highlights' => json_encode($p['architectural_highlights'] ?? []),
                    ':problem_statement'        => $p['problem_statement'] ?? '',
                    ':solution'                 => $p['solution'] ?? '',
                    ':gallery'                  => json_encode($p['gallery'] ?? []),
                    ':live_url'                 => $p['live_url'] ?? '',
                ]);
            }
        }
    } catch (Throwable $e) {
        error_log("Failed to seed projects: " . $e->getMessage());
    }
}

/**
 * Add a new project to MySQL Database
 */
function add_project_to_db(array $data): bool
{
    $db = get_db();
    if ($db === null) {
        return false;
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO `projects` (
                `slug`, `title`, `subtitle`, `role`, `client`, `date_period`,
                `featured`, `category_filter`, `category_name`, `categories`,
                `summary`, `image`, `image_large`, `metrics`, `stack`,
                `architectural_highlights`, `problem_statement`, `solution`, `gallery`, `live_url`
            ) VALUES (
                :slug, :title, :subtitle, :role, :client, :date_period,
                :featured, :category_filter, :category_name, :categories,
                :summary, :image, :image_large, :metrics, :stack,
                :architectural_highlights, :problem_statement, :solution, :gallery, :live_url
            )
            ON DUPLICATE KEY UPDATE
                `title` = VALUES(`title`),
                `subtitle` = VALUES(`subtitle`),
                `role` = VALUES(`role`),
                `client` = VALUES(`client`),
                `summary` = VALUES(`summary`),
                `metrics` = VALUES(`metrics`),
                `stack` = VALUES(`stack`),
                `architectural_highlights` = VALUES(`architectural_highlights`)
        ");

        return $stmt->execute([
            ':slug'                     => $data['slug'],
            ':title'                    => $data['title'],
            ':subtitle'                 => $data['subtitle'] ?? '',
            ':role'                     => $data['role'] ?? 'Software Architect',
            ':client'                   => $data['client'] ?? '',
            ':date_period'              => $data['date'] ?? date('Y'),
            ':featured'                 => !empty($data['featured']) ? 1 : 0,
            ':category_filter'          => $data['category_filter'] ?? 'web applications',
            ':category_name'            => $data['category_name'] ?? 'Web Engineering',
            ':categories'               => json_encode($data['categories'] ?? ['Web']),
            ':summary'                  => $data['summary'],
            ':image'                    => $data['image'] ?? 'img/project/1.jpg',
            ':image_large'              => $data['image_large'] ?? 'img/project/11.jpg',
            ':metrics'                  => json_encode($data['metrics'] ?? []),
            ':stack'                    => json_encode($data['stack'] ?? []),
            ':architectural_highlights' => json_encode($data['architectural_highlights'] ?? []),
            ':problem_statement'        => $data['problem_statement'] ?? '',
            ':solution'                 => $data['solution'] ?? '',
            ':gallery'                  => json_encode($data['gallery'] ?? []),
            ':live_url'                 => $data['live_url'] ?? '',
        ]);
    } catch (Throwable $e) {
        error_log("Failed to insert project: " . $e->getMessage());
        return false;
    }
}

/**
 * Primary project data store with MySQL synchronization
 */
/**
 * Cached wrapper: serves the project list from the file cache when fresh,
 * otherwise delegates to fetch_all_projects_uncached() and stores the result.
 * All derived getters (by-slug, featured, categories) reuse this, so they are
 * cached automatically. Admin saves call cache_clear() to invalidate.
 */
function get_all_projects(): array
{
    static $memProjects = null;
    if ($memProjects !== null) {
        return $memProjects;
    }
    $hit = cache_get('projects_all');
    if (is_array($hit)) {
        $memProjects = $hit;
        return $memProjects;
    }
    $memProjects = fetch_all_projects_uncached();
    cache_set('projects_all', $memProjects);
    return $memProjects;
}

function fetch_all_projects_uncached(): array
{
    static $cachedProjects = null;
    if ($cachedProjects !== null) {
        return $cachedProjects;
    }

    $fallback = [
        'jotah-ecom' => [
            'slug'            => 'jotah-ecom',
            'title'           => 'JOTAH ECOM NG LTD',
            'subtitle'        => 'FinTech & Escrow Multi-Vendor Marketplace',
            'role'            => 'Founder & Lead Engineer',
            'client'          => 'Jotah Ecom NG Ltd',
            'date'            => '2023 - 2024',
            'featured'        => true,
            'category_filter' => 'web applications fintech',
            'category_name'   => 'FinTech & Escrow',
            'categories'      => ['FinTech', 'E-Commerce', 'Web Platform'],
            'summary'         => 'Nigeria’s premier escrow-integrated multi-vendor marketplace with automated fund custody, double-entry ledger, and distributed Redis locking.',
            'image'           => 'img/project/1.jpg',
            'image_large'     => 'img/project/11.jpg',
            'metrics'         => [
                'Conversion'  => '+18%',
                'Ledger'      => '100% ACID',
                'Concurrency' => 'Zero Race Drops',
                'Status'      => 'Production Ready',
            ],
            'stack'           => [
                'PHP (Laravel 11)', 'Node.js', 'TypeScript', 'PostgreSQL', 'Redis', 'AWS (SQS, S3, CloudFront)', 'Docker'
            ],
            'architectural_highlights' => [
                'ACID Double-Entry Ledger: Designed and deployed a strict virtual ledger schema in PostgreSQL with debit/credit balance pairing to eliminate arbitrary balance modifications and resolve discrepancy risks at the database kernel level.',
                'Concurrency Control & Atomic Inventory: Deployed distributed Redis locking mechanisms (SET NX EX) combined with atomic SQL decrement queries (UPDATE products SET stock = stock - :qty WHERE stock >= :qty) to guarantee zero overselling during rapid flash-sales.',
                'Automated Fund Custody: Engineered an automated multi-party escrow custody engine that holds buyer payments until cryptographic or buyer-authorized delivery confirmations are verified.',
                'Vendor Onboarding Pipeline: Automated asynchronous parsing pipelines for merchant contracts from unstructured PDFs and Word documents, decreasing onboarding verification delays by 65%.',
                'Performance Surge: Elevated overall successful checkout transactions by +18% through resilient asynchronous queue processing via AWS SQS.'
            ],
            'problem_statement' => 'Traditional Nigerian multi-vendor marketplaces suffer from severe merchant mistrust, payment fraud, race conditions during high-demand inventory flash drops, and painful bookkeeping reconciliation gaps. The challenge was building an unalterable financial transactions layer while sustaining high throughput with zero order collision.',
            'solution' => 'Architected an end-to-end cloud platform featuring Laravel 11 micro-services, an isolated double-entry accounting engine in PostgreSQL, distributed Redis locks, and an AWS CloudFront CDN delivery system. Every buyer naira is held in a segregated cryptographic escrow vault until milestone delivery conditions are satisfied.',
            'gallery' => [
                'img/project/1.jpg',
                'img/project/2.jpg',
                'img/project/3.jpg',
                'img/project/4.jpg',
            ],
            'live_url' => 'https://jotahecom.com',
        ],

        'fusionhub-ai' => [
            'slug'            => 'fusionhub-ai',
            'title'           => 'FusionHub AI CMS',
            'subtitle'        => 'Headless AI Automation & Content Engine',
            'role'            => 'Founder & Lead Architect',
            'client'          => 'FusionHub AI Systems',
            'date'            => '2023 - Present',
            'featured'        => true,
            'category_filter' => 'ai applications cloud',
            'category_name'   => 'AI & Automation',
            'categories'      => ['AI & ML', 'Headless CMS', 'Cloud Architecture'],
            'summary'         => 'Intelligent headless CMS automating 80% of routine content drafting, synthesis, and scheduling through real-time RAG pipelines and AWS Serverless.',
            'image'           => 'img/project/2.jpg',
            'image_large'     => 'img/project/22.jpg',
            'metrics'         => [
                'Automation'   => '80% Reduction',
                'Cloud Savings'=> '-35% Overhead',
                'Uptime SLA'   => '99.9%',
                'Latency'      => '< 800ms Stream',
            ],
            'stack'           => [
                'Next.js', 'React', 'Node.js', 'TypeScript', 'LangChain', 'OpenAI API', 'Google Gemini', 'AWS (Lambda, S3, DynamoDB)', 'Tailwind CSS'
            ],
            'architectural_highlights' => [
                'Retrieval-Augmented Generation (RAG): Built resilient vector search pipelines that ingest multi-format corporate knowledge repositories and inject contextual reference frames into dynamic prompts.',
                'AWS Serverless Migration: Transitioned legacy monolithic containerized services to event-driven AWS Lambda, DynamoDB, and Amazon S3, slashing monthly operational cloud costs by 35%.',
                'Reliability & High Availability: Maintained a verified 99.9% uptime SLA across continuous agentic generative tasks for thousands of active multi-tenant users.',
                'Dynamic Semantic Synthesis: Built autonomous agents utilizing OpenAI GPT-4o and Google Gemini Pro for real-time editorial reviews, SEO metadata generation, and cross-platform publishing.'
            ],
            'problem_statement' => 'Editorial and media teams spend hundreds of manual hours every week drafting boilerplate articles, cross-referencing brand guidelines, and formatting structured metadata across fragmented CMS instances, resulting in ballooning editorial costs and delayed time-to-market.',
            'solution' => 'Constructed a modern headless AI platform with high-speed Next.js frontend, Node.js agent microservices, LangChain orchestration, and serverless compute on AWS. Writers upload raw source documents, and the system automatically synthesizes verified multi-channel copy while maintaining full editorial control.',
            'gallery' => [
                'img/project/2.jpg',
                'img/project/3.jpg',
                'img/project/1.jpg',
                'img/project/5.jpg',
            ],
            'live_url' => 'https://fushionhubai.com.ng',
        ],

        'doccure' => [
            'slug'            => 'doccure',
            'title'           => 'Doccure Telemedicine Platform',
            'subtitle'        => 'Healthcare Data & Consultation Platform',
            'role'            => 'Founder & Lead Developer',
            'client'          => 'Doccure Health Tech',
            'date'            => '2023 - 2024',
            'featured'        => true,
            'category_filter' => 'web healthtech applications',
            'category_name'   => 'HealthTech',
            'categories'      => ['HealthTech', 'Telemedicine', 'Data Security'],
            'summary'         => 'HIPAA-compliant telemedicine platform with Redis query acceleration cutting database latency by 50% and secure digital medical report generation.',
            'image'           => 'img/project/3.jpg',
            'image_large'     => 'img/project/33.jpg',
            'metrics'         => [
                'DB Latency'   => '-50% Faster',
                'Security'     => 'HIPAA / TLS 1.3',
                'Signatures'   => 'Cryptographic',
                'Availability' => 'High Concurrency',
            ],
            'stack'           => [
                'PHP (Laravel)', 'TypeScript', 'MySQL / PostgreSQL', 'Redis', 'AWS S3 (KMS Encrypted)', 'Tailwind CSS', 'WebRTC'
            ],
            'architectural_highlights' => [
                'Database Index Clustering: Overhauled the relational database normalization, query profiling, composite index clustering, and Redis key-value caching to cut doctor-patient lookup query latencies by 50%.',
                'HIPAA-Compliant Document Pipeline: Built automated encrypted PDF generation for clinical diagnostic reports featuring validated practitioner cryptographic signatures and tamper-evident audit logs.',
                'Zero-Trust RBAC & Encryption: Enforced granular role-based access control (RBAC), KMS envelope encryption for resting health records on AWS S3, and strict TLS 1.3 in-transit protections.',
                'Real-Time Tele-Consultations: Configured resilient WebRTC signaling and session tokens to support real-time audio/video consultations with automated fallbacks.'
            ],
            'problem_statement' => 'Healthcare portals often suffer from sluggish clinical history lookups, high consultation room latency, and precarious file storage that fails regulatory compliance for patient confidentiality and digital practitioner signatures.',
            'solution' => 'Engineered a high-performance clinical platform utilizing Laravel, Redis caching layers, PostgreSQL composite indices, and an AWS KMS-encrypted document repository. Designed the architecture to satisfy rigorous data privacy mandates while maintaining instantaneous UI responsiveness.',
            'gallery' => [
                'img/project/3.jpg',
                'img/project/4.jpg',
                'img/project/6.jpg',
                'img/project/2.jpg',
            ],
            'live_url' => 'https://doccure.morexweb.com',
        ],

        'sokarlab' => [
            'slug'            => 'sokarlab',
            'title'           => 'SOKARLAB LTD Frameworks',
            'subtitle'        => 'Modular Backend & Distributed Diagnostic Systems',
            'role'            => 'Software Architect',
            'client'          => 'Sokarlab Ltd',
            'date'            => '2022 - 2023',
            'featured'        => false,
            'category_filter' => 'cloud applications web',
            'category_name'   => 'Cloud & Systems',
            'categories'      => ['Software Architecture', 'Modular Frameworks', 'DevOps'],
            'summary'         => 'Reusable modular backend framework that reduced standard boilerplate code by 60%, slashing MVP setup time for enterprise client initiatives.',
            'image'           => 'img/project/4.jpg',
            'image_large'     => 'img/project/44.jpg',
            'metrics'         => [
                'Boilerplate' => '-60% Reduction',
                'MVP Delivery'=> '2x Speed',
                'Monitoring'  => 'Automated Diagnostics',
                'Test Coverage'=> '94%',
            ],
            'stack'           => [
                'TypeScript', 'Node.js', 'PHP 8.2', 'Express', 'PostgreSQL', 'Docker', 'GitHub Actions'
            ],
            'architectural_highlights' => [
                'Reusable Architectural Blueprint: Created a modular scaffolding architecture that decoupled domain business logic from infrastructure adapters, reducing new project initialization code by 60%.',
                'Centralized Health Diagnostics: Built automated error reporting, request tracing, and latency monitoring hooks to alert distributed engineering teams before clients detect service degradations.',
                'Strict CI/CD Pipelines: Enforced automated linting, security scanning, and unit/integration test gates through GitHub Actions before any artifact promotion to staging or production.'
            ],
            'problem_statement' => 'Frequent technical client launches suffered from redundant boilerplate setup, inconsistent error handling, divergent database migration strategies, and scattered logging across different micro-services.',
            'solution' => 'Engineered a unified enterprise boilerplate framework that standardized authentication, database abstraction, logging, and continuous deployment workflows, halving time-to-market across multiple client products.',
            'gallery' => [
                'img/project/4.jpg',
                'img/project/5.jpg',
                'img/project/1.jpg',
                'img/project/6.jpg',
            ],
            'live_url' => 'https://sokarlab.com',
        ],

        'oma-audiobooks' => [
            'slug'            => 'oma-audiobooks',
            'title'           => 'Oma Audiobooks Platform',
            'subtitle'        => 'AI-Driven Audio Streaming & Behavioral Recommendation',
            'role'            => 'Senior Backend Engineer',
            'client'          => 'Oma Media Group',
            'date'            => '2022 - 2023',
            'featured'        => false,
            'category_filter' => 'ai applications',
            'category_name'   => 'AI & Streaming',
            'categories'      => ['AI / ML', 'Audio Streaming', 'Mobile API'],
            'summary'         => 'Integrated a behavioral machine learning recommendation engine that boosted average user session duration by 40% with adaptive bitrate audio delivery.',
            'image'           => 'img/project/5.jpg',
            'image_large'     => 'img/project/55.jpg',
            'metrics'         => [
                'Engagement'  => '+40% Session Time',
                'Throughput'  => '10k+ Concurrents',
                'Latency'     => '< 150ms Chunk Start',
                'Retention'   => '+28% Monthly',
            ],
            'stack'           => [
                'Node.js', 'Firebase', 'Python', 'Machine Learning Algorithms', 'Google Cloud', 'RESTful API'
            ],
            'architectural_highlights' => [
                'Behavioral ML Recommender: Integrated a user listening habit vector model in Python that dynamically recommended chapters, authors, and genres, elevating user engagement duration by 40%.',
                'Optimized Audio Chunk Streaming: Implemented byte-range chunk streaming that eliminated buffering stutter on spotty 3G/4G cellular connections across Africa.',
                'Real-Time Playhead Sync: Engineered Firebase real-time synchronization so users seamlessly switch between mobile apps and web players with zero loss of bookmark position.'
            ],
            'problem_statement' => 'Users experienced high audio buffering latency and poor personalized discovery, leading to high drop-off rates within the first 5 minutes of listening.',
            'solution' => 'Engineered an adaptive byte-range audio streaming pipeline paired with a collaborative-filtering recommendation model, drastically improving retention and discovery.',
            'gallery' => [
                'img/project/5.jpg',
                'img/project/6.jpg',
                'img/project/2.jpg',
                'img/project/3.jpg',
            ],
            'live_url' => 'https://omaaudio.com',
        ],
    ];

    $db = get_db();
    if ($db === null) {
        $cachedProjects = $fallback;
        return $cachedProjects;
    }

    try {
        // Auto-seed if empty
        seed_projects_if_needed($db, $fallback);

        $stmt = $db->query("SELECT * FROM `projects` ORDER BY `id` ASC");
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            $cachedProjects = $fallback;
            return $cachedProjects;
        }

        $projects = [];
        foreach ($rows as $row) {
            $slug = $row['slug'];
            $projects[$slug] = [
                'slug'                     => $row['slug'],
                'title'                    => $row['title'],
                'subtitle'                 => $row['subtitle'] ?? '',
                'role'                     => $row['role'],
                'client'                   => $row['client'] ?? '',
                'date'                     => $row['date_period'] ?? '',
                'featured'                 => (bool)$row['featured'],
                'category_filter'          => $row['category_filter'],
                'category_name'            => $row['category_name'],
                'categories'               => json_decode($row['categories'] ?? '[]', true) ?: [],
                'summary'                  => $row['summary'],
                'image'                    => $row['image'],
                'image_large'              => $row['image_large'],
                'metrics'                  => json_decode($row['metrics'] ?? '[]', true) ?: [],
                'stack'                    => json_decode($row['stack'] ?? '[]', true) ?: [],
                'architectural_highlights' => json_decode($row['architectural_highlights'] ?? '[]', true) ?: [],
                'problem_statement'        => $row['problem_statement'] ?? '',
                'solution'                 => $row['solution'] ?? '',
                'gallery'                  => json_decode($row['gallery'] ?? '[]', true) ?: [],
                'live_url'                 => $row['live_url'] ?? '',
            ];
        }

        $cachedProjects = $projects;
        return $cachedProjects;
    } catch (Throwable $e) {
        error_log("Failed to query projects from database: " . $e->getMessage());
        $cachedProjects = $fallback;
        return $cachedProjects;
    }
}

function get_project_by_slug(string $slug): ?array
{
    $projects = get_all_projects();
    return $projects[$slug] ?? null;
}

function get_featured_projects(int $limit = 3): array
{
    $projects = get_all_projects();
    $featured = array_filter($projects, fn($p) => !empty($p['featured']));
    return array_slice($featured, 0, $limit);
}

function get_project_categories(): array
{
    return [
        'all'          => 'All',
        'fintech'      => 'FinTech & Escrow',
        'ai'           => 'AI & Automation',
        'cloud'        => 'Cloud / DevOps',
        'healthtech'   => 'HealthTech',
        'web'          => 'Web Systems',
        'applications' => 'Applications',
    ];
}
