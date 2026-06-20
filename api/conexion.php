<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/conexion.php
 * Centralized PDO connection. Reads DB_HOST/DB_NAME/DB_USER/DB_PASS from
 * core/.env (never committed, never web-accessible — blocked in .htaccess).
 *
 * Forensic failure handling: a connection error is never echoed to the
 * client. The real exception goes to error_log() only; the client gets a
 * generic bilingual JSON message with HTTP 500.
 */
final class Conexion
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    public static function getConnection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $env = self::loadEnv(__DIR__ . '/../core/.env');

        $host = $env['DB_HOST'] ?? '';
        $name = $env['DB_NAME'] ?? '';
        $user = $env['DB_USER'] ?? '';
        $pass = $env['DB_PASS'] ?? '';

        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $name);

        try {
            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            error_log('[LLY conexion] DB connection failed: ' . $e->getMessage());

            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success'    => false,
                'message_en' => 'A server error occurred. Please try again later.',
                'message_es' => 'Ocurrió un error del servidor. Inténtalo más tarde.',
            ]);
            exit;
        }

        return self::$instance;
    }

    private static function loadEnv(string $path): array
    {
        $vars = [];
        if (!is_readable($path)) {
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
