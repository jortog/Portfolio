<?php
$pageTitle = '403 — Forbidden';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<section class="section visible" style="min-height:60vh;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:1rem;">
    <div class="section-label">Error 403</div>
    <h2 style="font-family:'Playfair Display',serif;font-size:5rem;font-weight:900;font-style:italic;">Forbidden</h2>
    <p style="color:var(--muted);">You don't have permission to access this page.</p>
    <a href="<?= BASE_URL ?>/" class="ask-btn" style="display:inline-block;padding:1rem 2rem;text-decoration:none;"><span>← Back Home</span></a>
</section>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
