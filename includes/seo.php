<?php
declare(strict_types=1);

/**
 * SEO / AEO Structured-Data Engine
 * Jamachi Maurice Mauricennadi Portfolio Platform
 *
 * Emits schema.org JSON-LD that mirrors the *visible* content of the page, so
 * rich results (FAQ, Breadcrumb, Article, Person knowledge panel) are earned
 * rather than spammy. Every builder returns an array; header.php serialises
 * them into a single <script type="application/ld+json"> block.
 *
 * Used by:
 *   - header.php        (WebSite, Person, Organization, breadcrumbs)
 *   - services.php      (FAQPage - matches the on-page accordion)
 *   - contact.php       (FAQPage - matches the on-page booking FAQ)
 *   - blog-detail.php   (Article + Breadcrumb)
 *   - single-project.php(CreativeWork + Breadcrumb)
 */

if (!function_exists('seo_jsonld')) {
    /** Serialise one or more schema.org graphs, XSS-safe for inline output. */
    function seo_jsonld(array $blocks): string
    {
        if ($blocks === []) {
            return '';
        }
        $payload = count($blocks) === 1 ? $blocks[0] : ['@context' => 'https://schema.org', '@graph' => array_map(
            static function (array $block): array {
                unset($block['@context']);
                return $block;
            },
            $blocks
        )];
        if (!isset($payload['@context'])) {
            $payload = ['@context' => 'https://schema.org'] + $payload;
        }
        $json = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
        return '<script type="application/ld+json">' . ($json ?: '{}') . '</script>';
    }

    /** The canonical WebSite graph (site-wide). */
    function seo_website_graph(): array
    {
        return [
            '@type'       => 'WebSite',
            '@id'         => url('') . '#website',
            'url'         => url(''),
            'name'        => APP_NAME,
            'alternateName' => 'JMN Portfolio',
            'description' => APP_TAGLINE,
            'inLanguage'  => 'en',
            'publisher'   => ['@id' => url('') . '#organization'],
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => url('blog.php') . '?q={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /** The PERSON knowledge-panel graph for the portfolio owner. */
    function seo_person_graph(): array
    {
        return [
            '@type'       => 'Person',
            '@id'         => url('') . '#person',
            'name'        => PROFILE_NAME,
            'givenName'   => PROFILE_FIRST_NAME,
            'familyName'  => PROFILE_LAST_NAME,
            'jobTitle'    => PROFILE_TITLE,
            'description' => APP_TAGLINE,
            'email'       => 'mailto:' . PROFILE_EMAIL,
            'telephone'   => PROFILE_PHONE,
            'url'         => url('about.php'),
            'image'       => asset('img/hero/hero.png'),
            'address'     => [
                '@type'           => 'PostalAddress',
                'addressCountry'  => 'NG',
                'addressRegion'   => 'Imo State & Lagos',
            ],
            'worksFor'    => ['@id' => url('') . '#organization'],
            'sameAs'      => array_values(array_filter([
                PROFILE_LINKEDIN,
                PROFILE_GITHUB,
                PROFILE_VENTURE_URL,
                PROFILE_PORTFOLIO_URL,
            ])),
            'knowsAbout'  => [
                'Full-Stack Software Architecture',
                'Artificial Intelligence & Machine Learning',
                'Retrieval-Augmented Generation (RAG)',
                'FinTech & Escrow Ledgers',
                'AWS Cloud-Native Infrastructure',
                'Laravel, PHP, Node.js, PostgreSQL, Redis',
            ],
            'knowsLanguage' => ['en', 'ig'],
            'hasCredential' => [
                '@type' => 'EducationalOccupationalCredential',
                'name'  => 'Corporate Registration BN ' . PROFILE_REG_NO,
                'credentialCategory' => 'registration',
            ],
        ];
    }

    /** The corporate entity behind the portfolio. */
    function seo_organization_graph(): array
    {
        return [
            '@type'        => ['Organization', 'Corporation'],
            '@id'          => url('') . '#organization',
            'name'         => PROFILE_COMPANY,
            'alternateName' => 'Morex Web Enterprise',
            'url'          => PROFILE_VENTURE_URL,
            'email'        => 'mailto:' . PROFILE_CORP_EMAIL,
            'founder'      => ['@id' => url('') . '#person'],
            'vatID'        => PROFILE_REG_NO,
            'address'      => [
                '@type'           => 'PostalAddress',
                'addressLocality' => 'Lagos',
                'addressCountry'  => 'NG',
            ],
            'contactPoint' => [
                '@type'          => 'ContactPoint',
                'contactType'    => 'customer support',
                'email'          => PROFILE_CORP_EMAIL,
                'availableLanguage' => ['en'],
            ],
            'sameAs'       => [PROFILE_VENTURE_URL, PROFILE_LINKEDIN],
        ];
    }

    /**
     * FAQPage graph - MUST only be passed items that are rendered on screen.
     *
     * @param array<int,array{question:string,answer:string}> $items
     */
    function seo_faq_graph(array $items, string $pageUrl): array
    {
        $mainEntities = [];
        foreach ($items as $item) {
            $q = trim((string)($item['question'] ?? ''));
            $a = trim((string)($item['answer'] ?? ''));
            if ($q === '' || $a === '') {
                continue;
            }
            $mainEntities[] = [
                '@type'          => 'Question',
                'name'           => $q,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => $a,
                ],
            ];
        }

        if ($mainEntities === []) {
            return [];
        }

        return [
            '@type'     => 'FAQPage',
            '@id'       => $pageUrl . '#faq',
            'url'       => $pageUrl,
            'mainEntity' => $mainEntities,
        ];
    }

    /**
     * BreadcrumbList graph.
     *
     * @param array<int,array{name:string,url:string}> $trail
     */
    function seo_breadcrumb_graph(array $trail): array
    {
        $items = [];
        foreach (array_values($trail) as $position => $crumb) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $position + 1,
                'name'     => (string)$crumb['name'],
                'item'     => (string)$crumb['url'],
            ];
        }
        if ($items === []) {
            return [];
        }
        return [
            '@type'        => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * Blog posting graph for blog-detail.php.
     *
     * @param array<string,mixed> $article
     */
    function seo_article_graph(array $article, string $pageUrl, string $imageUrl): array
    {
        $published = (string)($article['published_at'] ?? ($article['date'] ?? ''));
        $stamp     = $published !== '' && strtotime($published) !== false ? date(DATE_W3C, (int)strtotime($published)) : date(DATE_W3C);

        return [
            '@type'         => 'Article',
            '@id'           => $pageUrl . '#article',
            'headline'      => (string)($article['title'] ?? ''),
            'description'   => (string)($article['excerpt'] ?? ($article['subtitle'] ?? '')),
            'image'         => $imageUrl,
            'datePublished' => $stamp,
            'dateModified'  => $stamp,
            'inLanguage'    => 'en',
            'author'        => ['@id' => url('') . '#person'],
            'publisher'     => ['@id' => url('') . '#organization'],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $pageUrl],
        ];
    }

    /**
     * CreativeWork graph for case studies.
     *
     * @param array<string,mixed> $project
     */
    function seo_project_graph(array $project, string $pageUrl, string $imageUrl): array
    {
        $graph = [
            '@type'                => ['CreativeWork', 'SoftwareApplication'],
            '@id'                  => $pageUrl . '#project',
            'name'                 => (string)($project['title'] ?? ''),
            'description'          => (string)($project['summary'] ?? ''),
            'url'                  => $pageUrl,
            'image'                => $imageUrl,
            'author'               => ['@id' => url('') . '#person'],
            'creator'              => ['@id' => url('') . '#organization'],
            'inLanguage'           => 'en',
            'genre'                => (string)($project['category_name'] ?? 'Case Study'),
            'keywords'             => is_array($project['stack'] ?? null)
                ? implode(', ', array_map('strval', $project['stack']))
                : (string)($project['category_name'] ?? ''),
        ];

        if (!empty($project['live_url'])) {
            $graph['sameAs'] = (string)$project['live_url'];
        }
        return $graph;
    }
}
