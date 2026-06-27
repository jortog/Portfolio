<?php
// ============================================================
// admin/settings.php — Site Settings
// ============================================================
define('ADMIN_VIEW', true);
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::verifyCsrf();
    $allowed = ['site_title', 'maintenance_mode', 'allow_registration'];
    foreach ($allowed as $key) {
        if (isset($_POST[$key])) {
            DB::query(
                'INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
                [$key, clean($_POST[$key])]
            );
        }
    }
    flash('success', 'Settings saved!');
    redirect('/admin/settings.php');
}

$settings = [];
foreach (DB::rows('SELECT * FROM site_settings') as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$pageTitle = 'Settings — Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="section visible" style="min-height:80vh;max-width:700px;">
    <div class="section-label">Site Settings</div>

    <div class="admin-panel" style="margin-top:1rem;">
        <form method="POST" class="auth-form">
            <?= Auth::csrfField() ?>

            <div class="auth-field">
                <label>Site Title</label>
                <input type="text" name="site_title" class="auth-input"
                       value="<?= e($settings['site_title'] ?? 'Jaru — John Ronie Ramiro') ?>">
            </div>

            <div class="auth-field">
                <label>Maintenance Mode</label>
                <select name="maintenance_mode" class="auth-input">
                    <option value="0" <?= ($settings['maintenance_mode'] ?? '0') === '0' ? 'selected' : '' ?>>Off</option>
                    <option value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'selected' : '' ?>>On</option>
                </select>
                <small style="color:var(--muted);">When ON, non-admins see a maintenance page.</small>
            </div>

            <div class="auth-field">
                <label>Allow Registration</label>
                <select name="allow_registration" class="auth-input">
                    <option value="1" <?= ($settings['allow_registration'] ?? '1') === '1' ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= ($settings['allow_registration'] ?? '1') === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>

            <button type="submit" class="ask-btn" style="width:100%;margin-top:2rem;"><span>✓ Save Settings</span></button>
        </form>
    </div>

    <div class="admin-panel" style="margin-top:2rem;">
        <div class="admin-panel-title">Danger Zone</div>
        <p style="color:var(--muted);font-size:.8rem;margin:1rem 0;">Flush all quiz attempts from the database.</p>
        <form method="POST" action="<?= BASE_URL ?>/admin/settings.php">
            <?= Auth::csrfField() ?>
            <input type="hidden" name="flush_quiz" value="1">
            <button type="submit" onclick="return confirm('Flush all quiz attempts?')"
                    class="ask-btn" style="background:#7f1d1d;padding:.8rem 1.5rem;">
                <span>✕ Flush Quiz Attempts</span>
            </button>
        </form>
    </div>
</section>

<?php
// Handle flush outside main block so we catch it regardless
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flush_quiz'])) {
    DB::query('TRUNCATE TABLE quiz_attempts');
    flash('success', 'Quiz attempts cleared.');
    redirect('/admin/settings.php');
}
require_once dirname(__DIR__) . '/includes/footer.php';
?>
