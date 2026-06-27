<?php
// ============================================================
// admin/messages.php — Messages Inbox
// ============================================================
define('ADMIN_VIEW', true);
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

Auth::requireAdmin();

// ── DELETE ────────────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    DB::query('DELETE FROM messages WHERE id = ?', [(int)$_GET['delete']]);
    flash('success', 'Message deleted.');
    redirect('/admin/messages.php');
}

// ── MARK READ ─────────────────────────────────────────────────
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    DB::query('UPDATE messages SET is_read = 1 WHERE id = ?', [(int)$_GET['read']]);
}

// ── VIEW SINGLE ───────────────────────────────────────────────
$viewing = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $viewing = DB::row('SELECT * FROM messages WHERE id = ?', [(int)$_GET['view']]);
    if ($viewing && !$viewing['is_read']) {
        DB::query('UPDATE messages SET is_read = 1 WHERE id = ?', [$viewing['id']]);
        $viewing['is_read'] = 1;
    }
}

// ── LIST ──────────────────────────────────────────────────────
$filter  = clean($_GET['filter'] ?? 'all'); // all | unread
$perPage = 15;
$page    = max(1, (int)($_GET['page'] ?? 1));

$whereSQL = $filter === 'unread' ? 'WHERE is_read = 0' : '';
$total    = DB::count("SELECT COUNT(*) FROM messages $whereSQL");
$pag      = paginate($total, $perPage, $page);
$messages = DB::rows("SELECT * FROM messages $whereSQL ORDER BY created_at DESC LIMIT $perPage OFFSET {$pag['offset']}");

$pageTitle = 'Messages — Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="section visible" style="min-height:90vh;">
    <div class="section-label">Messages Inbox</div>

    <div style="display:flex;gap:1rem;margin-bottom:2rem;flex-wrap:wrap;align-items:center;justify-content:space-between;">
        <div style="display:flex;gap:.5rem;">
            <a href="?filter=all"    class="ask-btn <?= $filter==='all'    ? '' : 'btn-ghost' ?>" style="padding:.6rem 1.2rem;text-decoration:none;font-size:.7rem;"><span>All (<?= DB::count('SELECT COUNT(*) FROM messages') ?>)</span></a>
            <a href="?filter=unread" class="ask-btn <?= $filter==='unread' ? '' : 'btn-ghost' ?>" style="padding:.6rem 1.2rem;text-decoration:none;font-size:.7rem;"><span>Unread (<?= DB::count('SELECT COUNT(*) FROM messages WHERE is_read=0') ?>)</span></a>
        </div>
        <a href="<?= BASE_URL ?>/admin/" style="font-size:.75rem;color:var(--muted);">← Dashboard</a>
    </div>

    <?php if ($viewing): ?>
    <!-- SINGLE MESSAGE VIEW -->
    <div class="admin-panel" style="margin-bottom:2rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
            <div class="admin-panel-title"><?= e($viewing['subject']) ?></div>
            <div style="display:flex;gap:1rem;">
                <a href="?delete=<?= $viewing['id'] ?>" onclick="return confirm('Delete this message?')" style="color:#ff4444;font-size:.75rem;">✕ Delete</a>
                <a href="?" style="font-size:.75rem;color:var(--muted);">← Back to Inbox</a>
            </div>
        </div>
        <div class="msg-detail-meta">
            <span><strong>From:</strong> <?= e($viewing['sender_name']) ?> &lt;<?= e($viewing['sender_email']) ?>&gt;</span>
            <span><strong>Time:</strong> <?= date('M j, Y g:i A', strtotime($viewing['created_at'])) ?></span>
        </div>
        <div class="msg-detail-body"><?= nl2br(e($viewing['body'])) ?></div>
        <a href="mailto:<?= e($viewing['sender_email']) ?>?subject=Re: <?= e($viewing['subject']) ?>"
           class="ask-btn" style="display:inline-block;margin-top:1.5rem;text-decoration:none;padding:.8rem 1.5rem;">
           <span>✉ Reply via Email</span>
        </a>
    </div>
    <?php endif; ?>

    <!-- MESSAGES TABLE -->
    <table class="admin-table">
        <thead><tr><th>From</th><th>Email</th><th>Subject</th><th>Status</th><th>Time</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($messages as $m): ?>
        <tr class="<?= $m['is_read'] ? '' : 'unread-row' ?>">
            <td><?= e($m['sender_name']) ?></td>
            <td><?= e($m['sender_email']) ?></td>
            <td><?= e($m['subject']) ?></td>
            <td><?= $m['is_read'] ? '<span class="badge-read">Read</span>' : '<span class="badge-unread">New</span>' ?></td>
            <td><?= timeAgo($m['created_at']) ?></td>
            <td class="admin-actions">
                <a href="?view=<?= $m['id'] ?>">View →</a>
                <a href="?delete=<?= $m['id'] ?>" onclick="return confirm('Delete?')" style="color:#ff4444;">✕</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($messages)): ?><tr><td colspan="6" style="padding:1rem;color:var(--muted);">No messages.</td></tr><?php endif; ?>
        </tbody>
    </table>

    <?php if ($pag['pages'] > 1): ?>
    <div class="admin-pagination">
        <?php for ($i = 1; $i <= $pag['pages']; $i++): ?>
        <a href="?filter=<?= e($filter) ?>&page=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
