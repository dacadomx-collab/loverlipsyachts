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

        // Conexión unificada: core/.env sigue siendo la única fuente de
        // verdad para DB_NAME/DB_USER/DB_PASS — no se toca ni se duplica.
        // En producción (lly.tourfindy.com), DB_HOST="localhost" es correcto
        // porque Apache/PHP/MySQL viven en la misma máquina cPanel.
        //
        // Puente híbrido SOLO para XAMPP local: "localhost" ahí apunta a la
        // MariaDB de XAMPP (sin la tabla lly_users), nunca a tourfindy.com.
        // Se sobreescribe el host únicamente cuando la petición llega por
        // localhost/127.0.0.1 — el flujo en vivo no entra a este bloque.
        $env = self::loadEnv(__DIR__ . '/../core/.env');

        $requestHost = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        $isLocalEnv  = in_array($requestHost, ['localhost', '127.0.0.1'], true)
            || str_starts_with($requestHost, 'localhost:')
            || str_starts_with($requestHost, '127.0.0.1:');

        // Hostname externo real, validado en esta misma sesión (Remote
        // MySQL debe seguir habilitado en cPanel para la IP de quien prueba
        // en local — si vuelve a dar 500, eso es lo primero a revisar).
        $host = $isLocalEnv ? 'chir205.websitehostserver.net' : ($env['DB_HOST'] ?? '');
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
