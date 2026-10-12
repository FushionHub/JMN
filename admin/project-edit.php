<?php
declare(strict_types=1);

/**
 * Admin Project Editor — create or edit a portfolio project.
 *
 * JSON-backed columns (categories, metrics, stack, architectural_highlights,
 * gallery) are edited as plain one-per-line textareas via the shared helpers
 * in includes/helpers.php, then re-encoded on save. The public templates
 * output problem_statement / solution with e() (escaped), so those stay plain
 * textareas — TinyMCE is reserved for the blog body, which renders raw HTML.
 */

require_once __DIR__ . '/includes/auth.php';
admin_require_auth();
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/cache.php';
require_once __DIR__ . '/includes/helpers.php';

$db = get_db();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$isNew = $id <= 0;

$project = [
    'id' => 0, 'slug' => '', 'title' => '', 'subtitle' => '', 'role' => '',
    'client' => '', 'date_period' => '', 'featured' => 0,
    'category_filter' => '', 'category_name' => '', 'categories' => [],
    'summary' => '', 'image' => '', 'image_large' => '',
    'metrics' => [], 'stack' => [], 'architectural_highlights' => [],
    'problem_statement' => '', 'solution' => '', 'gallery' => [], 'live_url' => '',
];

if (!$isNew && $db !== null) {
    try {
        $stmt = $db->prepare('SELECT * FROM `projects` WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            $_SESSION['flash_err'] = 'Project not found.';
            header('Location: projects.php');
            exit;
        }
        $project = [
            'id'                     => (int)$row['id'],
            'slug'                   => (string)$row['slug'],
            'title'                  => (string)$row['title'],
            'subtitle'               => (string)($row['subtitle'] ?? ''),
            'role'                   => (string)$row['role'],
            'client'                 => (string)($row['client'] ?? ''),
            'date_period'            => (string)($row['date_period'] ?? ''),
            'featured'               => (int)$row['featured'],
            'category_filter'        => (string)$row['category_filter'],
            'category_name'          => (string)$row['category_name'],
            'categories'             => json_decode($row['categories'] ?? '[]', true) ?: [],
            'summary'                => (string)$row['summary'],
            'image'                  => (string)$row['image'],
            'image_large'            => (string)$row['image_large'],
            'metrics'                => json_decode($row['metrics'] ?? '{}', true) ?: [],
            'stack'                  => json_decode($row['stack'] ?? '[]', true) ?: [],
            'architectural_highlights' => json_decode($row['architectural_highlights'] ?? '[]', true) ?: [],
            'problem_statement'      => (string)($row['problem_statement'] ?? ''),
            'solution'               => (string)($row['solution'] ?? ''),
            'gallery'                => json_decode($row['gallery'] ?? '[]', true) ?: [],
            'live_url'               => (string)($row['live_url'] ?? ''),
        ];
    } catch (Throwable $e) {
        error_log('Project edit load failed: ' . $e->getMessage());
        $_SESSION['flash_err'] = 'Could not load the project.';
        header('Location: projects.php');
        exit;
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !verify_csrf_token($token)) {
        $_SESSION['flash_err'] = 'Your security token has expired. Please try again.';
        header('Location: project-edit.php' . ($id > 0 ? '?id=' . $id : ''));
        exit;
    }
    if (!check_rate_limit('admin_project_save', 2)) {
        $_SESSION['flash_err'] = 'Please wait a moment before saving again.';
        header('Location: project-edit.php' . ($id > 0 ? '?id=' . $id : ''));
        exit;
    }

    $f = static function (string $key, string $default = ''): string {
        $v = $_POST[$key] ?? $default;
        return is_string($v) ? trim($v) : $default;
    };

    $title      = $f('title');
    $slug       = $f('slug');
    $subtitle   = $f('subtitle');
    $role       = $f('role');
    $client     = $f('client');
    $datePeriod = $f('date_period');
    $featured   = ($_POST['featured'] ?? '') === '1' ? 1 : 0;
    $catFilter  = $f('category_filter');
    $catName    = $f('category_name');
    $categories = admin_lines_to_list($f('categories'));
    $summary    = $f('summary');
    $image      = $f('image');
    $imageLarge = $f('image_large');
    $metrics    = admin_lines_to_map($f('metrics'));
    $stack      = admin_lines_to_list($f('stack'));
    $highlights = admin_lines_to_list($f('architectural_highlights'));
    $problem    = $f('problem_statement');
    $solution   = $f('solution');
    $gallery    = admin_lines_to_list($f('gallery'));
    $liveUrl    = $f('live_url');

    if (mb_strlen($title) < 3) { $errors['title'] = 'Title must be at least 3 characters.'; }
    if (mb_strlen($title) > 255) { $errors['title'] = 'Title is too long (max 255).'; }
    if ($role === '') { $errors['role'] = 'Role is required.'; }
    if ($catName === '') { $errors['category_name'] = 'Category name is required.'; }
    if ($summary === '') { $errors['summary'] = 'Summary is required.'; }

    if ($slug === '') {
        $slug = slugify($title);
    } else {
        $slug = slugify($slug);
    }
    if ($slug === '') { $errors['slug'] = 'Could not derive a valid slug from the title.'; }

    if ($image === '') { $errors['image'] = 'An image path or upload is required.'; }
    if ($imageLarge === '') { $imageLarge = $image; }

    if ($liveUrl !== '' && !filter_var($liveUrl, FILTER_VALIDATE_URL)) {
        $errors['live_url'] = 'Live URL must be a valid absolute URL (or left blank).';
    }

    // Image upload (optional; overrides the text field when present).
    if (!empty($_FILES['image_file']['name']) && (int)($_FILES['image_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $uploaded = admin_handle_image_upload($_FILES['image_file'], 'project/uploads');
        if (is_array($uploaded) && isset($uploaded['error'])) {
            $errors['image_file'] = $uploaded['error'];
        } elseif (is_string($uploaded) && $uploaded !== '') {
            $image      = $uploaded;
            $imageLarge = $uploaded;
        }
    }

    // Slug uniqueness (auto-suffix on collision).
    if ($db !== null && $slug !== '' && empty($errors['slug'])) {
        try {
            $sql = 'SELECT COUNT(*) FROM `projects` WHERE `slug` = :slug';
            $params = [':slug' => $slug];
            if (!$isNew) {
                $sql .= ' AND `id` != :id';
                $params[':id'] = $id;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            if ((int)$stmt->fetchColumn() > 0) {
                $base = $slug;
                $n = 2;
                do {
                    $slug = $base . '-' . $n;
                    $stmt = $db->prepare("SELECT COUNT(*) FROM `projects` WHERE `slug` = :slug AND (`id` = 0" . (!$isNew ? " OR `id` != :id" : "") . ")");
                    $p = [':slug' => $slug];
                    if (!$isNew) { $p[':id'] = $id; }
                    $stmt->execute($p);
                    $n++;
                } while ((int)$stmt->fetchColumn() > 0 && $n < 50);
            }
        } catch (Throwable $e) {
            error_log('Project slug uniqueness check failed: ' . $e->getMessage());
        }
    }

    if ($errors) {
        $project['title']        = $title;
        $project['slug']         = $slug;
        $project['subtitle']     = $subtitle;
        $project['role']         = $role;
        $project['client']       = $client;
        $project['date_period']  = $datePeriod;
        $project['featured']     = $featured;
        $project['category_filter'] = $catFilter;
        $project['category_name']   = $catName;
        $project['categories']   = $categories;
        $project['summary']      = $summary;
        $project['image']        = $image;
        $project['image_large']  = $imageLarge;
        $project['metrics']      = $metrics;
        $project['stack']        = $stack;
        $project['architectural_highlights'] = $highlights;
        $project['problem_statement'] = $problem;
        $project['solution']     = $solution;
        $project['gallery']      = $gallery;
        $project['live_url']     = $liveUrl;
        $_SESSION['flash_err'] = 'Please correct the errors below and try again.';
    } elseif ($db === null) {
        $_SESSION['flash_err'] = 'Database unavailable; the project was not saved.';
        header('Location: projects.php');
        exit;
    } else {
        try {
            $jsonCategories = json_encode($categories, JSON_UNESCAPED_UNICODE);
            $jsonMetrics    = json_encode($metrics, JSON_UNESCAPED_UNICODE);
            $jsonStack      = json_encode($stack, JSON_UNESCAPED_UNICODE);
            $jsonHighlights = json_encode($highlights, JSON_UNESCAPED_UNICODE);
            $jsonGallery    = json_encode($gallery, JSON_UNESCAPED_UNICODE);

            if ($isNew) {
                $stmt = $db->prepare("
                    INSERT INTO `projects`
                        (`slug`,`title`,`subtitle`,`role`,`client`,`date_period`,`featured`,
                         `category_filter`,`category_name`,`categories`,`summary`,`image`,`image_large`,
                         `metrics`,`stack`,`architectural_highlights`,`problem_statement`,`solution`,`gallery`,`live_url`)
                    VALUES
                        (:slug,:title,:subtitle,:role,:client,:date_period,:featured,
                         :category_filter,:category_name,:categories,:summary,:image,:image_large,
                         :metrics,:stack,:architectural_highlights,:problem_statement,:solution,:gallery,:live_url)
                ");
                $stmt->execute([
                    ':slug' => $slug, ':title' => mb_substr($title, 0, 255),
                    ':subtitle' => $subtitle !== '' ? mb_substr($subtitle, 0, 255) : null,
                    ':role' => mb_substr($role, 0, 150),
                    ':client' => $client !== '' ? mb_substr($client, 0, 150) : null,
                    ':date_period' => $datePeriod !== '' ? mb_substr($datePeriod, 0, 100) : null,
                    ':featured' => $featured,
                    ':category_filter' => mb_substr($catFilter !== '' ? $catFilter : $catName, 0, 100),
                    ':category_name' => mb_substr($catName, 0, 100),
                    ':categories' => $jsonCategories,
                    ':summary' => $summary,
                    ':image' => mb_substr($image, 0, 255),
                    ':image_large' => mb_substr($imageLarge, 0, 255),
                    ':metrics' => $jsonMetrics,
                    ':stack' => $jsonStack,
                    ':architectural_highlights' => $jsonHighlights,
                    ':problem_statement' => $problem,
                    ':solution' => $solution,
                    ':gallery' => $jsonGallery,
                    ':live_url' => $liveUrl !== '' ? mb_substr($liveUrl, 0, 255) : null,
                ]);
                $id = (int)$db->lastInsertId();
            } else {
                $stmt = $db->prepare("
                    UPDATE `projects` SET
                        `slug` = :slug, `title` = :title, `subtitle` = :subtitle,
                        `role` = :role, `client` = :client, `date_period` = :date_period,
                        `featured` = :featured, `category_filter` = :category_filter,
                        `category_name` = :category_name, `categories` = :categories,
                        `summary` = :summary, `image` = :image, `image_large` = :image_large,
                        `metrics` = :metrics, `stack` = :stack,
                        `architectural_highlights` = :architectural_highlights,
                        `problem_statement` = :problem_statement, `solution` = :solution,
                        `gallery` = :gallery, `live_url` = :live_url
                    WHERE `id` = :id
                ");
                $stmt->execute([
                    ':slug' => $slug, ':title' => mb_substr($title, 0, 255),
                    ':subtitle' => $subtitle !== '' ? mb_substr($subtitle, 0, 255) : null,
                    ':role' => mb_substr($role, 0, 150),
                    ':client' => $client !== '' ? mb_substr($client, 0, 150) : null,
                    ':date_period' => $datePeriod !== '' ? mb_substr($datePeriod, 0, 100) : null,
                    ':featured' => $featured,
                    ':category_filter' => mb_substr($catFilter !== '' ? $catFilter : $catName, 0, 100),
                    ':category_name' => mb_substr($catName, 0, 100),
                    ':categories' => $jsonCategories,
                    ':summary' => $summary,
                    ':image' => mb_substr($image, 0, 255),
                    ':image_large' => mb_substr($imageLarge, 0, 255),
                    ':metrics' => $jsonMetrics,
                    ':stack' => $jsonStack,
                    ':architectural_highlights' => $jsonHighlights,
                    ':problem_statement' => $problem,
                    ':solution' => $solution,
                    ':gallery' => $jsonGallery,
                    ':live_url' => $liveUrl !== '' ? mb_substr($liveUrl, 0, 255) : null,
                    ':id' => $id,
                ]);
            }

            $_SESSION['flash_ok'] = $isNew
                ? 'Project created (id #' . $id . ').'
                : 'Project updated.';
            cache_clear(); // content changed — drop cached lists so the public site updates immediately
            rotate_csrf_token();
            header('Location: projects.php');
            exit;
        } catch (Throwable $e) {
            error_log('Project save failed: ' . $e->getMessage());
            $_SESSION['flash_err'] = 'Database error while saving: ' . $e->getMessage();
        }
    }
}

// Helpers to render the JSON-backed fields as plain textareas.
$categoriesText    = implode("\n", $project['categories']);
$stackText         = implode("\n", $project['stack']);
$highlightsText    = implode("\n", $project['architectural_highlights']);
$galleryText       = implode("\n", $project['gallery']);
$metricsText       = '';
foreach ($project['metrics'] as $mKey => $mVal) {
    $metricsText .= $mKey . ': ' . $mVal . "\n";
}

$csrf = generate_csrf_token();
$adminTitle = $isNew ? 'New Project' : 'Edit: ' . $project['title'];
require_once __DIR__ . '/includes/layout-header.php';
?>

<div class="admin-filters">
    <a class="admin-chip" href="projects.php"><i class="ri-arrow-left-s-line"></i> Back to projects</a>
    <span class="admin-chip is-active"><?= $isNew ? 'New project' : 'Editing' ?></span>
</div>

<form method="post" action="project-edit.php<?= $isNew ? '' : '?id=' . $id ?>" enctype="multipart/form-data" id="projectForm">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="admin-detail-grid">
        <!-- Main column -->
        <div>
            <div class="admin-card">
                <div class="admin-card-head"><h2>Content</h2></div>
                <div class="admin-card-body">

                    <div class="admin-field">
                        <label for="f_title">Title *</label>
                        <input class="admin-input" type="text" id="f_title" name="title"
                               value="<?= e($project['title']) ?>" required maxlength="255">
                        <?php if (!empty($errors['title'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['title']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="admin-field">
                        <label for="f_slug">Slug *</label>
                        <div class="admin-slug-row">
                            <input class="admin-input" type="text" id="f_slug" name="slug"
                                   value="<?= e($project['slug']) ?>" placeholder="auto-generated-from-title">
                            <button type="button" class="admin-slug-lock" id="slug_lock"
                                    data-initial-locked="<?= $isNew ? '0' : '1' ?>"
                                    title="Lock / unlock auto-generation">
                                <i class="ri-lock-line"></i>
                            </button>
                        </div>
                        <div class="admin-hint">Used in the URL: single-project.php?slug=…</div>
                        <?php if (!empty($errors['slug'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['slug']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="admin-field">
                        <label for="f_subtitle">Subtitle</label>
                        <input class="admin-input" type="text" id="f_subtitle" name="subtitle"
                               value="<?= e($project['subtitle']) ?>" maxlength="255">
                    </div>

                    <div class="admin-field">
                        <label for="f_summary">Summary * <span style="font-weight:400;color:var(--a-muted);">(shown on the project cards)</span></label>
                        <textarea class="admin-textarea" id="f_summary" name="summary" rows="3" required><?= e($project['summary']) ?></textarea>
                        <?php if (!empty($errors['summary'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['summary']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="admin-field">
                        <label for="f_problem_statement">Problem statement</label>
                        <textarea class="admin-textarea" id="f_problem_statement" name="problem_statement" rows="5"><?= e($project['problem_statement']) ?></textarea>
                    </div>

                    <div class="admin-field">
                        <label for="f_solution">Architectural solution</label>
                        <textarea class="admin-textarea" id="f_solution" name="solution" rows="6"><?= e($project['solution']) ?></textarea>
                    </div>

                    <div class="admin-field">
                        <label for="f_architectural_highlights">Architectural highlights <span style="font-weight:400;color:var(--a-muted);">(one per line)</span></label>
                        <textarea class="admin-textarea" id="f_architectural_highlights" name="architectural_highlights" rows="5"><?= e($highlightsText) ?></textarea>
                    </div>

                </div>
            </div>
        </div>

        <!-- Sidebar column -->
        <div>
            <div class="admin-card">
                <div class="admin-card-head"><h2>Publish</h2></div>
                <div class="admin-card-body">
                    <div class="admin-field">
                        <label for="f_featured">Featured</label>
                        <select class="admin-select" id="f_featured" name="featured">
                            <option value="0" <?= $project['featured'] === 0 ? 'selected' : '' ?>>Standard</option>
                            <option value="1" <?= $project['featured'] === 1 ? 'selected' : '' ?>>Featured (home page)</option>
                        </select>
                    </div>
                    <div class="admin-field">
                        <label for="f_live_url">Live URL</label>
                        <input class="admin-input" type="url" id="f_live_url" name="live_url"
                               value="<?= e($project['live_url']) ?>" placeholder="https://…">
                        <?php if (!empty($errors['live_url'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['live_url']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-form-actions">
                        <button class="admin-btn admin-btn--primary" type="submit">
                            <i class="ri-save-3-fill"></i> <?= $isNew ? 'Create project' : 'Save changes' ?>
                        </button>
                        <?php if (!$isNew): ?>
                            <a class="admin-btn admin-btn--secondary" href="<?= e(url('single-project.php?slug=' . rawurlencode($project['slug']))) ?>" target="_blank" rel="noopener">
                                <i class="ri-eye-line"></i> Preview
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-head"><h2>Details</h2></div>
                <div class="admin-card-body">
                    <div class="admin-field">
                        <label for="f_role">Role *</label>
                        <input class="admin-input" type="text" id="f_role" name="role"
                               value="<?= e($project['role']) ?>" maxlength="150">
                        <?php if (!empty($errors['role'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['role']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-field">
                        <label for="f_client">Client</label>
                        <input class="admin-input" type="text" id="f_client" name="client"
                               value="<?= e($project['client']) ?>" maxlength="150">
                    </div>
                    <div class="admin-field">
                        <label for="f_date_period">Date period</label>
                        <input class="admin-input" type="text" id="f_date_period" name="date_period"
                               value="<?= e($project['date_period']) ?>" placeholder="2023 - 2024" maxlength="100">
                    </div>
                    <div class="admin-field">
                        <label for="f_category_name">Category name *</label>
                        <input class="admin-input" type="text" id="f_category_name" name="category_name"
                               value="<?= e($project['category_name']) ?>" maxlength="100">
                        <?php if (!empty($errors['category_name'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['category_name']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-field">
                        <label for="f_category_filter">Category filter</label>
                        <input class="admin-input" type="text" id="f_category_filter" name="category_filter"
                               value="<?= e($project['category_filter']) ?>" placeholder="web applications fintech" maxlength="100">
                        <div class="admin-hint">Space-separated keywords used by the project filter buttons.</div>
                    </div>
                    <div class="admin-field">
                        <label for="f_categories">Tags <span style="font-weight:400;color:var(--a-muted);">(one per line)</span></label>
                        <textarea class="admin-textarea" id="f_categories" name="categories" rows="3"><?= e($categoriesText) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-head"><h2>Images</h2></div>
                <div class="admin-card-body">
                    <div class="admin-field">
                        <label for="f_image">Card image *</label>
                        <input class="admin-input" type="text" id="f_image" name="image"
                               value="<?= e($project['image']) ?>" placeholder="img/project/1.jpg">
                        <?php if (!empty($errors['image'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['image']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-field">
                        <label for="f_image_large">Large / detail image</label>
                        <input class="admin-input" type="text" id="f_image_large" name="image_large"
                               value="<?= e($project['image_large']) ?>" placeholder="img/project/11.jpg">
                        <div class="admin-hint">Falls back to the card image when blank.</div>
                    </div>
                    <div class="admin-field">
                        <label>Or upload a card image</label>
                        <div class="admin-drop" id="imageDrop">
                            <i class="ri-upload-cloud-2-line"></i>
                            Drop an image here or click to browse
                            <input type="file" id="f_image_file" name="image_file" accept="image/*">
                        </div>
                        <?php if (!empty($errors['image_file'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['image_file']) ?></div>
                        <?php endif; ?>
                        <?php if ($project['image'] !== ''): ?>
                            <img class="admin-img-preview" id="imgPreview" src="<?= e($project['image']) ?>" alt="" style="display:block;">
                        <?php else: ?>
                            <img class="admin-img-preview" id="imgPreview" src="" alt="">
                        <?php endif; ?>
                        <div class="admin-hint">JPG, PNG, WEBP, GIF or SVG &middot; max 5 MB.</div>
                    </div>
                    <div class="admin-field">
                        <label for="f_gallery">Gallery <span style="font-weight:400;color:var(--a-muted);">(one image path per line)</span></label>
                        <textarea class="admin-textarea" id="f_gallery" name="gallery" rows="4"><?= e($galleryText) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-head"><h2>Stack &amp; metrics</h2></div>
                <div class="admin-card-body">
                    <div class="admin-field">
                        <label for="f_stack">Technology stack <span style="font-weight:400;color:var(--a-muted);">(one per line)</span></label>
                        <textarea class="admin-textarea" id="f_stack" name="stack" rows="5"><?= e($stackText) ?></textarea>
                    </div>
                    <div class="admin-field">
                        <label for="f_metrics">Metrics <span style="font-weight:400;color:var(--a-muted);">(one “Key: Value” per line)</span></label>
                        <textarea class="admin-textarea" id="f_metrics" name="metrics" rows="4"><?= e($metricsText) ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>
