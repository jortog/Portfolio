<?php
// ============================================================
// includes/db.php — PDO Database Connection (Singleton)
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';

class DB {
    private static ?PDO $instance = null;

    public static function get(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                DB_HOST, DB_PORT, DB_NAME
            );
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // Don't expose DB credentials in errors
                die('<div style="font-family:monospace;padding:2rem;background:#1a0a0a;color:#ff6b6b;">
                     <strong>DB Connection Failed.</strong><br>
                     Check config/config.php credentials and ensure MySQL is running in XAMPP.
                     <br><small>' . htmlspecialchars($e->getMessage()) . '</small></div>');
            }
        }
        return self::$instance;
    }

    // ── Helpers ───────────────────────────────────────────────

    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function row(string $sql, array $params = []): ?array {
        return self::query($sql, $params)->fetch() ?: null;
    }

    public static function rows(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    public static function insert(string $sql, array $params = []): string {
        self::query($sql, $params);
        return self::get()->lastInsertId();
    }

    public static function count(string $sql, array $params = []): int {
        return (int) self::query($sql, $params)->fetchColumn();
    }
}
