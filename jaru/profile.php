<?php
// ============================================================
// profile.php — User Profile (View + Edit)
// ============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

Auth::requireLogin();
$user   = Auth::user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::verifyCsrf();
    $action = $_POST['action'] ?? '';

    // ── Update Bio / Username ─────────────────────────────────
    if ($action === 'update_profile') {
        $username = clean($_POST['username'] ?? '');
        $bio      = clean($_POST['bio'] ?? '');

        if (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = 'Username must be 3–50 characters.';
        } else {
            $taken = DB::count(
                'SELECT COUNT(*) FROM users WHERE username = ? AND id != ?',
                [$username, $user['id']]
            );
            if ($taken) {
                $errors[] = 'Username already taken.';
            } else {
                DB::query(
                    'UPDATE users SET username = ?, bio = ? WHERE id = ?',
                    [$username, $bio, $user['id']]
                );
                $_SESSION['username'] = $username;
                flash('success', 'Profile updated!');
                redirect('/profile.php');
            }
        }
    }

    // ── Change Password ───────────────────────────────────────
    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $fresh = DB::row('SELECT password FROM users WHERE id = ?', [$user['id']]);
        if (!password_verify($current, $fresh['password'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif ($new !== $confirm) {
            $errors[] = 'New passwords do not match.';
        } elseif (strlen($new) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        } else {
            $hash = password_hash($new, PASSWORD_ALGO, ['cost' => PASSWORD_COST]);
            DB::query('UPDATE users SET password = ? WHERE id = ?', [$hash, $user['id']]);
            flash('success', 'Password changed!');
            redirect('/profile.php');
        }
    }

    // ── Upload Avatar ─────────────────────────────────────────
    if ($action === 'upload_avatar' && isset($_FILES['avatar'])) {
        $file    = $_FILES['avatar'];
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2 MB

        if (!in_array($file['type'], $allowed)) {
            $errors[] = 'Invalid file type. Use JPG, PNG, WEBP, or GIF.';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'File too large. Max 2MB.';
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload error. Try again.';
        } else {
            $dir = ROOT_PATH . '/public/images/avatars/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);

            $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
            move_uploaded_file($file['tmp_name'], $dir . $filename);

            // Remove old avatar
            if ($user['avatar'] && file_exists($dir . $user['avatar'])) {
                unlink($dir . $user['avatar']);
            }
            DB::query('UPDATE users SET avatar = ? WHERE id = ?', [$filename, $user['id']]);
            flash('success', 'Avatar updated!');
            redirect('/profile.php');
        }
    }
}

// Reload fresh data
$user      = Auth::user();
$pageTitle = 'Profile — ' . e($user['username']);
$myMsgs    = DB::rows(
    'SELECT * FROM messages WHERE user_id = ? ORDER BY created_at DESC LIMIT 5',
    [$user['id']]
);
require_once __DIR__ . '/includes/header.php';
?>

<section class="section visible" id="profile-page" style="min-height:80vh;">
    <div class="section-label">My Profile</div>

    <?php if ($errors): ?>
    <div class="auth-errors" style="margin-bottom:2rem;">
        <?php foreach ($errors as $e): ?><p>⚠ <?= e($e) ?></p><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="profile-grid">

        <!-- LEFT: Avatar + Stats -->
        <div class="profile-sidebar">
            <div class="profile-avatar-wrap">
                <img src="<?= avatarUrl($user['avatar']) ?>" alt="Avatar" class="profile-avatar">
                <form method="POST" enctype="multipart/form-data" class="avatar-upload-form">
                    <?= Auth::csrfField() ?>
                    <input type="hidden" name="action" value="upload_avatar">
                    <label class="avatar-upload-btn">
                        ✎ Change
                        <input type="file" name="avatar" accept="image/*" onchange="this.form.submit()" hidden>
                    </label>
                </form>
            </div>
            <div class="profile-meta">
                <div class="profile-name"><?= e($user['username']) ?></div>
                <div class="profile-role"><?= e(ucfirst($user['role'])) ?></div>
                <div class="profile-joined">Joined <?= timeAgo($user['created_at']) ?></div>
            </div>
            <div class="profile-bio">
                <?= $user['bio'] ? e($user['bio']) : '<em style="color:var(--muted)">No bio yet.</em>' ?>
            </div>
        </div>

        <!-- RIGHT: Edit Forms + Messages -->
        <div class="profile-main">

            <!-- Edit Profile -->
            <details class="profile-section" open>
                <summary class="profile-section-title">Edit Profile</summary>
                <form method="POST" class="auth-form" style="margin-top:1.5rem;">
                    <?= Auth::csrfField() ?>
                    <input type="hidden" name="action" value="update_profile">
                    <div class="auth-field">
                        <label>Username</label>
                        <input type="text" name="username" class="auth-input"
                               value="<?= e($user['username']) ?>" required>
                    </div>
                    <div class="auth-field">
                        <label>Bio <small style="color:var(--muted)">(optional)</small></label>
                        <textarea name="bio" class="auth-input" rows="3"
                                  placeholder="Tell Jaru something about yourself..."><?= e($user['bio'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="ask-btn" style="width:100%;"><span>Save Changes</span></button>
                </form>
            </details>

            <!-- Change Password -->
            <details class="profile-section">
                <summary class="profile-section-title">Change Password</summary>
                <form method="POST" class="auth-form" style="margin-top:1.5rem;">
                    <?= Auth::csrfField() ?>
                    <input type="hidden" name="action" value="change_password">
                    <div class="auth-field">
                        <label>Current Password</label>
                        <input type="password" name="current_password" class="auth-input" required>
                    </div>
                    <div class="auth-field">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="auth-input" required>
                    </div>
                    <div class="auth-field">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="auth-input" required>
                    </div>
                    <button type="submit" class="ask-btn" style="width:100%;"><span>Update Password</span></button>
                </form>
            </details>

            <!-- My Messages -->
            <div class="profile-section">
                <div class="profile-section-title">My Messages</div>
                <?php if (empty($myMsgs)): ?>
                    <p style="color:var(--muted);margin-top:1rem;">No messages sent yet.</p>
                <?php else: ?>
                <div style="margin-top:1rem;">
                    <?php foreach ($myMsgs as $m): ?>
                    <div class="msg-row">
                        <div class="msg-subject"><?= e($m['subject']) ?></div>
                        <div class="msg-body"><?= e(substr($m['body'], 0, 100)) ?>...</div>
                        <div class="msg-time"><?= timeAgo($m['created_at']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="<?= BASE_URL ?>/contact.php" style="display:inline-block;margin-top:1rem;font-size:.75rem;color:var(--muted);">→ Send a new message</a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
