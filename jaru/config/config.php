<?php
// ============================================================
// config/config.php — App Configuration
// ============================================================

define('APP_NAME',    'Jaru Portfolio');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://uncleja.rf.gd'); // Change if needed
define('ROOT_PATH',   dirname(__DIR__));          // Points to /htdocs/jaru/

// ── Database ─────────────────────────────────────────────────
define('DB_HOST', 'sql308.infinityfree.com');
define('DB_PORT', '3306');
define('DB_NAME', 'if0_41895292_jaru');
define('DB_USER', 'if0_41895292');
define('DB_PASS', 'Masterpaui27');                        // XAMPP default: empty

// ── Security ─────────────────────────────────────────────────
define('CSRF_TOKEN_LENGTH', 32);
define('SESSION_LIFETIME',  3600 * 2);            // 2 hours
define('PASSWORD_ALGO',     PASSWORD_BCRYPT);
define('PASSWORD_COST',     12);

// ── Quiz answers (used in signup verification) ────────────────
// Stored lowercase for case-insensitive matching
define('QUIZ_ANSWERS', [
    'valo_tag'  => ['jortog', 'jortog #123', 'jortog#123'],
    'school'    => ['pup', 'polytechnic university', 'polytechnic university of the philippines'],
    'artist'    => ['frank ocean', 'frank', 'frank dagat'],
    'location'  => ['kalayaan', 'laguna', 'kalayaan laguna'],
    'nickname'  => ['jaru', 'kuya ja'],
]);

// ── Error display (set to false on production) ────────────────
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ── Session config (BEFORE session_start in any file) ────────
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
