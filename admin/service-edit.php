<?php
declare(strict_types=1);

/**
 * Admin Service Editor — create or edit a service offering.
 */

require_once __DIR__ . '/includes/auth.php';
admin_require_auth();
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/cache.php';
require_once __DIR__ . '/includes/helpers.php';

$db = get_db();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$isNew = $id <= 0;

$service = [
    'id' => 0, 'slug' => '', 'title' => '', 'tagline' => '', 'bg_color' => '',
    'icon' => '', 'summary' => '', 'description' => '', 'deliverables' => [], 'sort_order' => 0,
];

if (!$isNew && $db !== null) {
    try {
        $stmt = $db->prepare('SELECT * FROM `services` WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            $_SESSION['flash_err'] = 'Service not found.';
            header('Location: services.php');
            exit;
        }
        $service = [
            'id'           => (int)$row['id'],
            'slug'         => (string)$row['slug'],
            'title'        => (string)$row['title'],
            'tagline'      => (string)($row['tagline'] ?? ''),
            'bg_color'     => (string)($row['bg_color'] ?? ''),
            'icon'         => (string)($row['icon'] ?? ''),
            'summary'      => (string)$row['summary'],
            'description'  => (string)$row['description'],
            'deliverables' => json_decode($row['deliverables'] ?? '[]', true) ?: [],
            'sort_order'   => (int)$row['sort_order'],
        ];
    } catch (Throwable $e) {
        error_log('Service edit load failed: ' . $e->getMessage());
        $_SESSION['flash_err'] = 'Could not load the service.';
        header('Location: services.php');
        exit;
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !verify_csrf_token($token)) {
        $_SESSION['flash_err'] = 'Your security token has expired. Please try again.';
        header('Location: service-edit.php' . ($id > 0 ? '?id=' . $id : ''));
        exit;
    }
    if (!check_rate_limit('admin_service_save', 2)) {
        $_SESSION['flash_err'] = 'Please wait a moment before saving again.';
        header('Location: service-edit.php' . ($id > 0 ? '?id=' . $id : ''));
        exit;
    }

    $f = static function (string $key, string $default = ''): string {
        $v = $_POST[$key] ?? $default;
        return is_string($v) ? trim($v) : $default;
    };

    $title        = $f('title');
    $slug         = $f('slug');
    $tagline      = $f('tagline');
    $bgColor      = $f('bg_color');
    $icon         = $f('icon');
    $summary      = $f('summary');
    $description  = $f('description');
    $deliverables = admin_lines_to_list($f('deliverables'));
    $sortOrder    = (int)$f('sort_order', '0');

    if (mb_strlen($title) < 3) { $errors['title'] = 'Title must be at least 3 characters.'; }
    if ($summary === '') { $errors['summary'] = 'Summary is required.'; }
    if ($description === '') { $errors['description'] = 'Description is required.'; }

    if ($slug === '') {
        $slug = slugify($title);
    } else {
        $slug = slugify($slug);
    }
    if ($slug === '') { $errors['slug'] = 'Could not derive a valid slug from the title.'; }

    // Icon upload (optional).
    if (!empty($_FILES['icon_file']['name']) && (int)($_FILES['icon_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $uploaded = admin_handle_image_upload($_FILES['icon_file'], 'services/uploads');
        if (is_array($uploaded) && isset($uploaded['error'])) {
            $errors['icon_file'] = $uploaded['error'];
        } elseif (is_string($uploaded) && $uploaded !== '') {
            $icon = $uploaded;
        }
    }

    // Slug uniqueness (auto-suffix).
    if ($db !== null && $slug !== '' && empty($errors['slug'])) {
        try {
            $sql = 'SELECT COUNT(*) FROM `services` WHERE `slug` = :slug';
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
                    $stmt = $db->prepare("SELECT COUNT(*) FROM `services` WHERE `slug` = :slug AND (`id` = 0" . (!$isNew ? " OR `id` != :id" : "") . ")");
                    $p = [':slug' => $slug];
                    if (!$isNew) { $p[':id'] = $id; }
                    $stmt->execute($p);
                    $n++;
                } while ((int)$stmt->fetchColumn() > 0 && $n < 50);
            }
        } catch (Throwable $e) {
            error_log('Service slug uniqueness check failed: ' . $e->getMessage());
        }
    }

    if ($errors) {
        $service['title']       = $title;
        $service['slug']        = $slug;
        $service['tagline']     = $tagline;
        $service['bg_color']    = $bgColor;
        $service['icon']        = $icon;
        $service['summary']     = $summary;
        $service['description'] = $description;
        $service['deliverables'] = $deliverables;
        $service['sort_order']  = $sortOrder;
        $_SESSION['flash_err'] = 'Please correct the errors below and try again.';
    } elseif ($db === null) {
        $_SESSION['flash_err'] = 'Database unavailable; the service was not saved.';
        header('Location: services.php');
        exit;
    } else {
        try {
            $jsonDeliverables = json_encode($deliverables, JSON_UNESCAPED_UNICODE);
            if ($isNew) {
                $stmt = $db->prepare("
                    INSERT INTO `services` (`slug`,`title`,`tagline`,`bg_color`,`icon`,`summary`,`description`,`deliverables`,`sort_order`)
                    VALUES (:slug,:title,:tagline,:bg_color,:icon,:summary,:description,:deliverables,:sort_order)
                ");
                $stmt->execute([
                    ':slug' => $slug, ':title' => mb_substr($title, 0, 255),
                    ':tagline' => $tagline !== '' ? mb_substr($tagline, 0, 255) : null,
                    ':bg_color' => $bgColor !== '' ? mb_substr($bgColor, 0, 20) : null,
                    ':icon' => $icon !== '' ? mb_substr($icon, 0, 255) : null,
                    ':summary' => $summary, ':description' => $description,
                    ':deliverables' => $jsonDeliverables, ':sort_order' => $sortOrder,
                ]);
                $id = (int)$db->lastInsertId();
            } else {
                $stmt = $db->prepare("
                    UPDATE `services` SET
                        `slug` = :slug, `title` = :title, `tagline` = :tagline,
                        `bg_color` = :bg_color, `icon` = :icon, `summary` = :summary,
                        `description` = :description, `deliverables` = :deliverables,
                        `sort_order` = :sort_order
                    WHERE `id` = :id
                ");
                $stmt->execute([
                    ':slug' => $slug, ':title' => mb_substr($title, 0, 255),
                    ':tagline' => $tagline !== '' ? mb_substr($tagline, 0, 255) : null,
                    ':bg_color' => $bgColor !== '' ? mb_substr($bgColor, 0, 20) : null,
                    ':icon' => $icon !== '' ? mb_substr($icon, 0, 255) : null,
                    ':summary' => $summary, ':description' => $description,
                    ':deliverables' => $jsonDeliverables, ':sort_order' => $sortOrder,
                    ':id' => $id,
                ]);
            }

            $_SESSION['flash_ok'] = $isNew
                ? 'Service created (id #' . $id . ').'
                : 'Service updated.';
            cache_clear(); // content changed — drop cached lists so the public site updates immediately
            rotate_csrf_token();
            header('Location: services.php');
            exit;
        } catch (Throwable $e) {
            error_log('Service save failed: ' . $e->getMessage());
            $_SESSION['flash_err'] = 'Database error while saving: ' . $e->getMessage();
        }
    }
}

$deliverablesText = implode("\n", $service['deliverables']);

$csrf = generate_csrf_token();
$adminTitle = $isNew ? 'New Service' : 'Edit: ' . $service['title'];
require_once __DIR__ . '/includes/layout-header.php';
?>

<div class="admin-filters">
    <a class="admin-chip" href="services.php"><i class="ri-arrow-left-s-line"></i> Back to services</a>
    <span class="admin-chip is-active"><?= $isNew ? 'New service' : 'Editing' ?></span>
</div>

<form method="post" action="service-edit.php<?= $isNew ? '' : '?id=' . $id ?>" enctype="multipart/form-data" id="serviceForm">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="admin-detail-grid">
        <div>
            <div class="admin-card">
                <div class="admin-card-head"><h2>Content</h2></div>
                <div class="admin-card-body">
                    <div class="admin-field">
                        <label for="f_title">Title *</label>
                        <input class="admin-input" type="text" id="f_title" name="title"
                               value="<?= e($service['title']) ?>" required maxlength="255">
                        <?php if (!empty($errors['title'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['title']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-field">
                        <label for="f_slug">Slug *</label>
                        <div class="admin-slug-row">
                            <input class="admin-input" type="text" id="f_slug" name="slug"
                                   value="<?= e($service['slug']) ?>" placeholder="auto-generated-from-title">
                            <button type="button" class="admin-slug-lock" id="slug_lock"
                                    data-initial-locked="<?= $isNew ? '0' : '1' ?>"
                                    title="Lock / unlock auto-generation">
                                <i class="ri-lock-line"></i>
                            </button>
                        </div>
                        <?php if (!empty($errors['slug'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['slug']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-field">
                        <label for="f_tagline">Tagline</label>
                        <input class="admin-input" type="text" id="f_tagline" name="tagline"
                               value="<?= e($service['tagline']) ?>" maxlength="255">
                    </div>
                    <div class="admin-field">
                        <label for="f_summary">Summary *</label>
                        <textarea class="admin-textarea" id="f_summary" name="summary" rows="3" required><?= e($service['summary']) ?></textarea>
                        <?php if (!empty($errors['summary'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['summary']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-field">
                        <label for="f_description">Description *</label>
                        <textarea class="admin-textarea" id="f_description" name="description" rows="4" required><?= e($service['description']) ?></textarea>
                        <?php if (!empty($errors['description'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['description']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-field">
                        <label for="f_deliverables">Deliverables <span style="font-weight:400;color:var(--a-muted);">(one per line)</span></label>
                        <textarea class="admin-textarea" id="f_deliverables" name="deliverables" rows="5"><?= e($deliverablesText) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="admin-card">
                <div class="admin-card-head"><h2>Publish</h2></div>
                <div class="admin-card-body">
                    <div class="admin-field">
                        <label for="f_sort_order">Sort order</label>
                        <input class="admin-input" type="number" id="f_sort_order" name="sort_order"
                               value="<?= (int)$service['sort_order'] ?>" step="10">
                        <div class="admin-hint">Lower numbers appear first.</div>
                    </div>
                    <div class="admin-form-actions">
                        <button class="admin-btn admin-btn--primary" type="submit">
                            <i class="ri-save-3-fill"></i> <?= $isNew ? 'Create service' : 'Save changes' ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-head"><h2>Appearance</h2></div>
                <div class="admin-card-body">
                    <div class="admin-field">
                        <label for="f_bg_color">Background colour</label>
                        <input class="admin-input" type="text" id="f_bg_color" name="bg_color"
                               value="<?= e($service['bg_color']) ?>" placeholder="#edf4b3" maxlength="20">
                    </div>
                    <div class="admin-field">
                        <label for="f_icon">Icon path or URL</label>
                        <input class="admin-input" type="text" id="f_icon" name="icon"
                               value="<?= e($service['icon']) ?>" placeholder="img/services/1.png">
                        <?php if (!empty($errors['icon_file'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['icon_file']) ?></div>
                        <?php endif; ?>
                        <div class="admin-field" style="margin-top:8px;">
                            <label>Or upload an icon</label>
                            <div class="admin-drop" id="imageDrop">
                                <i class="ri-upload-cloud-2-line"></i>
                                Drop an image here or click to browse
                                <input type="file" id="f_icon_file" name="icon_file" accept="image/*">
                            </div>
                            <?php if ($service['icon'] !== ''): ?>
                                <img class="admin-img-preview" id="imgPreview" src="<?= e($service['icon']) ?>" alt="" style="display:block;">
                            <?php else: ?>
                                <img class="admin-img-preview" id="imgPreview" src="" alt="">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>
