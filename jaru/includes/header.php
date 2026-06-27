<?php
// includes/header.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
Auth::startSession();
$currentUser = Auth::user();
$isLoggedIn  = Auth::check();
$isAdmin     = Auth::isAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= e($pageTitle ?? 'Jaru — John Ronie Ramiro') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&family=DM+Mono:wght@300;400;500&family=Syne:wght@400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/webstyle.css">
    <link rel="stylesheet" href="/public/css/app.css">
    <meta name="base-url" content="<?= BASE_URL ?>">
</head>
<body>

<div id="loader">
    <div class="loader-text"><span>Jaru</span></div>
    <div class="loader-sub">Loading portfolio</div>
</div>

<div class="accent-stripe"></div>

<nav>
    <a class="nav-logo" href="<?= BASE_URL ?>/">Jaru</a>
    <ul class="nav-links">
        <li><a href="<?= BASE_URL ?>/#info">About</a></li>
        <li><a href="<?= BASE_URL ?>/#hobbies">Interests</a></li>
        <li><a href="<?= BASE_URL ?>/#routine">Routine</a></li>
        <li><a href="<?= BASE_URL ?>/#ask">Ask Me</a></li>
        <li><a href="<?= BASE_URL ?>/contact.php">Contact</a></li>
        <?php if ($isLoggedIn): ?>
        <li><a href="<?= BASE_URL ?>/profile.php">Profile</a></li>
        <?php if ($isAdmin): ?>
        <li><a href="<?= BASE_URL ?>/admin/">⚙ Admin</a></li>
        <?php endif; ?>
        <?php endif; ?>
    </ul>
    <div class="nav-badge">PUP · BSCS</div>
    <div class="nav-auth-area">
        <?php if ($isLoggedIn): ?>
            <span class="nav-user">Hey, <?= e($currentUser['username']) ?></span>
            <a href="<?= BASE_URL ?>/logout.php" class="nav-btn nav-btn--outline">Logout</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/login.php" class="nav-btn nav-btn--outline">Login</a>
            <a href="<?= BASE_URL ?>/signup.php" class="nav-btn nav-btn--fill">Sign Up</a>
        <?php endif; ?>
        <div class="theme-switcher">
            <button class="theme-box neon"   onclick="setTheme('dark')"  title="Dark Mode"></button>
            <button class="theme-box purple" onclick="setTheme('light')" title="Light Mode"></button>
        </div>
    </div>
</nav>

<?= renderFlash() ?>
