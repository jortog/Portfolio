<?php
// ============================================================
// index.php — Homepage
// ============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

Auth::startSession();
if (!Auth::check()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';

$projects = DB::rows('SELECT * FROM projects WHERE is_featured = 1 ORDER BY sort_order ASC LIMIT 6');

// Redirect to login if not logged in
if (!Auth::check()) {
    redirect('/login.php');
}

?>

<section class="hero">
    <div class="hero-content">
        <div class="hero-left">
            <div class="hero-eyebrow">Portfolio · <?= date('Y') ?></div>
            <h1 class="hero-name">
                John<br>
                <em>Ronie</em><br>Ramiro
            </h1>
        </div>
        <div class="hero-right">
            <p class="hero-desc">
                A 20-year-old 3rd Year Computer Science student from Laguna,
                enjoying life, studying hard, and finding wonder in everything in between.
            </p>
            <div class="hero-stats">
                <div>
                    <div class="stat-num">20</div>
                    <div class="stat-label">Years Old</div>
                </div>
                <div>
                    <div class="stat-num">4</div>
                    <div class="stat-label">Hobbies</div>
                </div>
                <div>
                    <div class="stat-num">&#x221e;</div>
                    <div class="stat-label">Curiosity</div>
                </div>
            </div>
            
        </div>
    </div>
    <div class="hero-ticker">
        <div class="ticker-track" id="ticker"></div>
    </div>
    <div class="hero-scroll-hint">
        <span>Scroll</span>
        <div class="scroll-line"></div>
    </div>
</section>

<!-- PERSONAL INFO -->
<section class="section" id="info">
    <div class="section-label">Personal Information</div>
    <div class="info-grid">
        <div class="info-item"><span class="info-key">Full Name</span><span class="info-val">John Ronie Ramiro</span></div>
        <div class="info-item"><span class="info-key">Age</span><span class="info-val">20 years old</span></div>
        <div class="info-item"><span class="info-key">Address</span><span class="info-val">Longos Kalayaan, Laguna</span></div>
        <div class="info-item"><span class="info-key">Course</span><span class="info-val">BS Computer Science</span></div>
        <div class="info-item"><span class="info-key">University</span><span class="info-val">Polytechnic University of the Philippines</span></div>
        <div class="info-item"><span class="info-key">Call me</span><span class="info-val">Jaru</span></div>
    </div>
</section>

<!-- HOBBIES -->
<section class="section" id="hobbies">
    <div class="section-label">Interests &amp; Hobbies</div>
    <div class="hobby-display-container">
        <img id="hobby-image" src="<?= BASE_URL ?>/public/images/valo.jpg" alt="Hobby Preview">
        <div id="image-caption">Select an interest to see more</div>
    </div>
    <div class="hobbies-list">
        <div class="hobby-card" onclick="switchHobby('gaming')">
            <div class="hobby-num">01</div>
            <span class="hobby-icon">🎮</span>
            <div class="hobby-title">Video Games</div>
        </div>
        <div class="hobby-card" onclick="switchHobby('movies')">
            <div class="hobby-num">02</div>
            <span class="hobby-icon">🎬</span>
            <div class="hobby-title">Movies &amp; Series</div>
        </div>
        <div class="hobby-card" onclick="switchHobby('traveling')">
            <div class="hobby-num">03</div>
            <span class="hobby-icon">✈️</span>
            <div class="hobby-title">Traveling</div>
        </div>
        <div class="hobby-card" onclick="switchHobby('music')">
            <div class="hobby-num">04</div>
            <span class="hobby-icon">🎧</span>
            <div class="hobby-title">Music</div>
        </div>
    </div>
</section>

<!-- ROUTINE -->
<section class="section" id="routine">
    <div class="section-label">Daily Routine</div>
    <ol class="routine-list">
        <li class="routine-item"><div><div class="routine-main">Wake &amp; bake</div><div class="routine-sub">Gigising at sisimulan ang araw nang swabe</div></div></li>
        <li class="routine-item"><div><div class="routine-main">Papasok sa mga Klase</div><div class="routine-sub">Online lectures &middot; Laboratory activities</div></div></li>
        <li class="routine-item"><div><div class="routine-main">Study &amp; Gawa ng Pendings</div><div class="routine-sub">Chill aral lungs</div></div></li>
        <li class="routine-item"><div><div class="routine-main">Relax &amp; Socialize</div><div class="routine-sub">Tambay with mga og</div></div></li>
    </ol>
</section>

<!-- FEATURED PROJECTS -->
<?php if (!empty($projects)): ?>
<section class="section" id="projects">
    <div class="section-label">Featured Projects</div>
    <div class="projects-grid">
        <?php foreach ($projects as $p): ?>
        <div class="project-card">
            <?php if ($p['image']): ?>
            <img src="<?= BASE_URL ?>/public/images/projects/<?= e($p['image']) ?>" alt="<?= e($p['title']) ?>" class="project-img">
            <?php endif; ?>
            <div class="project-body">
                <div class="project-stack"><?= e($p['tech_stack'] ?? '') ?></div>
                <h3 class="project-title"><?= e($p['title']) ?></h3>
                <p class="project-desc"><?= e($p['description']) ?></p>
                <?php if ($p['project_url']): ?>
                <a href="<?= e($p['project_url']) ?>" class="project-link" target="_blank" rel="noopener">View →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ASK ME -->
<section class="section" id="ask">
    <div class="section-label">Ask Me Anything</div>
    <div class="ask-wrapper">
        <div>
            <h2 class="ask-title">May<br>tanong<br>ka?</h2>
            <p class="ask-sub">
                Ask me about my name, age, where I'm from, what I study, my hobbies, or my daily routine.
                Kahit tagalog yan, sasagutin ko. Wag lang bisaya hahaha.
            </p>
            <?php if (!$isLoggedIn): ?>
            <p class="ask-sub" style="margin-top:1rem;color:var(--accent);">
                ✦ <a href="<?= BASE_URL ?>/signup.php" style="color:inherit;">Create an account</a> to send me a real message!
            </p>
            <?php endif; ?>
        </div>
        <div>
            <div class="question-input-wrap">
                <input type="text" id="userQuestion" placeholder="Type your question here..." autocomplete="off">
            </div>
            <button class="ask-btn" id="submitBtn"><span>&#x2192; Send Question</span></button>
            <div class="answer-box" id="answer"></div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
