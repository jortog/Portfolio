<?php
// ============================================================
// admin/index.php — Admin Dashboard
// ============================================================
define('ADMIN_VIEW', true);
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

Auth::requireAdmin();

// Stats
$stats = [
    'users'    => DB::count('SELECT COUNT(*) FROM users'),
    'messages' => DB::count('SELECT COUNT(*) FROM messages'),
    'unread'   => DB::count('SELECT COUNT(*) FROM messages WHERE is_read = 0'),
    'projects' => DB::count('SELECT COUNT(*) FROM projects'),
    'quiz_attempts' => DB::count('SELECT COUNT(*) FROM quiz_attempts'),
];

$recentMsgs  = DB::rows('SELECT * FROM messages ORDER BY created_at DESC LIMIT 5');
$recentUsers = DB::rows('SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5');

$pageTitle = 'Admin Dashboard — Jaru';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="section visible" style="min-height:90vh;">
    <div class="section-label">Admin Dashboard</div>

    <!-- STAT CARDS -->
    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-num"><?= $stats['users'] ?></div>
            <div class="admin-stat-label">Total Users</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-num"><?= $stats['messages'] ?></div>
            <div class="admin-stat-label">Messages</div>
        </div>
        <div class="admin-stat-card" style="border-color:var(--accent);">
            <div class="admin-stat-num" style="color:var(--accent);"><?= $stats['unread'] ?></div>
            <div class="admin-stat-label">Unread</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-num"><?= $stats['projects'] ?></div>
            <div class="admin-stat-label">Projects</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-num"><?= $stats['quiz_attempts'] ?></div>
            <div class="admin-stat-label">Quiz Attempts</div>
        </div>
    </div>

    <!-- QUICK LINKS -->
    <div class="admin-quick-links">
        <a href="<?= BASE_URL ?>/admin/users.php"    class="admin-link-card">👥 Manage Users</a>
        <a href="<?= BASE_URL ?>/admin/messages.php" class="admin-link-card">✉️ Messages <?= $stats['unread'] ? '<span class="badge">'.$stats['unread'].'</span>' : '' ?></a>
        <a href="<?= BASE_URL ?>/admin/projects.php" class="admin-link-card">📁 Projects</a>
        <a href="<?= BASE_URL ?>/admin/settings.php" class="admin-link-card">⚙️ Settings</a>
    </div>

    <!-- RECENT MESSAGES -->
    <div class="admin-panel" style="margin-top:3rem;">
        <div class="admin-panel-title">Recent Messages</div>
        <table class="admin-table">
            <thead><tr><th>From</th><th>Subject</th><th>Status</th><th>Time</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($recentMsgs as $m): ?>
            <tr class="<?= $m['is_read'] ? '' : 'unread-row' ?>">
                <td><?= e($m['sender_name']) ?><br><small><?= e($m['sender_email']) ?></small></td>
                <td><?= e($m['subject']) ?></td>
                <td><?= $m['is_read'] ? '<span class="badge-read">Read</span>' : '<span class="badge-unread">New</span>' ?></td>
                <td><?= timeAgo($m['created_at']) ?></td>
                <td><a href="<?= BASE_URL ?>/admin/messages.php?view=<?= $m['id'] ?>">View →</a></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($recentMsgs)): ?><tr><td colspan="5" style="color:var(--muted);padding:1rem;">No messages yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- RECENT USERS -->
    <div class="admin-panel" style="margin-top:2rem;">
        <div class="admin-panel-title">Recent Users</div>
        <table class="admin-table">
            <thead><tr><th>Username</th><th>Email</th><th>Role</th><th>Joined</th></tr></thead>
            <tbody>
            <?php foreach ($recentUsers as $u): ?>
            <tr>
                <td><?= e($u['username']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><span class="badge-role-<?= $u['role'] ?>"><?= e($u['role']) ?></span></td>
                <td><?= timeAgo($u['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <a href="<?= BASE_URL ?>/admin/users.php" style="display:inline-block;margin-top:1rem;font-size:.75rem;">View all users →</a>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
