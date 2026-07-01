<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/conexion.php
 * Centralized PDO singleton. Reads credentials from core/.env — the
 * only source of truth for database parameters.
 *
 * Production (Hostinger):
 *   DB_HOST = localhost  (native socket — fastest, no TCP overhead)
 *   DB_NAME = u713871298_lly_db
 *   DB_USER = u713871298_lly_db_user
 *
 * Local XAMPP:
 *   Set DB_HOST in core/.env to the Hostinger remote MySQL hostname
 *   after whitelisting your IP under Databases → Remote MySQL.
 *
 * Security hardening:
 *   • PDO::ATTR_EMULATE_PREPARES = false  → real prepared statements
 *   • PDO::ERRMODE_EXCEPTION             → all errors throw, never echo
 *   • PDO::ATTR_DEFAULT_FETCH_MODE       → assoc arrays only
 *   • Connection errors logged, never exposed to the client
 */
final class Conexion
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getConnection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $env  = self::loadEnv(__DIR__ . '/../core/.env');

        $host = $env['DB_HOST'] ?? 'localhost';
        $name = $env['DB_NAME'] ?? '';
        $user = $env['DB_USER'] ?? '';
        $pass = $env['DB_PASS'] ?? '';

        if ($name === '' || $user === '') {
            error_log('[LLY conexion] Missing DB_NAME or DB_USER in core/.env');
            throw new RuntimeException('Database configuration incomplete.', 500);
        }

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $host,
            $name
        );

        try {
            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES   => false,   // real prepared statements
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'",
            ]);
        } catch (PDOException $e) {
            error_log('[LLY conexion] DB connection failed: ' . $e->getMessage());
            // Callers decide: API endpoints respond with JSON 503,
            // page-rendering files degrade gracefully to defaults.
            throw new RuntimeException('Database connection failed.', 500, $e);
        }

        return self::$instance;
    }

    /**
     * Parse a simple KEY="value" / KEY=value .env file.
     * Lines starting with # or ; and blank lines are ignored.
     */
    private static function loadEnv(string $path): array
    {
        $vars = [];
        if (!is_readable($path)) {
            error_log('[LLY conexion] core/.env not found or not readable at: ' . $path);
            return $vars;
        }
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || $line[0] === ';' || !str_contains($line, '=')) {
                continue;
            }
            [$key, $val] = explode('=', $line, 2);
            $vars[trim($key)] = trim($val, " \t\"'");
        }
        return $vars;
    }
}
