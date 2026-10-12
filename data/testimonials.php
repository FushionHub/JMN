<?php
declare(strict_types=1);

/**
 * Client Testimonials & Endorsements Registry
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Reads from MySQL Database when available, with resilient fallback array.
 */

require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/cache.php';

/**
 * Seed testimonials into MySQL if the table is empty.
 */
function seed_testimonials_if_needed(PDO $db, array $fallback): void
{
    try {
        $count = (int)$db->query("SELECT COUNT(*) FROM `testimonials`")->fetchColumn();
        if ($count === 0) {
            $stmt = $db->prepare("
                INSERT INTO `testimonials` (`name`,`role`,`company`,`image`,`content`,`sort_order`)
                VALUES (:name,:role,:company,:image,:content,:sort_order)
            ");
            $order = 0;
            foreach ($fallback as $t) {
                $order += 10;
                $stmt->execute([
                    ':name'    => $t['name'],
                    ':role'    => $t['role'] ?? '',
                    ':company' => $t['company'] ?? '',
                    ':image'   => $t['image'] ?? '',
                    ':content' => $t['content'],
                    ':sort_order' => $order,
                ]);
            }
        }
    } catch (Throwable $e) {
        error_log('Failed to seed testimonials: ' . $e->getMessage());
    }
}

/**
 * Cached wrapper for testimonials (file cache + in-request static).
 * Admin testimonial saves call cache_clear() to invalidate.
 */
function get_all_testimonials(): array
{
    static $memTestimonials = null;
    if ($memTestimonials !== null) {
        return $memTestimonials;
    }
    $hit = cache_get('testimonials_all');
    if (is_array($hit)) {
        $memTestimonials = $hit;
        return $memTestimonials;
    }
    $memTestimonials = fetch_all_testimonials_uncached();
    cache_set('testimonials_all', $memTestimonials);
    return $memTestimonials;
}

function fetch_all_testimonials_uncached(): array
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $fallback = [
        [
            'name'     => 'Chinedu Eze',
            'role'     => 'Managing Director',
            'company'  => 'Jotah Ecom NG Ltd',
            'image'    => 'assets/img/testimonials/1.jpg',
            'content'  => 'Jamachi engineered our core escrow and multi-vendor ledger from the ground up. His double-entry database design eliminated balance calculation discrepancies completely. He is a rare hybrid who possesses elite software engineering skills while thinking like a founder.'
        ],
        [
            'name'     => 'Adebayo Ogunlesi',
            'role'     => 'Head of Product',
            'company'  => 'FusionHub AI Systems',
            'image'    => 'assets/img/testimonials/2.jpg',
            'content'  => 'Migrating our AI platform to AWS Serverless under Jamachi’s architecture reduced our hosting overhead by 35% while keeping our uptime at 99.9%. His mastery of RAG pipelines and model benchmarking set our engineering standards.'
        ],
        [
            'name'     => 'Dr. Amina Bello',
            'role'     => 'Chief Medical Officer',
            'company'  => 'Doccure Health Tech',
            'image'    => 'assets/img/testimonials/3.jpg',
            'content'  => 'By refactoring our database indexing and caching layers, Jamachi cut consultation query latency in half. His attention to HIPAA data privacy, encrypted diagnostic reports, and practitioner signatures was extraordinary.'
        ]
    ];

    $db = get_db();
    if ($db === null) {
        $cached = $fallback;
        return $cached;
    }

    try {
        seed_testimonials_if_needed($db, $fallback);

        $stmt = $db->query("SELECT * FROM `testimonials` ORDER BY `sort_order` ASC, `id` ASC");
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            $cached = $fallback;
            return $cached;
        }

        $items = [];
        foreach ($rows as $row) {
            $items[] = [
                'name'    => $row['name'],
                'role'    => $row['role'] ?? '',
                'company' => $row['company'] ?? '',
                'image'   => $row['image'] ?? '',
                'content' => $row['content'],
            ];
        }

        $cached = $items;
        return $cached;
    } catch (Throwable $e) {
        error_log('Failed to query testimonials from database: ' . $e->getMessage());
        $cached = $fallback;
        return $cached;
    }
}
