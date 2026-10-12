<?php
declare(strict_types=1);

/**
 * Admin Skill Editor — create or edit a skills-matrix entry.
 */

require_once __DIR__ . '/includes/auth.php';
admin_require_auth();
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/cache.php';
require_once __DIR__ . '/includes/helpers.php';

$db = get_db();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$isNew = $id <= 0;

$item = ['id' => 0, 'slug' => '', 'category' => '', 'percentage' => 90, 'summary' => '', 'badges' => [], 'sort_order' => 0];

if (!$isNew && $db !== null) {
    try {
        $stmt = $db->prepare('SELECT * FROM `skills` WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            $_SESSION['flash_err'] = 'Skill not found.';
            header('Location: skills.php');
            exit;
        }
        $item = [
            'id'         => (int)$row['id'],
            'slug'       => (string)$row['slug'],
            'category'   => (string)$row['category'],
            'percentage' => (int)$row['percentage'],
            'summary'    => (string)$row['summary'],
            'badges'     => json_decode($row['badges'] ?? '[]', true) ?: [],
            'sort_order' => (int)$row['sort_order'],
        ];
    } catch (Throwable $e) {
        error_log('Skill edit load failed: ' . $e->getMessage());
        $_SESSION['flash_err'] = 'Could not load the skill.';
        header('Location: skills.php');
        exit;
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !verify_csrf_token($token)) {
        $_SESSION['flash_err'] = 'Your security token has expired. Please try again.';
        header('Location: skill-edit.php' . ($id > 0 ? '?id=' . $id : ''));
        exit;
    }
    if (!check_rate_limit('admin_skill_save', 2)) {
        $_SESSION['flash_err'] = 'Please wait a moment before saving again.';
        header('Location: skill-edit.php' . ($id > 0 ? '?id=' . $id : ''));
        exit;
    }

    $f = static function (string $key, string $default = ''): string {
        $v = $_POST[$key] ?? $default;
        return is_string($v) ? trim($v) : $default;
    };

    $slug       = $f('slug');
    $category   = $f('category');
    $percentage = (int)$f('percentage', '0');
    $summary    = $f('summary');
    $badges     = admin_lines_to_list($f('badges'));
    $sort       = (int)$f('sort_order', '0');

    if ($category === '') { $errors['category'] = 'Category is required.'; }
    if ($percentage < 0 || $percentage > 100) { $errors['percentage'] = 'Percentage must be between 0 and 100.'; }
    if ($summary === '') { $errors['summary'] = 'Summary is required.'; }

    if ($slug === '') {
        $slug = slugify($category);
    } else {
        $slug = slugify($slug);
    }
    if ($slug === '') { $errors['slug'] = 'Could not derive a valid slug from the category.'; }

    // Slug uniqueness (auto-suffix).
    if ($db !== null && $slug !== '' && empty($errors['slug'])) {
        try {
            $sql = 'SELECT COUNT(*) FROM `skills` WHERE `slug` = :slug';
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
                    $stmt = $db->prepare("SELECT COUNT(*) FROM `skills` WHERE `slug` = :slug AND (`id` = 0" . (!$isNew ? " OR `id` != :id" : "") . ")");
                    $p = [':slug' => $slug];
                    if (!$isNew) { $p[':id'] = $id; }
                    $stmt->execute($p);
                    $n++;
                } while ((int)$stmt->fetchColumn() > 0 && $n < 50);
            }
        } catch (Throwable $e) {
            error_log('Skill slug uniqueness check failed: ' . $e->getMessage());
        }
    }

    if ($errors) {
        $item['slug'] = $slug; $item['category'] = $category; $item['percentage'] = $percentage;
        $item['summary'] = $summary; $item['badges'] = $badges; $item['sort_order'] = $sort;
        $_SESSION['flash_err'] = 'Please correct the errors below and try again.';
    } elseif ($db === null) {
        $_SESSION['flash_err'] = 'Database unavailable; the skill was not saved.';
        header('Location: skills.php');
        exit;
    } else {
        try {
            $jsonBadges = json_encode($badges, JSON_UNESCAPED_UNICODE);
            if ($isNew) {
                $stmt = $db->prepare("
                    INSERT INTO `skills` (`slug`,`category`,`percentage`,`summary`,`badges`,`sort_order`)
                    VALUES (:slug,:category,:percentage,:summary,:badges,:sort_order)
                ");
                $stmt->execute([
                    ':slug' => $slug, ':category' => mb_substr($category, 0, 150),
                    ':percentage' => $percentage, ':summary' => $summary,
                    ':badges' => $jsonBadges, ':sort_order' => $sort,
                ]);
                $id = (int)$db->lastInsertId();
            } else {
                $stmt = $db->prepare("
                    UPDATE `skills` SET
                        `slug` = :slug, `category` = :category, `percentage` = :percentage,
                        `summary` = :summary, `badges` = :badges, `sort_order` = :sort_order
                    WHERE `id` = :id
                ");
                $stmt->execute([
                    ':slug' => $slug, ':category' => mb_substr($category, 0, 150),
                    ':percentage' => $percentage, ':summary' => $summary,
                    ':badges' => $jsonBadges, ':sort_order' => $sort, ':id' => $id,
                ]);
            }

            $_SESSION['flash_ok'] = $isNew
                ? 'Skill created (id #' . $id . ').'
                : 'Skill updated.';
            cache_clear(); // content changed — drop cached lists so the public site updates immediately
            rotate_csrf_token();
            header('Location: skills.php');
            exit;
        } catch (Throwable $e) {
            error_log('Skill save failed: ' . $e->getMessage());
            $_SESSION['flash_err'] = 'Database error while saving: ' . $e->getMessage();
        }
    }
}

$badgesText = implode("\n", $item['badges']);

$csrf = generate_csrf_token();
$adminTitle = $isNew ? 'New Skill' : 'Edit: ' . $item['category'];
require_once __DIR__ . '/includes/layout-header.php';
?>

<div class="admin-filters">
    <a class="admin-chip" href="skills.php"><i class="ri-arrow-left-s-line"></i> Back to skills</a>
    <span class="admin-chip is-active"><?= $isNew ? 'New skill' : 'Editing' ?></span>
</div>

<form method="post" action="skill-edit.php<?= $isNew ? '' : '?id=' . $id ?>" id="skillForm">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="admin-detail-grid">
        <div>
            <div class="admin-card">
                <div class="admin-card-head"><h2>Content</h2></div>
                <div class="admin-card-body">
                    <div class="admin-field">
                        <label for="f_category">Category *</label>
                        <input class="admin-input" type="text" id="f_category" name="category"
                               value="<?= e($item['category']) ?>" required maxlength="150">
                        <?php if (!empty($errors['category'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['category']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-field">
                        <label for="f_slug">Slug *</label>
                        <div class="admin-slug-row">
                            <input class="admin-input" type="text" id="f_slug" name="slug"
                                   value="<?= e($item['slug']) ?>" placeholder="auto-generated-from-category">
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
                        <label for="f_summary">Summary *</label>
                        <textarea class="admin-textarea" id="f_summary" name="summary" rows="3" required><?= e($item['summary']) ?></textarea>
                        <?php if (!empty($errors['summary'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['summary']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-field">
                        <label for="f_badges">Badges <span style="font-weight:400;color:var(--a-muted);">(one per line)</span></label>
                        <textarea class="admin-textarea" id="f_badges" name="badges" rows="5"><?= e($badgesText) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="admin-card">
                <div class="admin-card-head"><h2>Level</h2></div>
                <div class="admin-card-body">
                    <div class="admin-field">
                        <label for="f_percentage">Percentage</label>
                        <input class="admin-input" type="number" id="f_percentage" name="percentage"
                               value="<?= (int)$item['percentage'] ?>" min="0" max="100">
                        <?php if (!empty($errors['percentage'])): ?>
                            <div class="admin-hint" style="color:#b42318;"><?= e($errors['percentage']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-field">
                        <label for="f_sort_order">Sort order</label>
                        <input class="admin-input" type="number" id="f_sort_order" name="sort_order"
                               value="<?= (int)$item['sort_order'] ?>" step="10">
                    </div>
                    <div class="admin-form-actions">
                        <button class="admin-btn admin-btn--primary" type="submit">
                            <i class="ri-save-3-fill"></i> <?= $isNew ? 'Create skill' : 'Save changes' ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>
