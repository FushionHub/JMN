<?php
declare(strict_types=1);

/**
 * Admin Testimonial Editor — create or edit a client endorsement.
 */

require_once __DIR__ . '/includes/auth.php';
admin_require_auth();
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/cache.php';
require_once __DIR__ . '/includes/helpers.php';

$db = get_db();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$isNew = $id <= 0;

$item = ['id' => 0, 'name' => '', 'role' => '', 'company' => '', 'image' => '', 'content' => '', 'sort_order' => 0];

if (!$isNew && $db !== null) {
    try {
        $stmt = $db->prepare('SELECT * FROM `testimonials` WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            $_SESSION['flash_err'] = 'Testimonial not found.';
            header('Location: testimonials.php');
            exit;
        }
        $item = [
            'id'         => (int)$row['id'],
            'name'       => (string)$row['name'],
            'role'       => (string)($row['role'] ?? ''),
            'company'    => (string)($row['company'] ?? ''),
            'image'      => (string)($row['image'] ?? ''),
            'content'    => (string)$row['content'],
            'sort_order' => (int)$row['sort_order'],
        ];
    } catch (Throwable $e) {
        error_log('Testimonial edit load failed: ' . $e->getMessage());
        $_SESSION['flash_err'] = 'Could not load the testimonial.';
        header('Location: testimonials.php');
        exit;
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !verify_csrf_token($token)) {
        $_SESSION['flash_err'] = 'Your security token has expired. Please try again.';
        header('Location: testimonial-edit.php' . ($id > 0 ? '?id=' . $id : ''));
        exit;
    }
    if (!check_rate_limit('admin_testimonial_save', 2)) {
        $_SESSION['flash_err'] = 'Please wait a moment before saving again.';
        header('Location: testimonial-edit.php' . ($id > 0 ? '?id=' . $id : ''));
        exit;
    }

    $f = static function (string $key, string $default = ''): string {
        $v = $_POST[$key] ?? $default;
        return is_string($v) ? trim($v) : $default;
    };

    $name    = $f('name');
    $role    = $f('role');
    $company = $f('company');
    $image   = $f('image');
    $content = $f('content');
    $sort    = (int)$f('sort_order', '0');

    if ($name === '') { $errors['name'] = 'Name is required.'; }
    if (mb_strlen($content) < 10) { $errors['content'] = 'Content must be at least 10 characters.'; }

    if (!empty($_FILES['image_file']['name']) && (int)($_FILES['image_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $uploaded = admin_handle_image_upload($_FILES['image_file'], 'testimonials/uploads');
        if (is_array($uploaded) && isset($uploaded['error'])) {
            $errors['image_file'] = $uploaded['error'];
        } elseif (is_string($uploaded) && $uploaded !== '') {
            $image = $uploaded;
        }
    }

    if ($errors) {
        $item['name'] = $name; $item['role'] = $role; $item['company'] = $company;
        $item['image'] = $image; $item['content'] = $content; $item['sort_order'] = $sort;
        $_SESSION['flash_err'] = 'Please correct the errors below and try again.';
    } elseif ($db === null) {
        $_SESSION['flash_err'] = 'Database unavailable; the testimonial was not saved.';
        header('Location: testimonials.php');
        exit;
    } else {
        try {
            if ($isNew) {
                $stmt = $db->prepare("
                    INSERT INTO `testimonials` (`name`,`role`,`company`,`image`,`content`,`sort_order`)
                    VALUES (:name,:role,:company,:image,:content,:sort_order)
                ");
                $stmt->execute([
                    ':name' => mb_substr($name, 0, 150),
                    ':role' => $role !== '' ? mb_substr($role, 0, 150) : null,
                    ':company' => $company !== '' ? mb_substr($company, 0, 150) : null,
                    ':image' => $image !== '' ? mb_substr($image, 0, 255) : null,
                    ':content' => $content, ':sort_order' => $sort,
                ]);
                $id = (int)$db->lastInsertId();
            } else {
                $stmt = $db->prepare("
                    UPDATE `testimonials` SET
                        `name` = :name, `role` = :role, `company` = :company,
                        `image` = :image, `content` = :content, `sort_order` = :sort_order
                    WHERE `id` = :id
                ");
                $stmt->execute([
                    ':name' => mb_substr($name, 0, 150),
                    ':role' => $role !== '' ? mb_substr($role, 0, 150) : null,
                    ':company' => $company !== '' ? mb_substr($company, 0, 150) : null,
                    ':image' => $image !== '' ? mb_substr($image, 0, 255) : null,
                    ':content' => $content, ':sort_order' => $sort, ':id' => $id,
                ]);
            }

            $_SESSION['flash_ok'] = $isNew
                ? 'Testimonial created (id #' . $id . ').'
                : 'Testimonial updated.';
            cache_clear(); // content changed — drop cached lists so the public site updates immediately
            rotate_csrf_token();
            header('Location: testimonials.php');
            exit;
        } catch (Throwable $e) {
            error_log('Testimonial save failed: ' . $e->getMessage());
            $_SESSION['flash_err'] = 'Database error while saving: ' . $e->getMessage();
        }
    }
}

$csrf = generate_csrf_token();
$adminTitle = $isNew ? 'New Testimonial' : 'Edit: ' . $item['name'];
require_once __DIR__ . '/includes/layout-header.php';
?>

<div class="admin-filters">
    <a class="admin-chip" href="testimonials.php"><i class="ri-arrow-left-s-line"></i> Back to testimonials</a>
    <span class="admin-chip is-active"><?= $isNew ? 'New testimonial' : 'Editing' ?></span>
</div>

<form method="post" action="testimonial-edit.php<?= $isNew ? '' : '?id=' . $id ?>" enctype="multipart/form-data" id="testimonialForm">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="admin-detail-grid">
        <div>
            <div class="admin-card">
                <div class="admin-card-head"><h2>Content</h2></div>
                <div class="admin-card-body">
                    <div class="admin-field">
                        <label for="f_content">Quote *</label>
                        <textarea class="admin-textarea" id="f_content" name="content" rows="6" required><?= e($item['content']) ?></textarea>
                        <?php if (!empty($errors['content'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['content']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="admin-card">
                <div class="admin-card-head"><h2>Attribution</h2></div>
                <div class="admin-card-body">
                    <div class="admin-field">
                        <label for="f_name">Name *</label>
                        <input class="admin-input" type="text" id="f_name" name="name"
                               value="<?= e($item['name']) ?>" required maxlength="150">
                        <?php if (!empty($errors['name'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['name']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-field">
                        <label for="f_role">Role</label>
                        <input class="admin-input" type="text" id="f_role" name="role"
                               value="<?= e($item['role']) ?>" maxlength="150">
                    </div>
                    <div class="admin-field">
                        <label for="f_company">Company</label>
                        <input class="admin-input" type="text" id="f_company" name="company"
                               value="<?= e($item['company']) ?>" maxlength="150">
                    </div>
                    <div class="admin-field">
                        <label for="f_sort_order">Sort order</label>
                        <input class="admin-input" type="number" id="f_sort_order" name="sort_order"
                               value="<?= (int)$item['sort_order'] ?>" step="10">
                    </div>
                    <div class="admin-field">
                        <label for="f_image">Photo path or URL</label>
                        <input class="admin-input" type="text" id="f_image" name="image"
                               value="<?= e($item['image']) ?>" placeholder="assets/img/testimonials/1.jpg">
                        <?php if (!empty($errors['image_file'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['image_file']) ?></div>
                        <?php endif; ?>
                        <div class="admin-field" style="margin-top:8px;">
                            <label>Or upload a photo</label>
                            <div class="admin-drop" id="imageDrop">
                                <i class="ri-upload-cloud-2-line"></i>
                                Drop an image here or click to browse
                                <input type="file" id="f_image_file" name="image_file" accept="image/*">
                            </div>
                            <?php if ($item['image'] !== ''): ?>
                                <img class="admin-img-preview" id="imgPreview" src="<?= e($item['image']) ?>" alt="" style="display:block;">
                            <?php else: ?>
                                <img class="admin-img-preview" id="imgPreview" src="" alt="">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="admin-form-actions">
                        <button class="admin-btn admin-btn--primary" type="submit">
                            <i class="ri-save-3-fill"></i> <?= $isNew ? 'Create testimonial' : 'Save changes' ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>
