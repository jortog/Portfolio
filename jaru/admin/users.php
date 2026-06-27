<?php
// ============================================================
// admin/users.php — User Management CRUD
// ============================================================
define('ADMIN_VIEW', true);
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

Auth::requireAdmin();

$errors = [];

// ── DELETE ────────────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $uid = (int)$_GET['delete'];
    if ($uid === Auth::id()) {
        flash('error', "You can't delete yourself.");
    } else {
        DB::query('DELETE FROM users WHERE id = ?', [$uid]);
        flash('success', 'User deleted.');
    }
    redirect('/admin/users.php');
}

// ── TOGGLE ROLE ───────────────────────────────────────────────
if (isset($_GET['toggle_role']) && is_numeric($_GET['toggle_role'])) {
    $uid  = (int)$_GET['toggle_role'];
    $curr = DB::row('SELECT role FROM users WHERE id = ?', [$uid]);
    if ($curr) {
        $newRole = $curr['role'] === 'admin' ? 'user' : 'admin';
        DB::query('UPDATE users SET role = ? WHERE id = ?', [$newRole, $uid]);
        flash('success', 'Role updated to ' . $newRole . '.');
    }
    redirect('/admin/users.php');
}

// ── TOGGLE ACTIVE ─────────────────────────────────────────────
if (isset($_GET['toggle_active']) && is_numeric($_GET['toggle_active'])) {
    $uid  = (int)$_GET['toggle_active'];
    $curr = DB::row('SELECT is_active FROM users WHERE id = ?', [$uid]);
    if ($curr) {
        DB::query('UPDATE users SET is_active = ? WHERE id = ?', [$curr['is_active'] ? 0 : 1, $uid]);
        flash('success', 'User status toggled.');
    }
    redirect('/admin/users.php');
}

// ── ADD USER ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_user') {
    Auth::verifyCsrf();
    $result = Auth::register(
        clean($_POST['username'] ?? ''),
        clean($_POST['email'] ?? ''),
        $_POST['password'] ?? ''
    );
    if ($result['success']) {
        if (($_POST['role'] ?? '') === 'admin') {
            DB::query('UPDATE users SET role = "admin" WHERE id = ?', [$result['user_id']]);
        }
        flash('success', 'User created.');
        redirect('/admin/users.php');
    } else {
        $errors = $result['errors'];
    }
}

// ── SEARCH / PAGINATE ─────────────────────────────────────────
$search  = clean($_GET['q'] ?? '');
$perPage = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));

$whereSQL = $search ? 'WHERE username LIKE ? OR email LIKE ?' : '';
$params   = $search ? ["%$search%", "%$search%"] : [];

$total    = DB::count("SELECT COUNT(*) FROM users $whereSQL", $params);
$pag      = paginate($total, $perPage, $page);
$users    = DB::rows("SELECT * FROM users $whereSQL ORDER BY created_at DESC LIMIT $perPage OFFSET {$pag['offset']}", $params);

$pageTitle = 'Users — Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="section visible" style="min-height:90vh;">
    <div class="section-label">Manage Users</div>

    <?php if ($errors): ?>
    <div class="auth-errors" style="margin-bottom:1.5rem;">
        <?php foreach ($errors as $e): ?><p>⚠ <?= e($e) ?></p><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Search + Add -->
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:2rem;">
        <form method="GET" style="display:flex;gap:.5rem;">
            <input type="text" name="q" class="auth-input" style="width:280px;"
                   placeholder="Search users..." value="<?= e($search) ?>">
            <button type="submit" class="ask-btn" style="padding:.8rem 1.5rem;"><span>Search</span></button>
            <?php if ($search): ?><a href="<?= BASE_URL ?>/admin/users.php" class="ask-btn" style="padding:.8rem 1.5rem;background:var(--muted);text-decoration:none;"><span>Clear</span></a><?php endif; ?>
        </form>
        <button onclick="document.getElementById('addUserModal').style.display='flex'" class="ask-btn" style="padding:.8rem 1.5rem;">
            <span>+ Add User</span>
        </button>
    </div>

    <table class="admin-table">
        <thead>
            <tr><th>#</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= e($u['username']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><span class="badge-role-<?= $u['role'] ?>"><?= e($u['role']) ?></span></td>
            <td><?= $u['is_active'] ? '<span class="badge-read">Active</span>' : '<span class="badge-unread">Inactive</span>' ?></td>
            <td><?= timeAgo($u['created_at']) ?></td>
            <td class="admin-actions">
                <a href="?toggle_role=<?= $u['id'] ?>" title="Toggle Role">⇄ Role</a>
                <a href="?toggle_active=<?= $u['id'] ?>" title="Toggle Active">⏸</a>
                <?php if ($u['id'] !== Auth::id()): ?>
                <a href="?delete=<?= $u['id'] ?>" title="Delete"
                   onclick="return confirm('Delete <?= e($u['username']) ?>?')" style="color:#ff4444;">✕</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?><tr><td colspan="7" style="padding:1rem;color:var(--muted);">No users found.</td></tr><?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($pag['pages'] > 1): ?>
    <div class="admin-pagination">
        <?php for ($i = 1; $i <= $pag['pages']; $i++): ?>
        <a href="?q=<?= urlencode($search) ?>&page=<?= $i ?>"
           class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</section>

<!-- ADD USER MODAL -->
<div id="addUserModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box">
        <div class="modal-title">Add New User</div>
        <form method="POST" class="auth-form">
            <?= Auth::csrfField() ?>
            <input type="hidden" name="action" value="add_user">
            <div class="auth-field">
                <label>Username</label>
                <input type="text" name="username" class="auth-input" required>
            </div>
            <div class="auth-field">
                <label>Email</label>
                <input type="email" name="email" class="auth-input" required>
            </div>
            <div class="auth-field">
                <label>Password</label>
                <input type="password" name="password" class="auth-input" required>
            </div>
            <div class="auth-field">
                <label>Role</label>
                <select name="role" class="auth-input">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="ask-btn" style="width:100%;margin-top:1rem;"><span>Create User</span></button>
        </form>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
