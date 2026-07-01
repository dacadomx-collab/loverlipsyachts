<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/conexion.php
 * Centralized PDO singleton — single production DB, dual-environment routing.
 *
 * Architecture principle
 * ─────────────────────
 * There is ONE database: u713871298_lly_db on Hostinger.
 * How we reach it depends purely on where this code is executing:
 *
 *   ┌──────────────────────┬────────────────────────────────────────────┐
 *   │ Execution context    │ MySQL host used                            │
 *   ├──────────────────────┼────────────────────────────────────────────┤
 *   │ Hostinger server     │ localhost   — native socket, fastest path  │
 *   │ Local XAMPP / dev    │ 145.223.105.68:3306 — TCP to Hostinger     │
 *   └──────────────────────┴────────────────────────────────────────────┘
 *
 * Detection uses server-supplied HTTP signals only (no .env APP_ENV key):
 *   • HTTP_HOST  — value is "localhost" or "127.0.0.1" → local
 *   • SERVER_ADDR — value is 127.0.0.1 or ::1          → local
 *   • REMOTE_ADDR — value is 127.0.0.1 or ::1          → local
 *
 * DSN notes:
 *   • Remote path always includes port=3306 so PHP uses TCP, not Unix socket.
 *   • Production "localhost" lets Hostinger use the fast internal socket.
 *
 * Security:
 *   • PDO::ATTR_EMULATE_PREPARES = false  — real prepared statements only
 *   • PDO::ERRMODE_EXCEPTION              — errors throw, never echo
 *   • PDO::ATTR_DEFAULT_FETCH_MODE        — assoc arrays everywhere
 *   • MYSQL_ATTR_INIT_COMMAND             — utf8mb4 enforced per connection
 *   • Credentials never logged or exposed to the response stream
 */
final class Conexion
{
    /** Fallback remote host when DB_HOST_LOCAL is absent from .env */
    private const REMOTE_HOST_FALLBACK = '145.223.105.68';

    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getConnection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $env = self::loadEnv(__DIR__ . '/../core/.env');

        $name = $env['DB_NAME'] ?? '';
        $user = $env['DB_USER'] ?? '';
        $pass = $env['DB_PASS'] ?? '';

        if ($name === '' || $user === '') {
            error_log('[LLY conexion] Missing DB_NAME or DB_USER in core/.env');
            throw new RuntimeException('Database configuration incomplete.', 500);
        }

        /* ── Environment-adaptive host selection ─────────────────────
           isLocalRequest() inspects only PHP server superglobals —
           no dependency on any .env key.                              */
        if (self::isLocalRequest()) {
            // Local XAMPP → reach Hostinger over TCP; port=3306 forces TCP,
            // preventing PHP from trying a local Unix socket path.
            $remoteHost = $env['DB_HOST_LOCAL'] ?? self::REMOTE_HOST_FALLBACK;
            $dsn = sprintf(
                'mysql:host=%s;port=3306;dbname=%s;charset=utf8mb4',
                $remoteHost,
                $name
            );
        } else {
            // Hostinger production → internal socket binding (fastest path)
            $prodHost = $env['DB_HOST'] ?? 'localhost';
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $prodHost,
                $name
            );
        }

        try {
            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'",
            ]);
        } catch (PDOException $e) {
            error_log('[LLY conexion] DB connection failed: ' . $e->getMessage());
            // Callers decide response: API endpoints → JSON 503,
            // page-rendering files → graceful content defaults.
            throw new RuntimeException('Database connection failed.', 500, $e);
        }

        return self::$instance;
    }

    /**
     * Detect a local development request from PHP server superglobals.
     *
     * Three independent signals — all must fail for production to be assumed:
     *   Signal 1: HTTP_HOST   is localhost / 127.0.0.1 (web request via XAMPP)
     *   Signal 2: SERVER_ADDR is 127.0.0.1 / ::1       (Apache bound to loopback)
     *   Signal 3: REMOTE_ADDR is 127.0.0.1 / ::1       (request originated locally)
     *
     * Using three signals prevents false-negatives on non-standard XAMPP configs
     * and remains correct in CLI contexts where some superglobals are absent.
     */
    private static function isLocalRequest(): bool
    {
        $httpHost   = (string) ($_SERVER['HTTP_HOST']   ?? '');
        $serverAddr = (string) ($_SERVER['SERVER_ADDR'] ?? '');
        $remoteAddr = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

        // Signal 1 — HTTP_HOST
        if (
            in_array($httpHost, ['localhost', '127.0.0.1'], true) ||
            str_starts_with($httpHost, 'localhost:') ||
            str_starts_with($httpHost, '127.0.0.1:')
        ) {
            return true;
        }

        // Signal 2 — SERVER_ADDR (Apache/XAMPP binding address)
        if (in_array($serverAddr, ['127.0.0.1', '::1'], true)) {
            return true;
        }

        // Signal 3 — REMOTE_ADDR (who made the request)
        if (in_array($remoteAddr, ['127.0.0.1', '::1'], true)) {
            return true;
        }

        return false;
    }

    /**
     * Parse KEY="value" / KEY=value lines from a .env file.
     * Blank lines and lines starting with # or ; are ignored.
     */
    private static function loadEnv(string $path): array
    {
        $vars = [];
        if (!is_readable($path)) {
            error_log('[LLY conexion] core/.env not found or not readable: ' . $path);
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
