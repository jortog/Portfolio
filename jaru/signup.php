<?php
// ============================================================
// signup.php — Creative Quiz-Based Signup (3 Steps)
// ============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

Auth::startSession();
if (Auth::check()) redirect('/');

$errors  = [];
$success = false;
$step    = (int)($_SESSION['signup_step'] ?? 1);

// ── POST Handler ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::verifyCsrf();
    $action = $_POST['action'] ?? '';

    // ── STEP 1: Quiz ──────────────────────────────────────────
    if ($action === 'quiz') {
        $score = 0;
        $q1 = clean($_POST['q1'] ?? '');   // Valo tag
        $q2 = clean($_POST['q2'] ?? '');   // School
        $q3 = clean($_POST['q3'] ?? '');   // Artist
        $q4 = clean($_POST['q4'] ?? '');   // Nickname

        if (Auth::checkQuizAnswer('valo_tag',  $q1)) $score++;
        if (Auth::checkQuizAnswer('school',    $q2)) $score++;
        if (Auth::checkQuizAnswer('artist',    $q3)) $score++;
        if (Auth::checkQuizAnswer('nickname',  $q4)) $score++;

        // Log attempt
        DB::query('INSERT INTO quiz_attempts (email, score, ip_address) VALUES (?, ?, ?)',
            ['pending', $score, $_SERVER['REMOTE_ADDR'] ?? '']);

        if ($score >= 2) {
            $_SESSION['signup_step']  = 2;
            $_SESSION['quiz_score']   = $score;
            $step = 2;
        } else {
            $errors[] = "You only got $score/4 correct. You clearly don't know Jaru well enough 😅 Try again!";
        }
    }

    // ── STEP 2: Account Details ───────────────────────────────
    elseif ($action === 'account' && $step >= 2) {
        $_SESSION['signup_username'] = clean($_POST['username'] ?? '');
        $_SESSION['signup_email']    = clean($_POST['email'] ?? '');
        $password  = $_POST['password']  ?? '';
        $password2 = $_POST['password2'] ?? '';

        if ($password !== $password2) {
            $errors[] = 'Passwords do not match.';
        } else {
            $result = Auth::register(
                $_SESSION['signup_username'],
                $_SESSION['signup_email'],
                $password
            );
            if ($result['success']) {
                // Auto-login
                Auth::login($_SESSION['signup_email'], $password);
                // Cleanup
                unset($_SESSION['signup_step'], $_SESSION['quiz_score'],
                      $_SESSION['signup_username'], $_SESSION['signup_email']);
                flash('success', "Welcome to Jaru's world! 🎉");
                redirect('/');
            } else {
                $errors = $result['errors'];
            }
        }
    }
}

$pageTitle = 'Sign Up — Jaru';
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-page">
    <div class="auth-container">

        <!-- STEP INDICATOR -->
        <div class="auth-steps">
            <div class="auth-step <?= $step >= 1 ? 'active' : '' ?>">
                <span class="step-num">01</span>
                <span class="step-label">Prove You Know Me</span>
            </div>
            <div class="step-connector"></div>
            <div class="auth-step <?= $step >= 2 ? 'active' : '' ?>">
                <span class="step-num">02</span>
                <span class="step-label">Create Account</span>
            </div>
        </div>

        <?php if ($errors): ?>
        <div class="auth-errors">
            <?php foreach ($errors as $e): ?><p>⚠ <?= e($e) ?></p><?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ══════════ STEP 1: THE QUIZ ══════════ -->
        <?php if ($step === 1): ?>
        <div class="auth-card">
            <div class="auth-eyebrow"> SURPRISE </div>
            <h2 class="auth-title">Do you know<br><em>Jaru?</em></h2>
            <p class="auth-subtitle">
                Answer at least <strong>2 out of 4</strong> questions correctly to unlock your account.

            </p>

            <form method="POST" class="auth-form">
                <?= Auth::csrfField() ?>
                <input type="hidden" name="action" value="quiz">

                <div class="quiz-card">
                    <div class="quiz-num">Q1</div>
                    <label class="quiz-label">What is Jaru's Valorant in-game tag?</label>
                    <p class="quiz-hint">💡 Hint: THE GOATTTTT </p>
                    <input type="text" name="q1" class="auth-input" placeholder="e.g. Player #TAG" autocomplete="off">
                </div>

                <div class="quiz-card">
                    <div class="quiz-num">Q2</div>
                    <label class="quiz-label">What university does Jaru attend?</label>
                    <p class="quiz-hint">💡 Hint: 3 letters, stands for something Polytechnic...</p>
                    <input type="text" name="q2" class="auth-input" placeholder="University name or acronym" autocomplete="off">
                </div>

                <div class="quiz-card">
                    <div class="quiz-num">Q3</div>
                    <label class="quiz-label">What artist is Jaru known to "worship"?</label>
                    <p class="quiz-hint">💡 Hint: DAGATTT</p>
                    <input type="text" name="q3" class="auth-input" placeholder="Artist name" autocomplete="off">
                </div>

                <div class="quiz-card">
                    <div class="quiz-num">Q4</div>
                    <label class="quiz-label">What is Jaru's nickname?</label>
                    <p class="quiz-hint">💡 Hint: It's literally the logo of this website 💀</p>
                    <input type="text" name="q4" class="auth-input" placeholder="His nickname" autocomplete="off">
                </div>

                <button type="submit" class="ask-btn" style="width:100%;margin-top:2rem;">
                    <span>✦ Submit Answers</span>
                </button>
            </form>

            <p class="auth-switch">Already have an account? <a href="<?= BASE_URL ?>/login.php">Log in →</a></p>
        </div>

        <!-- ══════════ STEP 2: ACCOUNT CREATION ══════════ -->
        <?php elseif ($step === 2): ?>
        <div class="auth-card">
            <div class="auth-eyebrow">Step 02 / 02 — You passed! 🎉 <?= isset($_SESSION['quiz_score']) ? '(' . $_SESSION['quiz_score'] . '/4 correct)' : '' ?></div>
            <h2 class="auth-title">Create your<br><em>account</em></h2>
            <p class="auth-subtitle">You've proven you know Jaru. Now set up your account.</p>

            <form method="POST" class="auth-form">
                <?= Auth::csrfField() ?>
                <input type="hidden" name="action" value="account">

                <div class="auth-field">
                    <label>Username</label>
                    <input type="text" name="username" class="auth-input"
                           value="<?= e($_SESSION['signup_username'] ?? '') ?>"
                           placeholder="your_username" required minlength="3" maxlength="50" autocomplete="off">
                </div>
                <div class="auth-field">
                    <label>Email Address</label>
                    <input type="email" name="email" class="auth-input"
                           value="<?= e($_SESSION['signup_email'] ?? '') ?>"
                           placeholder="you@email.com" required autocomplete="off">
                </div>
                <div class="auth-field">
                    <label>Password</label>
                    <input type="password" name="password" id="pw" class="auth-input"
                           placeholder="Min. 8 chars, 1 uppercase, 1 number" required>
                    <div class="pw-strength" id="pwStrength"></div>
                </div>
                <div class="auth-field">
                    <label>Confirm Password</label>
                    <input type="password" name="password2" class="auth-input"
                           placeholder="Repeat your password" required>
                </div>

                <button type="submit" class="ask-btn" style="width:100%;margin-top:2rem;">
                    <span>→ Create Account</span>
                </button>
            </form>

            <p style="margin-top:1rem;font-size:0.7rem;color:var(--muted);text-align:center;">
                Changed your mind? <a href="?reset=1" style="color:var(--ink)">Restart quiz</a>
            </p>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php
// Allow resetting the flow
if (isset($_GET['reset'])) {
    unset($_SESSION['signup_step'], $_SESSION['quiz_score'], $_SESSION['signup_username'], $_SESSION['signup_email']);
    redirect('/signup.php');
}
require_once __DIR__ . '/includes/footer.php';
?>

<script>
// Password strength meter
const pw = document.getElementById('pw');
const bar = document.getElementById('pwStrength');
if (pw && bar) {
    pw.addEventListener('input', () => {
        const v = pw.value;
        let strength = 0;
        if (v.length >= 8) strength++;
        if (/[A-Z]/.test(v)) strength++;
        if (/[0-9]/.test(v)) strength++;
        if (/[^A-Za-z0-9]/.test(v)) strength++;
        const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
        const colors = ['', '#ff4444', '#ffaa00', '#88cc00', '#c8f135'];
        bar.style.cssText = `height:3px;background:${colors[strength] || '#333'};width:${strength*25}%;transition:all .3s;margin-top:4px;`;
        bar.title = labels[strength] || '';
    });
}
</script>
