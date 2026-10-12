<?php
declare(strict_types=1);

/**
 * Technical Matrix & Capabilities Registry
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Reads the skills matrix from MySQL when available, with resilient fallback.
 */

require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/cache.php';

/**
 * Seed the skills matrix into MySQL if the table is empty.
 */
function seed_skills_if_needed(PDO $db, array $fallback): void
{
    try {
        $count = (int)$db->query("SELECT COUNT(*) FROM `skills`")->fetchColumn();
        if ($count === 0) {
            $stmt = $db->prepare("
                INSERT INTO `skills` (`slug`,`category`,`percentage`,`summary`,`badges`,`sort_order`)
                VALUES (:slug,:category,:percentage,:summary,:badges,:sort_order)
            ");
            $order = 0;
            foreach ($fallback as $key => $s) {
                $order += 10;
                $stmt->execute([
                    ':slug'       => $key,
                    ':category'   => $s['category'],
                    ':percentage' => (int)$s['percentage'],
                    ':summary'    => $s['summary'],
                    ':badges'     => json_encode($s['badges'] ?? [], JSON_UNESCAPED_UNICODE),
                    ':sort_order' => $order,
                ]);
            }
        }
    } catch (Throwable $e) {
        error_log('Failed to seed skills: ' . $e->getMessage());
    }
}

/**
 * Cached wrapper for the skills matrix (file cache + in-request static).
 * Derived getters (featured) reuse this and are cached automatically.
 * Admin skill saves call cache_clear() to invalidate.
 */
function get_skills_matrix(): array
{
    static $memSkills = null;
    if ($memSkills !== null) {
        return $memSkills;
    }
    $hit = cache_get('skills_matrix');
    if (is_array($hit)) {
        $memSkills = $hit;
        return $memSkills;
    }
    $memSkills = fetch_skills_matrix_uncached();
    cache_set('skills_matrix', $memSkills);
    return $memSkills;
}

function fetch_skills_matrix_uncached(): array
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $fallback = [
        'backend' => [
            'category'    => 'Backend & Distributed Systems',
            'percentage'  => 95,
            'summary'     => 'Modern PHP 8.2+, Laravel 11, Node.js (NestJS, Express), TypeScript, RESTful API Design, Microservices, Domain-Driven Design (DDD), Double-Entry Accounting Systems.',
            'badges'      => ['PHP 8.2+', 'Laravel 11', 'Node.js', 'NestJS', 'Express', 'TypeScript', 'DDD', 'Microservices', 'RESTful API']
        ],
        'databases' => [
            'category'    => 'Databases & In-Memory Caching',
            'percentage'  => 94,
            'summary'     => 'PostgreSQL, MySQL, MongoDB, Redis (In-memory caching, distributed locking states SET NX EX, session persistence, index clustering).',
            'badges'      => ['PostgreSQL', 'MySQL', 'Redis', 'MongoDB', 'ACID Transactions', 'Distributed Locks', 'Query Optimization']
        ],
        'ai_ml' => [
            'category'    => 'AI & Autonomous Agent Systems',
            'percentage'  => 90,
            'summary'     => 'Retrieval-Augmented Generation (RAG) Pipelines, Autonomous Agent Workflows, Prompt Engineering, Model Benchmarking / RLHF Evaluation (Gemini, OpenAI, DeepSeek), LangChain.',
            'badges'      => ['RAG Pipelines', 'LangChain', 'OpenAI API', 'Google Gemini', 'Vector Databases', 'Autonomous Agents']
        ],
        'cloud_devops' => [
            'category'    => 'Cloud Infrastructure & DevOps',
            'percentage'  => 88,
            'summary'     => 'AWS (Lambda, API Gateway, S3, RDS, DynamoDB, CloudFront), Docker containerization, GitHub Actions CI/CD pipelines, Cloudflare edge security.',
            'badges'      => ['AWS Serverless', 'AWS Lambda', 'Amazon S3', 'Docker', 'GitHub Actions', 'CloudFront', 'CI/CD']
        ],
        'frontend' => [
            'category'    => 'Frontend Engineering & UI/UX',
            'percentage'  => 92,
            'summary'     => 'HTML5, Modern Semantic CSS, Tailwind CSS, JavaScript (ESNext), React, Next.js, Responsive Cross-Browser UI Systems.',
            'badges'      => ['React', 'Next.js', 'Tailwind CSS', 'JavaScript (ESNext)', 'HTML5 / CSS3', 'UI/UX Architecture']
        ],
        'mobile' => [
            'category'    => 'Cross-Platform Mobile Engineering',
            'percentage'  => 86,
            'summary'     => 'Cross-platform mobile apps in Flutter (Dart) and React Native; native Android modules in Java and Kotlin.',
            'badges'      => ['Flutter (Dart)', 'React Native', 'Java', 'Kotlin', 'Mobile APIs', 'Offline-First Sync']
        ],
        'governance' => [
            'category'    => 'Technical Oversight & Governance',
            'percentage'  => 93,
            'summary'     => 'Client-Side Oversight for Non-Technical Founders, Technical Vendor Auditing, Agile/Scrum Sprint Facilitation (PIM Certified), Definition of Done (DoD) Gatekeeping.',
            'badges'      => ['Vendor Code Auditing', 'Fractional CTO', 'Agile / Scrum', 'PIM Certified', 'Definition of Done', 'Security Audits']
        ],
    ];

    $db = get_db();
    if ($db === null) {
        $cached = $fallback;
        return $cached;
    }

    try {
        seed_skills_if_needed($db, $fallback);

        $stmt = $db->query("SELECT * FROM `skills` ORDER BY `sort_order` ASC, `id` ASC");
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            $cached = $fallback;
            return $cached;
        }

        $matrix = [];
        foreach ($rows as $row) {
            $matrix[$row['slug']] = [
                'category'   => $row['category'],
                'percentage' => (int)$row['percentage'],
                'summary'    => $row['summary'],
                'badges'     => json_decode($row['badges'] ?? '[]', true) ?: [],
            ];
        }

        $cached = $matrix;
        return $cached;
    } catch (Throwable $e) {
        error_log('Failed to query skills from database: ' . $e->getMessage());
        $cached = $fallback;
        return $cached;
    }
}

function get_featured_skills(): array
{
    return [
        [
            'title'       => 'Backend Systems',
            'sub'         => '(PHP 8.2+, Laravel, Node)',
            'percentage'  => 95,
            'bg_class'    => 'bg-[#faf1c6]',
            'description' => 'Architecting ACID ledgers, microservices, and high-throughput APIs.'
        ],
        [
            'title'       => 'AI & Autonomous Agents',
            'sub'         => '(RAG, LangChain, Gemini)',
            'percentage'  => 90,
            'bg_class'    => 'bg-[#ffd5df]',
            'description' => 'Contextual vector search and intelligent multi-step workflows.'
        ],
        [
            'title'       => 'Cloud & AWS Serverless',
            'sub'         => '(Lambda, Docker, CI/CD)',
            'percentage'  => 88,
            'bg_class'    => 'bg-[#d5f5ff]',
            'description' => 'Cutting infrastructure overhead by 35% with 99.9% uptime.'
        ]
    ];
}
