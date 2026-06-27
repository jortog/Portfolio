<?php
// ============================================================
// includes/auth.php — Authentication & Session Helpers
// ============================================================
require_once __DIR__ . '/db.php';

class Auth {

    // ── Session Bootstrap ─────────────────────────────────────
    public static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('JARU_SESS');
            session_start();
            // Regenerate session ID periodically
            if (!isset($_SESSION['_initiated'])) {
                session_regenerate_id(true);
                $_SESSION['_initiated'] = true;
            }
        }
    }

    // ── CSRF ──────────────────────────────────────────────────
    public static function csrfToken(): string {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfField(): string {
        return '<input type="hidden" name="csrf_token" value="' . self::csrfToken() . '">';
    }

    public static function verifyCsrf(): void {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals(self::csrfToken(), $token)) {
            http_response_code(403);
            die('<h2>CSRF validation failed. Go back and try again.</h2>');
        }
    }

    // ── Register ──────────────────────────────────────────────
    public static function register(string $username, string $email, string $password): array {
        $username = trim($username);
        $email    = strtolower(trim($email));
        $errors   = [];

        if (strlen($username) < 3 || strlen($username) > 50)
            $errors[] = 'Username must be 3–50 characters.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors[] = 'Invalid email address.';
        if (strlen($password) < 8)
            $errors[] = 'Password must be at least 8 characters.';
        if (!preg_match('/[A-Z]/', $password))
            $errors[] = 'Password needs at least one uppercase letter.';
        if (!preg_match('/[0-9]/', $password))
            $errors[] = 'Password needs at least one number.';

        if ($errors) return ['success' => false, 'errors' => $errors];

        // Uniqueness check
        if (DB::count('SELECT COUNT(*) FROM users WHERE email = ?', [$email]))
            return ['success' => false, 'errors' => ['Email already registered.']];
        if (DB::count('SELECT COUNT(*) FROM users WHERE username = ?', [$username]))
            return ['success' => false, 'errors' => ['Username already taken.']];

        $hash = password_hash($password, PASSWORD_ALGO, ['cost' => PASSWORD_COST]);
        $id   = DB::insert(
            'INSERT INTO users (username, email, password) VALUES (?, ?, ?)',
            [$username, $email, $hash]
        );

        return ['success' => true, 'user_id' => $id];
    }

    // ── Login ─────────────────────────────────────────────────
    public static function login(string $identifier, string $password): array {
        $identifier = strtolower(trim($identifier));
        $user = DB::row(
            'SELECT * FROM users WHERE (email = ? OR username = ?) AND is_active = 1',
            [$identifier, $identifier]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'Invalid credentials.'];
        }

        self::startSession();
        session_regenerate_id(true);

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['_login_at'] = time();

        return ['success' => true, 'role' => $user['role']];
    }

    // ── Logout ────────────────────────────────────────────────
    public static function logout(): void {
        self::startSession();
        $_SESSION = [];
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    // ── Guards ────────────────────────────────────────────────
    public static function check(): bool {
        self::startSession();
        if (!isset($_SESSION['logged_in'])) return false;
        // Expire check
        if (isset($_SESSION['_login_at']) && (time() - $_SESSION['_login_at']) > SESSION_LIFETIME) {
            self::logout();
        }
        return true;
    }

    public static function requireLogin(): void {
        if (!self::check()) {
            header('Location: ' . BASE_URL . '/login.php?next=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }

    public static function requireAdmin(): void {
        self::requireLogin();
        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            include ROOT_PATH . '/includes/403.php';
            exit;
        }
    }

    // ── Current user ─────────────────────────────────────────
    public static function user(): ?array {
        if (!self::check()) return null;
        return DB::row('SELECT id, username, email, role, avatar, bio, created_at FROM users WHERE id = ?', [$_SESSION['user_id']]);
    }

    public static function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public static function isAdmin(): bool {
        return ($_SESSION['role'] ?? '') === 'admin';
    }

    // ── Quiz verification ─────────────────────────────────────
    public static function checkQuizAnswer(string $key, string $userAnswer): bool {
        $answers = QUIZ_ANSWERS[$key] ?? [];
        $clean   = strtolower(trim($userAnswer));
        foreach ($answers as $ans) {
            if (str_contains($clean, $ans) || str_contains($ans, $clean)) return true;
        }
        return false;
    }
}
