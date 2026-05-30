<?php
declare(strict_types=1);

/**
 * =============================================================================
 * LOVER LIPS YACHTS — core/deploy.php
 * Manifiesto de Configuración de Despliegue — Staging (lly.tourfindy.com)
 * =============================================================================
 * PROPÓSITO:
 *   Centraliza y valida todas las variables de entorno requeridas para operar
 *   el servidor de pruebas. Nunca expone credenciales — las carga desde .env.
 *   Actúa como el "mapa de control" entre el entorno local y el remoto.
 *
 * USO:
 *   require_once __DIR__ . '/deploy.php';
 *   $cfg = DeployConfig::getInstance();
 *   $ftpHost = $cfg->ftp('host');
 *   $dbName  = $cfg->db('name');
 * =============================================================================
 */

final class DeployConfig
{
    private static ?self $instance = null;
    private array $env = [];

    /* ── Staging Server Map ────────────────────────────────────────────────── */
    private const STAGING = [
        'url'         => 'https://lly.tourfindy.com',
        'remote_root' => '/public_html/lly',
        'ftp_host'    => 'ftp.tourfindy.com',
        'ftp_port'    => 21,
        'ftp_secure'  => 'EXPLICIT', // FTPS — TLS explícito sobre puerto 21
    ];

    /* ── Required .env keys (validation contract) ──────────────────────────── */
    private const REQUIRED_KEYS = [
        'APP_ENV',
        'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS',
        'FTP_HOST', 'FTP_PORT', 'FTP_USER', 'FTP_PASS',
        'REMOTE_ROOT',
        'ALLOWED_ORIGINS',
    ];

    private function __construct()
    {
        $this->env = $this->loadEnv(__DIR__ . '/.env.staging');
        $this->validate();
    }

    /* ── Singleton accessor ─────────────────────────────────────────────────── */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /* ── Public accessors ───────────────────────────────────────────────────── */

    /** Returns a resolved FTP configuration value. */
    public function ftp(string $key): string|int
    {
        return match ($key) {
            'host'    => (string)($this->env['FTP_HOST']   ?? self::STAGING['ftp_host']),
            'port'    => (int)  ($this->env['FTP_PORT']    ?? self::STAGING['ftp_port']),
            'user'    => (string)($this->env['FTP_USER']   ?? ''),
            'pass'    => (string)($this->env['FTP_PASS']   ?? ''),
            'secure'  => (string)($this->env['FTP_SECURE'] ?? self::STAGING['ftp_secure']),
            default   => throw new \InvalidArgumentException("FTP key '{$key}' not defined."),
        };
    }

    /** Returns a resolved database configuration value. */
    public function db(string $key): string
    {
        return match ($key) {
            'host'    => (string)($this->env['DB_HOST'] ?? ''),
            'name'    => (string)($this->env['DB_NAME'] ?? ''),
            'user'    => (string)($this->env['DB_USER'] ?? ''),
            'pass'    => (string)($this->env['DB_PASS'] ?? ''),
            default   => throw new \InvalidArgumentException("DB key '{$key}' not defined."),
        };
    }

    /** Returns a resolved server path. */
    public function path(string $key): string
    {
        return match ($key) {
            'remote_root'   => (string)($this->env['REMOTE_ROOT']   ?? self::STAGING['remote_root']),
            'remote_core'   => (string)($this->env['REMOTE_CORE']   ?? self::STAGING['remote_root'] . '/core'),
            'remote_assets' => (string)($this->env['REMOTE_ASSETS'] ?? self::STAGING['remote_root'] . '/assets'),
            'remote_docs'   => (string)($this->env['REMOTE_DOCS']   ?? self::STAGING['remote_root'] . '/docs'),
            'staging_url'   => (string)($this->env['APP_URL_STAGING'] ?? self::STAGING['url']),
            default         => throw new \InvalidArgumentException("Path key '{$key}' not defined."),
        };
    }

    /** Returns CORS origins list as array. */
    public function allowedOrigins(): array
    {
        return array_map(
            'trim',
            explode(',', (string)($this->env['ALLOWED_ORIGINS'] ?? ''))
        );
    }

    /** Returns a human-readable deployment summary (safe — no secrets). */
    public function summary(): array
    {
        return [
            'environment'   => $this->env['APP_ENV']           ?? 'unknown',
            'app_url'       => $this->path('staging_url'),
            'remote_root'   => $this->path('remote_root'),
            'ftp_host'      => $this->ftp('host'),
            'ftp_port'      => $this->ftp('port'),
            'ftp_secure'    => $this->ftp('secure'),
            'ftp_user'      => $this->ftp('user'),
            'db_host'       => $this->db('host'),
            'db_name'       => $this->db('name'),
            'db_user'       => $this->db('user'),
            // db_pass and ftp_pass intentionally omitted from summary
        ];
    }

    /** Health-check: verifies DB connectivity and FTP host resolution. */
    public function healthCheck(): array
    {
        $results = ['db' => false, 'ftp_dns' => false, 'timestamp' => date('c')];

        // DB ping
        try {
            $dsn  = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $this->db('host'), $this->db('name'));
            $pdo  = new \PDO($dsn, $this->db('user'), $this->db('pass'), [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT            => 5,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            $pdo->query('SELECT 1');
            $results['db'] = true;
        } catch (\PDOException) {
            $results['db_error'] = 'Connection failed — check DB_HOST, DB_NAME, DB_USER, DB_PASS in .env.staging';
        }

        // FTP DNS resolution (non-blocking check)
        $resolved = gethostbyname($this->ftp('host'));
        $results['ftp_dns']    = ($resolved !== $this->ftp('host'));
        $results['ftp_target'] = $resolved;

        return $results;
    }

    /* ── Private helpers ────────────────────────────────────────────────────── */

    private function loadEnv(string $path): array
    {
        if (!is_readable($path)) {
            // Fallback: try .env in same dir
            $fallback = __DIR__ . '/.env';
            if (!is_readable($fallback)) {
                throw new \RuntimeException(
                    "DeployConfig: No .env.staging found at {$path}. " .
                    "Copy core/.env.staging.example → core/.env.staging and fill credentials."
                );
            }
            $path = $fallback;
        }

        $data = parse_ini_file($path, false, INI_SCANNER_RAW);
        if ($data === false) {
            throw new \RuntimeException("DeployConfig: Could not parse env file at {$path}.");
        }

        return $data;
    }

    private function validate(): void
    {
        $missing = [];
        foreach (self::REQUIRED_KEYS as $key) {
            if (empty($this->env[$key]) || $this->env[$key] === 'TO_BE_SET') {
                $missing[] = $key;
            }
        }
        if (!empty($missing)) {
            throw new \RuntimeException(
                "DeployConfig: Missing or unset required env keys: " . implode(', ', $missing) . ". " .
                "Fill them in core/.env.staging before deploying."
            );
        }
    }
}


/*
 * =============================================================================
 * CLI USAGE — Run as standalone script for health check:
 *   php core/deploy.php
 * =============================================================================
 */
if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    echo "\n╔══════════════════════════════════════════════════════════╗\n";
    echo "║  LOVER LIPS YACHTS — Staging Deploy Config Health Check ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n\n";

    try {
        $cfg = DeployConfig::getInstance();

        echo "📋  DEPLOYMENT SUMMARY\n";
        echo str_repeat('─', 50) . "\n";
        foreach ($cfg->summary() as $key => $value) {
            printf("  %-20s %s\n", $key . ':', $value);
        }

        echo "\n🩺  HEALTH CHECK\n";
        echo str_repeat('─', 50) . "\n";
        $health = $cfg->healthCheck();
        printf("  %-20s %s\n", 'database:', $health['db'] ? '✅  Connected' : '❌  Failed — ' . ($health['db_error'] ?? ''));
        printf("  %-20s %s\n", 'ftp dns:', $health['ftp_dns'] ? '✅  Resolved → ' . $health['ftp_target'] : '❌  Could not resolve');
        printf("  %-20s %s\n", 'checked_at:', $health['timestamp']);

        echo "\n✅  Config loaded successfully. Ready for staging deployment.\n\n";

    } catch (\RuntimeException $e) {
        echo "❌  ERROR: " . $e->getMessage() . "\n\n";
        exit(1);
    }
}
