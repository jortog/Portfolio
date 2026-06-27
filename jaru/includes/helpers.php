<?php
// ============================================================
// includes/helpers.php — Utility Functions
// ============================================================

// ── XSS Prevention ────────────────────────────────────────────
function e(mixed $val): string {
    return htmlspecialchars((string)$val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ── Redirect ─────────────────────────────────────────────────
function redirect(string $path): never {
    $url = str_starts_with($path, 'http') ? $path : BASE_URL . '/' . ltrim($path, '/');
    header('Location: ' . $url);
    exit;
}

// ── Flash messages ────────────────────────────────────────────
function flash(string $type, string $msg): void {
    Auth::startSession();
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function renderFlash(): string {
    Auth::startSession();
    if (empty($_SESSION['flash'])) return '';
    $out = '';
    foreach ($_SESSION['flash'] as $f) {
        $cls = match($f['type']) {
            'success' => 'flash-success',
            'error'   => 'flash-error',
            'info'    => 'flash-info',
            default   => 'flash-info',
        };
        $out .= '<div class="flash ' . $cls . '">' . e($f['msg']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $out;
}

// ── Sanitize input ────────────────────────────────────────────
function clean(string $str): string {
    return trim(strip_tags($str));
}

// ── Pagination ────────────────────────────────────────────────
function paginate(int $total, int $perPage, int $current): array {
    $pages = (int) ceil($total / $perPage);
    $offset = ($current - 1) * $perPage;
    return ['pages' => $pages, 'offset' => $offset, 'current' => $current];
}

// ── Time ago ─────────────────────────────────────────────────
function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)      return 'just now';
    if ($diff < 3600)    return floor($diff / 60) . 'm ago';
    if ($diff < 86400)   return floor($diff / 3600) . 'h ago';
    if ($diff < 604800)  return floor($diff / 86400) . 'd ago';
    return date('M j, Y', strtotime($datetime));
}

// ── Avatar URL ────────────────────────────────────────────────
function avatarUrl(?string $file): string {
    if ($file && file_exists(ROOT_PATH . '/public/images/avatars/' . $file)) {
        return BASE_URL . '/public/images/avatars/' . $file;
    }
    return BASE_URL . '/public/images/default-avatar.png';
}
