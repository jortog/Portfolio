<?php
// ============================================================
// login.php — Login Page
// ============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

Auth::startSession();
if (Auth::check()) redirect('/');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::verifyCsrf();
    $identifier = clean($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';
    $next       = clean($_POST['next'] ?? '');

    if (!$identifier || !$password) {
        $errors[] = 'Please fill in all fields.';
    } else {
        $result = Auth::login($identifier, $password);
        if ($result['success']) {
            flash('success', 'Welcome back! 🤙');
            $safe = filter_var($next, FILTER_VALIDATE_URL) ? $next : BASE_URL . '/';
            redirect($safe);
        } else {
            $errors[] = $result['error'];
        }
    }
}

$next      = clean($_GET['next'] ?? '');
$pageTitle = 'Login — Jaru';
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-page">
    <div class="auth-container" style="max-width:480px;">
        <div class="auth-card">
            <div class="auth-eyebrow">Welcome</div>
            <h2 class="auth-title">Log<br><em>back in</em></h2>
            <p class="auth-subtitle">Use your username or email + password.</p>

            <?php if ($errors): ?>
            <div class="auth-errors">
                <?php foreach ($errors as $e): ?><p>⚠ <?= e($e) ?></p><?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <?= Auth::csrfField() ?>
                <input type="hidden" name="next" value="<?= e($next) ?>">

                <div class="auth-field">
                    <label>Username or Email</label>
                    <input type="text" name="identifier" class="auth-input"
                           placeholder="jaru / you@gmail.com"
                           value="<?= e($_POST['identifier'] ?? '') ?>"
                           required autofocus autocomplete="username">
                </div>
                <div class="auth-field">
                    <label>Password</label>
                    <input type="password" name="password" class="auth-input"
                           placeholder="••••••••" required autocomplete="current-password">
                </div>

                <button type="submit" class="ask-btn" style="width:100%;margin-top:2rem;">
                    <span>→ Login</span>
                </button>
            </form>

            <p class="auth-switch" style="margin-top:1.5rem;">
                Don't have an account? <a href="<?= BASE_URL ?>/signup.php">Sign up →</a>
            </p>
            <p class="auth-switch">
                <small style="color:var(--muted);">Admin? <a href="<?= BASE_URL ?>/admin/" style="color:var(--muted)">Admin Panel</a></small>
            </p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
