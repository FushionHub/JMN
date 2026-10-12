<?php
declare(strict_types=1);

/**
 * Admin Blog Editor — create or edit a post.
 *
 * Content is edited with TinyMCE 8 (registered API key) loaded from the
 * official CDN; the CSP allowances live in config/security.php. The form is a
 * classic POST (no AJAX) so it degrades gracefully if the editor fails to
 * load — the raw textarea stays editable as plain HTML.
 *
 * Image handling: either a text field (path/URL, matching the existing
 * assets/img/blog/*.jpg convention) or a one-shot file upload that is
 * validated with finfo and moved into assets/img/blog/uploads/.
 */

require_once __DIR__ . '/includes/auth.php';
admin_require_auth();
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/cache.php';
require_once __DIR__ . '/includes/helpers.php';

$adminTitle = 'Blog Editor';
$adminNav   = 'blog';

$db = get_db();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$isNew = $id <= 0;

// ---------------------------------------------------------------------------
// Load existing post (edit mode)
// ---------------------------------------------------------------------------
$post = [
    'id' => 0, 'slug' => '', 'title' => '', 'subtitle' => '', 'category' => 'Engineering',
    'read_time' => '5 min read', 'image' => '', 'excerpt' => '', 'content' => '',
    'status' => 'published', 'published_at' => date('Y-m-d\TH:i'),
];

if (!$isNew && $db !== null) {
    try {
        $stmt = $db->prepare('SELECT * FROM `blogs` WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            $_SESSION['flash_err'] = 'Post not found.';
            header('Location: blog.php');
            exit;
        }
        $post = [
            'id'           => (int)$row['id'],
            'slug'         => (string)$row['slug'],
            'title'        => (string)$row['title'],
            'subtitle'     => (string)($row['subtitle'] ?? ''),
            'category'     => (string)$row['category'],
            'read_time'    => (string)($row['read_time'] ?? '5 min read'),
            'image'        => (string)$row['image'],
            'excerpt'      => (string)$row['excerpt'],
            'content'      => (string)$row['content'],
            'status'       => (string)$row['status'],
            'published_at' => date('Y-m-d\TH:i', strtotime((string)($row['published_at'] ?? $row['created_at']))),
        ];
    } catch (Throwable $e) {
        error_log('Blog edit load failed: ' . $e->getMessage());
        $_SESSION['flash_err'] = 'Could not load the post.';
        header('Location: blog.php');
        exit;
    }
}

// ---------------------------------------------------------------------------
// Handle POST (save)
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !verify_csrf_token($token)) {
        $_SESSION['flash_err'] = 'Your security token has expired. Please try again.';
        header('Location: blog-edit.php' . ($id > 0 ? '?id=' . $id : ''));
        exit;
    }
    if (!check_rate_limit('admin_blog_save', 2)) {
        $_SESSION['flash_err'] = 'Please wait a moment before saving again.';
        header('Location: blog-edit.php' . ($id > 0 ? '?id=' . $id : ''));
        exit;
    }

    $f = static function (string $key, string $default = ''): string {
        $v = $_POST[$key] ?? $default;
        return is_string($v) ? trim($v) : $default;
    };

    $title     = $f('title');
    $slug      = $f('slug');
    $subtitle  = $f('subtitle');
    $category  = $f('category', 'Engineering');
    $readTime  = $f('read_time', '5 min read');
    $image     = $f('image');
    $excerpt   = $f('excerpt');
    $content   = $_POST['content'] ?? '';
    $content   = is_string($content) ? trim($content) : '';
    $status    = $f('status', 'published') === 'draft' ? 'draft' : 'published';
    $pubAtRaw  = $f('published_at');

    $errors = [];
    if (mb_strlen($title) < 3) { $errors['title'] = 'Title must be at least 3 characters.'; }
    if (mb_strlen($title) > 255) { $errors['title'] = 'Title is too long (max 255).'; }
    if (mb_strlen($excerpt) < 10) { $errors['excerpt'] = 'Excerpt must be at least 10 characters.'; }
    if ($content === '') { $errors['content'] = 'Content cannot be empty.'; }
    if ($category === '') { $category = 'Engineering'; }

    // Slug: auto-derive from title when blank, then enforce uniqueness.
    if ($slug === '') {
        $slug = slugify($title);
    } else {
        $slug = slugify($slug);
    }
    if ($slug === '') { $errors['slug'] = 'Could not derive a valid slug from the title.'; }

    // Published date.
    $publishedAt = null;
    if ($pubAtRaw !== '') {
        $dt = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $pubAtRaw);
        if ($dt instanceof DateTimeImmutable && $dt->format('Y-m-d\TH:i') === $pubAtRaw) {
            $publishedAt = $dt->format('Y-m-d H:i:s');
        }
    }
    if ($publishedAt === null) {
        $publishedAt = $isNew ? date('Y-m-d H:i:s') : $post['published_at'];
        // published_at in the form is Y-m-d\TH:i; convert the loaded value.
        if (!$isNew && str_contains($publishedAt, 'T')) {
            $publishedAt = date('Y-m-d H:i:s', strtotime($publishedAt));
        }
    }

    // Image upload (optional; overrides the text field when present).
    if (!empty($_FILES['image_file']['name']) && (int)($_FILES['image_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $uploaded = admin_handle_image_upload($_FILES['image_file']);
        if (is_array($uploaded) && isset($uploaded['error'])) {
            $errors['image_file'] = $uploaded['error'];
        } elseif (is_string($uploaded) && $uploaded !== '') {
            $image = $uploaded;
        }
    }
    if ($image === '') { $errors['image'] = 'An image path or upload is required.'; }

    // Uniqueness check for the slug (ignore the current row when editing).
    if ($db !== null && $slug !== '' && empty($errors['slug'])) {
        try {
            $sql = 'SELECT COUNT(*) FROM `blogs` WHERE `slug` = :slug';
            $params = [':slug' => $slug];
            if (!$isNew) {
                $sql .= ' AND `id` != :id';
                $params[':id'] = $id;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            if ((int)$stmt->fetchColumn() > 0) {
                // Auto-suffix rather than reject.
                $base = $slug;
                $n = 2;
                do {
                    $slug = $base . '-' . $n;
                    $stmt = $db->prepare("SELECT COUNT(*) FROM `blogs` WHERE `slug` = :slug AND (`id` = 0" . (!$isNew ? " OR `id` != :id" : "") . ")");
                    $p = [':slug' => $slug];
                    if (!$isNew) { $p[':id'] = $id; }
                    $stmt->execute($p);
                    $n++;
                } while ((int)$stmt->fetchColumn() > 0 && $n < 50);
            }
        } catch (Throwable $e) {
            error_log('Slug uniqueness check failed: ' . $e->getMessage());
        }
    }

    if ($errors) {
        // Repopulate the form from the submitted values.
        $post['title']        = $title;
        $post['slug']         = $slug;
        $post['subtitle']     = $subtitle;
        $post['category']     = $category;
        $post['read_time']    = $readTime;
        $post['image']        = $image;
        $post['excerpt']      = $excerpt;
        $post['content']      = $content;
        $post['status']       = $status;
        $post['published_at'] = $pubAtRaw !== '' ? $pubAtRaw : $post['published_at'];
        $_SESSION['flash_err'] = 'Please correct the errors below and try again.';
        // fall through to render with $errors
    } elseif ($db === null) {
        $_SESSION['flash_err'] = 'Database unavailable; the post was not saved.';
        header('Location: blog.php');
        exit;
    } else {
        try {
            if ($isNew) {
                $stmt = $db->prepare("
                    INSERT INTO `blogs` (`slug`,`title`,`subtitle`,`category`,`read_time`,`image`,`excerpt`,`content`,`status`,`published_at`)
                    VALUES (:slug,:title,:subtitle,:category,:read_time,:image,:excerpt,:content,:status,:published_at)
                ");
                $stmt->execute([
                    ':slug' => $slug, ':title' => mb_substr($title, 0, 255),
                    ':subtitle' => $subtitle !== '' ? mb_substr($subtitle, 0, 255) : null,
                    ':category' => mb_substr($category, 0, 100),
                    ':read_time' => mb_substr($readTime, 0, 50),
                    ':image' => mb_substr($image, 0, 255),
                    ':excerpt' => $excerpt, ':content' => $content,
                    ':status' => $status, ':published_at' => $publishedAt,
                ]);
                $id = (int)$db->lastInsertId();
            } else {
                $stmt = $db->prepare("
                    UPDATE `blogs` SET
                        `slug` = :slug, `title` = :title, `subtitle` = :subtitle,
                        `category` = :category, `read_time` = :read_time,
                        `image` = :image, `excerpt` = :excerpt, `content` = :content,
                        `status` = :status, `published_at` = :published_at
                    WHERE `id` = :id
                ");
                $stmt->execute([
                    ':slug' => $slug, ':title' => mb_substr($title, 0, 255),
                    ':subtitle' => $subtitle !== '' ? mb_substr($subtitle, 0, 255) : null,
                    ':category' => mb_substr($category, 0, 100),
                    ':read_time' => mb_substr($readTime, 0, 50),
                    ':image' => mb_substr($image, 0, 255),
                    ':excerpt' => $excerpt, ':content' => $content,
                    ':status' => $status, ':published_at' => $publishedAt,
                    ':id' => $id,
                ]);
            }

            $_SESSION['flash_ok'] = $isNew
                ? 'Post created (id #' . $id . ').'
                : 'Post updated.';
            cache_clear(); // content changed — drop cached lists so the public site updates immediately
            rotate_csrf_token();
            header('Location: blog.php');
            exit;
        } catch (Throwable $e) {
            error_log('Blog save failed: ' . $e->getMessage());
            $_SESSION['flash_err'] = 'Database error while saving: ' . $e->getMessage();
        }
    }
}

// The form uses Y-m-d\TH:i for the date input; normalise the loaded value.
$formPubAt = $post['published_at'];
if (str_contains($formPubAt, ' ')) {
    $ts = strtotime($formPubAt);
    if ($ts !== false) { $formPubAt = date('Y-m-d\TH:i', $ts); }
}

$csrf = generate_csrf_token();
$adminTitle = $isNew ? 'New Post' : 'Edit: ' . $post['title'];
require_once __DIR__ . '/includes/layout-header.php';
?>

<div class="admin-filters">
    <a class="admin-chip" href="blog.php"><i class="ri-arrow-left-s-line"></i> Back to posts</a>
    <span class="admin-chip is-active"><?= $isNew ? 'New post' : 'Editing' ?></span>
</div>

<form method="post" action="blog-edit.php<?= $isNew ? '' : '?id=' . $id ?>" enctype="multipart/form-data" id="blogForm">
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
                               value="<?= e($post['title']) ?>" required maxlength="255">
                        <?php if (!empty($errors['title'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['title']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="admin-field">
                        <label for="f_slug">Slug *</label>
                        <div class="admin-slug-row">
                            <input class="admin-input" type="text" id="f_slug" name="slug"
                                   value="<?= e($post['slug']) ?>" placeholder="auto-generated-from-title">
                            <button type="button" class="admin-slug-lock" id="slug_lock"
                                    data-initial-locked="<?= $isNew ? '0' : '1' ?>"
                                    title="Lock / unlock auto-generation">
                                <i class="ri-lock-line"></i>
                            </button>
                        </div>
                        <div class="admin-hint">Used in the URL: blog-detail.php?slug=…</div>
                        <?php if (!empty($errors['slug'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['slug']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="admin-field">
                        <label for="f_subtitle">Subtitle</label>
                        <input class="admin-input" type="text" id="f_subtitle" name="subtitle"
                               value="<?= e($post['subtitle']) ?>" maxlength="255">
                    </div>

                    <div class="admin-field">
                        <label for="f_excerpt">Excerpt * <span style="font-weight:400;color:var(--a-muted);">(shown on the blog index &amp; used as the meta description)</span></label>
                        <textarea class="admin-textarea" id="f_excerpt" name="excerpt" rows="3" required><?= e($post['excerpt']) ?></textarea>
                        <?php if (!empty($errors['excerpt'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['excerpt']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="admin-field">
                        <label for="f_content">Body content * <span style="font-weight:400;color:var(--a-muted);">(TinyMCE 8 rich-text editor)</span></label>
                        <textarea class="admin-textarea admin-tinymce-host" id="f_content" name="content" rows="18"><?= e($post['content']) ?></textarea>
                        <?php if (!empty($errors['content'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['content']) ?></div>
                        <?php endif; ?>
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
                        <label for="f_status">Status</label>
                        <select class="admin-select" id="f_status" name="status">
                            <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                            <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                        </select>
                    </div>
                    <div class="admin-field">
                        <label for="f_published_at">Publish date</label>
                        <input class="admin-input" type="datetime-local" id="f_published_at" name="published_at"
                               value="<?= e($formPubAt) ?>">
                    </div>
                    <div class="admin-form-actions">
                        <button class="admin-btn admin-btn--primary" type="submit">
                            <i class="ri-save-3-fill"></i> <?= $isNew ? 'Create post' : 'Save changes' ?>
                        </button>
                        <?php if (!$isNew && $post['status'] === 'published'): ?>
                            <a class="admin-btn admin-btn--secondary" href="<?= e(url('blog-detail.php?slug=' . rawurlencode($post['slug']))) ?>" target="_blank" rel="noopener">
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
                        <label for="f_category">Category</label>
                        <input class="admin-input" type="text" id="f_category" name="category"
                               value="<?= e($post['category']) ?>" list="category-suggest" maxlength="100">
                        <datalist id="category-suggest">
                            <option value="FinTech Architecture">
                            <option value="AI &amp; Autonomous Agents">
                            <option value="Cloud Engineering">
                            <option value="Technical Oversight">
                            <option value="Engineering">
                            <option value="DevOps">
                            <option value="Security">
                        </datalist>
                    </div>
                    <div class="admin-field">
                        <label for="f_read_time">Read time</label>
                        <input class="admin-input" type="text" id="f_read_time" name="read_time"
                               value="<?= e($post['read_time']) ?>" placeholder="5 min read" maxlength="50">
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-head"><h2>Cover image</h2></div>
                <div class="admin-card-body">
                    <div class="admin-field">
                        <label for="f_image">Image path or URL</label>
                        <input class="admin-input" type="text" id="f_image" name="image"
                               value="<?= e($post['image']) ?>" placeholder="assets/img/blog/5.jpg">
                        <?php if (!empty($errors['image'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['image']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-field">
                        <label>Or upload an image</label>
                        <div class="admin-drop" id="imageDrop">
                            <i class="ri-upload-cloud-2-line"></i>
                            Drop an image here or click to browse
                            <input type="file" id="f_image_file" name="image_file" accept="image/*">
                        </div>
                        <?php if (!empty($errors['image_file'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['image_file']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($post['image'])): ?>
                            <img class="admin-img-preview" id="imgPreview" src="<?= e($post['image']) ?>" alt="" style="display:block;">
                        <?php else: ?>
                            <img class="admin-img-preview" id="imgPreview" src="" alt="">
                        <?php endif; ?>
                        <div class="admin-hint">JPG, PNG, WEBP, GIF or SVG &middot; max 5 MB.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- TinyMCE 8. API key registered for this site. Loaded only on this page. -->
<script>window.JMN_TINYMCE = true;</script>
<script src="https://cdn.tiny.cloud/1/v0xlnluysr3rgxaap7agv1l90tlfnu2zwu8rve04fubvv7lw/tinymce/8/tinymce.min.js" referrerpolicy="origin"></script>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>

