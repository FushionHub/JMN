<?php
declare(strict_types=1);


/**
 * Architectural Essays & Engineering Articles Registry
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Reads from MySQL Database when available, with resilient fallback array.
 */

require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/cache.php';

/**
 * Seed articles into MySQL if table is empty
 */
function seed_articles_if_needed(PDO $db, array $fallbackArticles): void
{
    try {
        $count = (int)$db->query("SELECT COUNT(*) FROM `blogs`")->fetchColumn();
        if ($count === 0) {
            $stmt = $db->prepare("
                INSERT INTO `blogs` (
                    `slug`, `title`, `subtitle`, `category`, `read_time`,
                    `image`, `excerpt`, `content`, `status`, `published_at`
                ) VALUES (
                    :slug, :title, :subtitle, :category, :read_time,
                    :image, :excerpt, :content, 'published', NOW()
                )
            ");

            foreach ($fallbackArticles as $art) {
                $stmt->execute([
                    ':slug'      => $art['slug'],
                    ':title'     => $art['title'],
                    ':subtitle'  => $art['subtitle'] ?? '',
                    ':category'  => $art['category'],
                    ':read_time' => $art['read_time'] ?? '5 min read',
                    ':image'     => $art['image'],
                    ':excerpt'   => $art['excerpt'],
                    ':content'   => $art['content'],
                ]);
            }
        }
    } catch (Throwable $e) {
        error_log("Failed to seed articles: " . $e->getMessage());
    }
}

/**
 * Add a new blog article to MySQL Database
 */
function add_blog_to_db(array $data): bool
{
    $db = get_db();
    if ($db === null) {
        return false;
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO `blogs` (
                `slug`, `title`, `subtitle`, `category`, `read_time`,
                `image`, `excerpt`, `content`, `status`, `published_at`
            ) VALUES (
                :slug, :title, :subtitle, :category, :read_time,
                :image, :excerpt, :content, :status, NOW()
            )
            ON DUPLICATE KEY UPDATE
                `title` = VALUES(`title`),
                `subtitle` = VALUES(`subtitle`),
                `category` = VALUES(`category`),
                `read_time` = VALUES(`read_time`),
                `image` = VALUES(`image`),
                `excerpt` = VALUES(`excerpt`),
                `content` = VALUES(`content`),
                `status` = VALUES(`status`)
        ");

        return $stmt->execute([
            ':slug'      => $data['slug'],
            ':title'     => $data['title'],
            ':subtitle'  => $data['subtitle'] ?? '',
            ':category'  => $data['category'] ?? 'Engineering',
            ':read_time' => $data['read_time'] ?? '5 min read',
            ':image'     => $data['image'] ?? 'assets/img/blog/1.jpg',
            ':excerpt'   => $data['excerpt'],
            ':content'   => $data['content'],
            ':status'    => $data['status'] ?? 'published',
        ]);
    } catch (Throwable $e) {
        error_log("Failed to insert blog: " . $e->getMessage());
        return false;
    }
}

/**
 * Primary article data store with MySQL synchronization
 */
/**
 * Cached wrapper for the article list (file cache + in-request static).
 * Derived getters (by-slug, recent) reuse this and are cached automatically.
 * Admin blog saves call cache_clear() to invalidate.
 */
function get_all_articles(): array
{
    static $memArticles = null;
    if ($memArticles !== null) {
        return $memArticles;
    }
    $hit = cache_get('articles_all');
    if (is_array($hit)) {
        $memArticles = $hit;
        return $memArticles;
    }
    $memArticles = fetch_all_articles_uncached();
    cache_set('articles_all', $memArticles);
    return $memArticles;
}

function fetch_all_articles_uncached(): array
{
    static $cachedArticles = null;
    if ($cachedArticles !== null) {
        return $cachedArticles;
    }

    $fallback = [
        'double-entry-ledger-architecture' => [
            'slug'      => 'double-entry-ledger-architecture',
            'title'     => 'Architecting ACID-Compliant Double-Entry Ledgers in PostgreSQL',
            'subtitle'  => 'Eliminating balance discrepancies and race conditions in high-volume FinTech escrow marketplaces.',
            'category'  => 'FinTech Architecture',
            'date'      => 'October 12, 2024',
            'read_time' => '7 min read',
            'image'     => 'assets/img/blog/1.jpg',
            'excerpt'   => 'How to design a mathematical double-entry virtual ledger using PostgreSQL and distributed Redis locks to guarantee 100% financial transaction integrity.',
            'content'   => '
                <p class="mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]">
                    In multi-vendor e-commerce and peer-to-peer escrow platforms, relying on a naive <code>users.balance</code> column updated with standard arithmetic is an existential architectural risk. Under concurrent traffic drops, race conditions and partial failures invariably produce phantom balances, lost funds, and agonizing reconciliation disputes.
                </p>
                <h4 class="font-montserrat text-[22px] font-bold text-[#111a24] mt-[24px] mb-[12px] leading-[1.3]">The Principle of Zero-Sum Balancing</h4>
                <p class="mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]">
                    Double-entry bookkeeping dictates that every single transaction consists of at least two balanced entries: a debit and a credit. The sum of all debits must equal the sum of all credits at all times. By enforcing this at the schema layer with database constraints and immutable transaction logs, modifying balance state without an explicit audit trail becomes mathematically impossible.
                </p>
                <div class="my-[20px] p-[20px] bg-[#111a24] text-[#fff] rounded-[15px] font-mono text-[13px] overflow-x-auto leading-[22px]">
                    -- Simplified PostgreSQL Ledger Transaction<br>
                    BEGIN TRANSACTION ISOLATION LEVEL SERIALIZABLE;<br><br>
                    INSERT INTO ledger_entries (account_id, entry_type, amount, currency, transaction_ref)<br>
                    VALUES <br>
                    &nbsp;&nbsp;(:buyer_wallet, \'DEBIT\', 25000.00, \'NGN\', :tx_hash),<br>
                    &nbsp;&nbsp;(:escrow_vault,  \'CREDIT\', 25000.00, \'NGN\', :tx_hash);<br><br>
                    COMMIT;
                </div>
                <h4 class="font-montserrat text-[22px] font-bold text-[#111a24] mt-[24px] mb-[12px] leading-[1.3]">Distributed Redis Locking for Atomic Inventory</h4>
                <p class="mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]">
                    Beyond ledger consistency, preventing inventory overselling requires coordinated distributed locks. By utilizing Redis <code>SET resource_name my_random_value NX PX 30000</code> alongside atomic SQL conditional updates (<code>UPDATE products SET stock = stock - :qty WHERE id = :id AND stock >= :qty</code>), checkout drops achieve deterministic thread-safe execution with zero order collisions.
                </p>
            '
        ],

        'rag-pipelines-enterprise' => [
            'slug'      => 'rag-pipelines-enterprise',
            'title'     => 'Building High-Precision Enterprise RAG Pipelines with LangChain & Google Gemini',
            'subtitle'  => 'Overcoming hallucinations and contextual latency in production generative AI applications.',
            'category'  => 'AI & Autonomous Agents',
            'date'      => 'November 03, 2024',
            'read_time' => '6 min read',
            'image'     => 'assets/img/blog/2.jpg',
            'excerpt'   => 'A deep dive into chunking strategies, hybrid vector retrieval, and reranking pipelines to deliver reliable autonomous AI agents for corporate workflows.',
            'content'   => '
                <p class="mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]">
                    While prototyping an LLM wrapper requires fewer than 50 lines of code, deploying a production Retrieval-Augmented Generation (RAG) system that corporate clients can trust demands surgical engineering around document chunking, semantic overlap, and vector similarity thresholds.
                </p>
                <h4 class="font-montserrat text-[22px] font-bold text-[#111a24] mt-[24px] mb-[12px] leading-[1.3]">The Triad of Precision Retrieval</h4>
                <p class="mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]">
                    Dense vector embeddings alone often miss domain-specific identifiers, SKU numbers, or exact legal clauses. In FusionHub AI CMS, we implemented hybrid search: combining BM25 keyword matching with dense cosine distance embeddings, then passing the top 20 candidate passages through a cross-encoder reranker before feeding them into Google Gemini Pro’s context window.
                </p>
                <div class="my-[20px] p-[20px] bg-[#f7f5fb] border-l-[4px] border-[#f41a4a] rounded-[10px]">
                    <p class="m-[0] font-montserrat text-[14px] italic text-[#111a24] leading-[24px]">
                        “A RAG system is only as intelligent as the relevance of its retrieved context. Feeding noisy tokens to frontier models doesn’t improve comprehension—it accelerates hallucination.”
                    </p>
                </div>
            '
        ],

        'cutting-cloud-costs-serverless' => [
            'slug'      => 'cutting-cloud-costs-serverless',
            'title'     => 'Slashing Infrastructure Overhead by 35% with AWS Serverless Architecture',
            'subtitle'  => 'How we migrated containerized monoliths to event-driven serverless while preserving 99.9% uptime.',
            'category'  => 'Cloud Engineering',
            'date'      => 'December 18, 2024',
            'read_time' => '5 min read',
            'image'     => 'assets/img/blog/3.jpg',
            'excerpt'   => 'Practical techniques for replacing expensive idle EC2 compute instances with AWS Lambda, DynamoDB on-demand, and CloudFront edge caching.',
            'content'   => '
                <p class="mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]">
                    Many startups and growing enterprises pay thousands of dollars each month for idle cloud capacity. Servers provisioned for peak traffic sit at 4% CPU utilization during overnight lulls, bleeding financial runway.
                </p>
                <h4 class="font-montserrat text-[22px] font-bold text-[#111a24] mt-[24px] mb-[12px] leading-[1.3]">The Serverless Shift</h4>
                <p class="mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]">
                    By decomposing synchronous batch jobs and monolithic endpoints into event-driven AWS Lambda functions fronted by API Gateway and cached aggressively via CloudFront, we slashed compute overhead by 35% across our production deployments. When zero requests arrive, compute cost drops to exactly zero.
                </p>
            '
        ],

        'vendor-code-auditing-guide' => [
            'slug'      => 'vendor-code-auditing-guide',
            'title'     => 'The Non-Technical Founder’s Guide to Auditing Agency Codebases',
            'subtitle'  => 'Critical checkpoints to verify before signing off on software agency deliverables and releasing final milestones.',
            'category'  => 'Technical Oversight',
            'date'      => 'January 15, 2025',
            'read_time' => '8 min read',
            'image'     => 'assets/img/blog/4.jpg',
            'excerpt'   => 'How non-technical founders can prevent agency scope fraud, identify technical debt early, and enforce strict Definition of Done gatekeeping.',
            'content'   => '
                <p class="mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]">
                    Every month, non-technical founders hand over millions to outsourced software development agencies, only to receive brittle prototypes held together by hardcoded secrets, duplicate dependencies, and unindexed database queries that collapse the moment real users sign up.
                </p>
                <h4 class="font-montserrat text-[22px] font-bold text-[#111a24] mt-[24px] mb-[12px] leading-[1.3]">The 5-Point Milestone Audit Checklist</h4>
                <p class="mb-[16px] font-montserrat text-[15px] text-[#495461] leading-[28px] tracking-[0.03rem]">
                    Before approving any deliverable invoice, verify: (1) Source code ownership and clean Git commit histories, (2) Automated unit and integration test coverage above 80%, (3) Complete absence of hardcoded API keys and credentials, (4) Index coverage on foreign key columns, and (5) Stress-tested deployment documentation that compiles cleanly on a fresh container.
                </p>
            '
        ],
    ];

    $db = get_db();
    if ($db === null) {
        $cachedArticles = $fallback;
        return $cachedArticles;
    }

    try {
        // Auto-seed if empty
        seed_articles_if_needed($db, $fallback);

        $stmt = $db->query("SELECT * FROM `blogs` WHERE `status` = 'published' ORDER BY `published_at` DESC");
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            $cachedArticles = $fallback;
            return $cachedArticles;
        }

        $articles = [];
        foreach ($rows as $row) {
            $slug = $row['slug'];
            $articles[$slug] = [
                'slug'      => $row['slug'],
                'title'     => $row['title'],
                'subtitle'  => $row['subtitle'] ?? '',
                'category'  => $row['category'],
                'date'      => date('F d, Y', strtotime($row['published_at'] ?? $row['created_at'])),
                'read_time' => $row['read_time'] ?? '5 min read',
                'image'     => $row['image'],
                'excerpt'   => $row['excerpt'],
                'content'   => $row['content'],
            ];
        }

        $cachedArticles = $articles;
        return $cachedArticles;
    } catch (Throwable $e) {
        error_log("Failed to query blogs from database: " . $e->getMessage());
        $cachedArticles = $fallback;
        return $cachedArticles;
    }
}

function get_article_by_slug(string $slug): ?array
{
    $articles = get_all_articles();
    return $articles[$slug] ?? null;
}

function get_recent_articles(int $limit = 3): array
{
    $articles = get_all_articles();
    return array_slice($articles, 0, $limit);
}
