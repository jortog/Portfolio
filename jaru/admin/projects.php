<?php
// ============================================================
// admin/projects.php — Projects CRUD
// ============================================================
define('ADMIN_VIEW', true);
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

Auth::requireAdmin();

$errors = [];
$editing = null;

// ── DELETE ────────────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $proj = DB::row('SELECT image FROM projects WHERE id = ?', [(int)$_GET['delete']]);
    if ($proj && $proj['image']) {
        $img = ROOT_PATH . '/public/images/projects/' . $proj['image'];
        if (file_exists($img)) unlink($img);
    }
    DB::query('DELETE FROM projects WHERE id = ?', [(int)$_GET['delete']]);
    flash('success', 'Project deleted.');
    redirect('/admin/projects.php');
}

// ── TOGGLE FEATURED ───────────────────────────────────────────
if (isset($_GET['toggle_featured']) && is_numeric($_GET['toggle_featured'])) {
    $curr = DB::row('SELECT is_featured FROM projects WHERE id = ?', [(int)$_GET['toggle_featured']]);
    if ($curr) {
        DB::query('UPDATE projects SET is_featured = ? WHERE id = ?', [$curr['is_featured'] ? 0 : 1, (int)$_GET['toggle_featured']]);
    }
    redirect('/admin/projects.php');
}

// ── LOAD FOR EDIT ─────────────────────────────────────────────
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing = DB::row('SELECT * FROM projects WHERE id = ?', [(int)$_GET['edit']]);
}

// ── SAVE (Create or Update) ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['create', 'update'])) {
    Auth::verifyCsrf();

    $title       = clean($_POST['title']       ?? '');
    $description = clean($_POST['description'] ?? '');
    $tech_stack  = clean($_POST['tech_stack']  ?? '');
    $project_url = clean($_POST['project_url'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $sort_order  = (int)($_POST['sort_order']  ?? 0);
    $action      = $_POST['action'];
    $projId      = (int)($_POST['project_id'] ?? 0);

    if (!$title)                 $errors[] = 'Title is required.';
    if (!$description)           $errors[] = 'Description is required.';
    if ($project_url && !filter_var($project_url, FILTER_VALIDATE_URL)) $errors[] = 'Invalid project URL.';

    // Handle image upload
    $imageFile = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file    = $_FILES['image'];
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file['type'], $allowed)) {
            $errors[] = 'Invalid image type.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Image too large (max 5MB).';
        } else {
            $dir = ROOT_PATH . '/public/images/projects/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
            $imageFile = 'proj_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            move_uploaded_file($file['tmp_name'], $dir . $imageFile);
        }
    }

    if (!$errors) {
        if ($action === 'create') {
            DB::insert(
                'INSERT INTO projects (title, description, tech_stack, image, project_url, is_featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$title, $description, $tech_stack, $imageFile, $project_url, $is_featured, $sort_order]
            );
            flash('success', 'Project created!');
        } else {
            $old = DB::row('SELECT image FROM projects WHERE id = ?', [$projId]);
            // Delete old image if replaced
            if ($imageFile && $old && $old['image']) {
                $oldPath = ROOT_PATH . '/public/images/projects/' . $old['image'];
                if (file_exists($oldPath)) unlink($oldPath);
            }
            DB::query(
                'UPDATE projects SET title=?, description=?, tech_stack=?, project_url=?, is_featured=?, sort_order=?' .
                ($imageFile ? ', image=?' : '') . ' WHERE id=?',
                $imageFile
                    ? [$title, $description, $tech_stack, $project_url, $is_featured, $sort_order, $imageFile, $projId]
                    : [$title, $description, $tech_stack, $project_url, $is_featured, $sort_order, $projId]
            );
            flash('success', 'Project updated!');
        }
        redirect('/admin/projects.php');
    } else {
        // Re-load editing state on error
        if ($action === 'update') $editing = DB::row('SELECT * FROM projects WHERE id = ?', [$projId]);
    }
}

$projects  = DB::rows('SELECT * FROM projects ORDER BY sort_order ASC, created_at DESC');
$pageTitle = 'Projects — Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="section visible" style="min-height:90vh;">
    <div class="section-label">Portfolio Projects</div>

    <?php if ($errors): ?>
    <div class="auth-errors" style="margin-bottom:1.5rem;">
        <?php foreach ($errors as $e): ?><p>⚠ <?= e($e) ?></p><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="projects-admin-layout">

        <!-- FORM (Create / Edit) -->
        <div class="admin-panel">
            <div class="admin-panel-title"><?= $editing ? 'Edit Project' : 'Add New Project' ?></div>
            <form method="POST" enctype="multipart/form-data" class="auth-form" style="margin-top:1.5rem;">
                <?= Auth::csrfField() ?>
                <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
                <?php if ($editing): ?>
                <input type="hidden" name="project_id" value="<?= $editing['id'] ?>">
                <?php endif; ?>

                <div class="auth-field">
                    <label>Title *</label>
                    <input type="text" name="title" class="auth-input"
                           value="<?= e($editing['title'] ?? '') ?>" required>
                </div>
                <div class="auth-field">
                    <label>Description *</label>
                    <textarea name="description" class="auth-input" rows="4" required><?= e($editing['description'] ?? '') ?></textarea>
                </div>
                <div class="auth-field">
                    <label>Tech Stack <small>(comma separated)</small></label>
                    <input type="text" name="tech_stack" class="auth-input"
                           value="<?= e($editing['tech_stack'] ?? '') ?>"
                           placeholder="PHP, MySQL, JS">
                </div>
                <div class="auth-field">
                    <label>Project URL</label>
                    <input type="url" name="project_url" class="auth-input"
                           value="<?= e($editing['project_url'] ?? '') ?>"
                           placeholder="https://github.com/...">
                </div>
                <div class="auth-field">
                    <label>Project Image <small>(JPG/PNG/WEBP, max 5MB)</small></label>
                    <?php if ($editing && $editing['image']): ?>
                    <img src="<?= BASE_URL ?>/public/images/projects/<?= e($editing['image']) ?>"
                         alt="Current" style="width:100%;height:120px;object-fit:cover;margin-bottom:.5rem;">
                    <?php endif; ?>
                    <input type="file" name="image" class="auth-input" accept="image/*">
                </div>
                <div class="auth-field" style="display:flex;gap:2rem;align-items:center;">
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                        <input type="checkbox" name="is_featured" value="1"
                               <?= ($editing['is_featured'] ?? 0) ? 'checked' : '' ?>>
                        Featured on homepage
                    </label>
                    <div style="flex:1;">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" class="auth-input" style="width:80px;"
                               value="<?= e($editing['sort_order'] ?? 0) ?>">
                    </div>
                </div>
                <button type="submit" class="ask-btn" style="width:100%;margin-top:1rem;">
                    <span><?= $editing ? '✓ Update Project' : '+ Create Project' ?></span>
                </button>
                <?php if ($editing): ?>
                <a href="<?= BASE_URL ?>/admin/projects.php" style="display:block;text-align:center;margin-top:.8rem;font-size:.75rem;color:var(--muted);">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- TABLE -->
        <div>
            <table class="admin-table">
                <thead><tr><th>Title</th><th>Stack</th><th>Featured</th><th>Order</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($projects as $p): ?>
                <tr>
                    <td>
                        <?php if ($p['image']): ?>
                        <img src="<?= BASE_URL ?>/public/images/projects/<?= e($p['image']) ?>"
                             alt="" style="width:40px;height:40px;object-fit:cover;margin-right:.5rem;vertical-align:middle;">
                        <?php endif; ?>
                        <?= e($p['title']) ?>
                    </td>
                    <td><small><?= e($p['tech_stack'] ?? '—') ?></small></td>
                    <td>
                        <a href="?toggle_featured=<?= $p['id'] ?>">
                            <?= $p['is_featured'] ? '⭐' : '☆' ?>
                        </a>
                    </td>
                    <td><?= $p['sort_order'] ?></td>
                    <td class="admin-actions">
                        <a href="?edit=<?= $p['id'] ?>">✎ Edit</a>
                        <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Delete project?')" style="color:#ff4444;">✕</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($projects)): ?>
                <tr><td colspan="5" style="padding:1rem;color:var(--muted);">No projects yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
