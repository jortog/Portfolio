<?php
// ============================================================
// contact.php — Contact / Message System
// ============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

Auth::startSession();
$user    = Auth::user();
$errors  = [];
$sent    = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::verifyCsrf();

    $name    = clean($_POST['name']    ?? '');
    $email   = strtolower(trim($_POST['email']   ?? ''));
    $subject = clean($_POST['subject'] ?? 'General');
    $body    = clean($_POST['body']    ?? '');
    $userId  = $user ? $user['id'] : null;

    if (!$name)                                  $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
    if (strlen($body) < 10)                      $errors[] = 'Message must be at least 10 characters.';
    if (strlen($body) > 2000)                    $errors[] = 'Message too long (max 2000 chars).';

    if (!$errors) {
        DB::insert(
            'INSERT INTO messages (user_id, sender_name, sender_email, subject, body) VALUES (?, ?, ?, ?, ?)',
            [$userId, $name, $email, $subject, $body]
        );
        $sent = true;
    }
}

$pageTitle = 'Contact — Jaru';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section visible" id="contact-page" style="min-height:80vh;">
    <div class="section-label">Contact Me</div>

    <div class="ask-wrapper">
        <div>
            <h2 class="ask-title">Wazzup,<br><em>cuhhh</em></h2>
            <p class="ask-sub" style="margin-top:1rem;">
                Send me a message! I read everything.<br>
                For collabs, questions, or just to say hi.
            </p>
            <div style="margin-top:2.5rem;">
                <div class="info-item" style="border:none;padding:.5rem 0;">
                    <span class="info-key">School</span>
                    <span class="info-val" style="font-size:1rem;">PUP · BSCS</span>
                </div>
                <div class="info-item" style="border:none;padding:.5rem 0;">
                    <span class="info-key">Location</span>
                    <span class="info-val" style="font-size:1rem;">Laguna, Philippines</span>
                </div>
                <div class="info-item" style="border:none;padding:.5rem 0;">
                    <span class="info-key">Valo Tag</span>
                    <span class="info-val" style="font-size:1rem;">Jortog #123</span>
                </div>
            </div>
        </div>

        <div>
            <?php if ($sent): ?>
            <div class="answer-box show" style="border-left-color:var(--accent);font-size:1rem;">
                ✅ Message sent! I'll get back to you. Salamat!
            </div>
            <a href="<?= BASE_URL ?>/" class="ask-btn" style="display:inline-block;margin-top:1rem;text-decoration:none;width:100%;"><span>← Back Home</span></a>

            <?php else: ?>

            <?php if ($errors): ?>
            <div class="auth-errors" style="margin-bottom:1.5rem;">
                <?php foreach ($errors as $e): ?><p>⚠ <?= e($e) ?></p><?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <?= Auth::csrfField() ?>

                <div class="auth-field">
                    <label>Your Name</label>
                    <input type="text" name="name" class="auth-input"
                           value="<?= e($user ? $user['username'] : ($_POST['name'] ?? '')) ?>"
                           placeholder="John Doe" required>
                </div>
                <div class="auth-field">
                    <label>Email</label>
                    <input type="email" name="email" class="auth-input"
                           value="<?= e($user ? $user['email'] : ($_POST['email'] ?? '')) ?>"
                           placeholder="you@email.com" required
                           <?= $user ? 'readonly' : '' ?>>
                </div>
                <div class="auth-field">
                    <label>Subject</label>
                    <select name="subject" class="auth-input">
                        <?php foreach (['General', 'Collab', 'Question', 'Just saying hi', 'Valo invite 🎮'] as $opt): ?>
                        <option <?= ($_POST['subject'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="auth-field">
                    <label>Message</label>
                    <textarea name="body" class="auth-input" rows="5"
                              placeholder="Say something... kahit anong topic." required><?= e($_POST['body'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="ask-btn" style="width:100%;"><span>→ Send Message</span></button>
            </form>

            <?php if (!$user): ?>
            <p class="auth-switch" style="margin-top:1rem;">
                <a href="<?= BASE_URL ?>/login.php">Log in</a> so I know who you are! 👀
            </p>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
