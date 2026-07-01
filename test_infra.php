<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — test_infra.php
 * Standalone infrastructure diagnostic utility.
 *
 * Checks:
 *   1. PHP version and critical extension availability
 *   2. Core file-system path resolution
 *   3. .env readability (keys listed, values never echoed)
 *   4. MySQL socket connectivity via PDO (u713871298_lly_db)
 *
 * ACCESS CONTROL — token-gated. Remove from server after diagnostics.
 * Usage: https://loverlipsyachts.com/cockpit/test_infra.php?token=YOUR_DIAG_TOKEN
 */

/* ── Environment detection ──────────────────────────────────────── */
$remoteAddr  = $_SERVER['REMOTE_ADDR']  ?? '';
$httpHost    = $_SERVER['HTTP_HOST']    ?? '';
$serverAddr  = $_SERVER['SERVER_ADDR']  ?? '';

$isLocalEnv  = in_array($remoteAddr, ['127.0.0.1', '::1'], true)
    || in_array($serverAddr, ['127.0.0.1', '::1'], true)
    || in_array($httpHost,   ['localhost', '127.0.0.1'], true)
    || str_starts_with($httpHost, 'localhost:')
    || str_starts_with($httpHost, '127.0.0.1:');

/* ── Token gate (skipped on localhost, required on production) ──── */
$diagToken = (string) ($_GET['token'] ?? '');
$envToken  = (string) (getenv('DIAG_TOKEN') ?: 'lly_diag_2026');

if (!$isLocalEnv && ($diagToken === '' || !hash_equals($envToken, $diagToken))) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('403 Forbidden — production requires ?token=YOUR_DIAG_TOKEN');
}

header('Content-Type: text/plain; charset=utf-8');

$SEP  = str_repeat('─', 60);
$PASS = '[OK]  ';
$FAIL = '[!!]  ';
$INFO = '[--]  ';

echo "LLY INFRASTRUCTURE DIAGNOSTIC\n";
echo date('Y-m-d H:i:s T') . "\n";
echo $INFO . "Environment   : " . ($isLocalEnv ? "LOCAL (token gate bypassed)" : "PRODUCTION (token validated)") . "\n";
echo $SEP . "\n\n";

/* ════════════════════════════════════════════════════════════════
   1. PHP ENVIRONMENT
   ════════════════════════════════════════════════════════════════ */
echo "1. PHP ENVIRONMENT\n$SEP\n";
echo $INFO . "PHP version   : " . PHP_VERSION . "\n";
echo $INFO . "SAPI          : " . PHP_SAPI . "\n";
echo $INFO . "OS            : " . PHP_OS_FAMILY . "\n";
echo $INFO . "Memory limit  : " . ini_get('memory_limit') . "\n";
echo $INFO . "Max exec time : " . ini_get('max_execution_time') . "s\n";

$required_exts = ['pdo', 'pdo_mysql', 'gd', 'mbstring', 'json', 'openssl', 'fileinfo'];
echo "\nRequired extensions:\n";
foreach ($required_exts as $ext) {
    $ok = extension_loaded($ext);
    echo ($ok ? $PASS : $FAIL) . $ext . ($ok ? '' : '  <-- MISSING') . "\n";
}

/* ════════════════════════════════════════════════════════════════
   2. FILE-SYSTEM PATH RESOLUTION
   ════════════════════════════════════════════════════════════════ */
echo "\n2. FILE-SYSTEM PATHS\n$SEP\n";
echo $INFO . "Script __DIR__ : " . __DIR__ . "\n\n";

$paths = [
    'core/.env'           => __DIR__ . '/core/.env',
    'core/auth_check.php' => __DIR__ . '/core/auth_check.php',
    'api/conexion.php'    => __DIR__ . '/api/conexion.php',
    'api/login.php'       => __DIR__ . '/api/login.php',
    'assets/css/style.css'=> __DIR__ . '/assets/css/style.css',
    'book.php'            => __DIR__ . '/book.php',
    'index.php'           => __DIR__ . '/index.php',
    'dashboard.php'       => __DIR__ . '/dashboard.php',
];

foreach ($paths as $label => $abs) {
    $exists = file_exists($abs);
    echo ($exists ? $PASS : $FAIL) . $label . "\n";
    if (!$exists) {
        echo "       -> NOT FOUND: $abs\n";
        echo "          Check deployment completeness for this file.\n";
    }
}

/* ════════════════════════════════════════════════════════════════
   3. .ENV FILE — KEY INVENTORY (values never exposed)
   ════════════════════════════════════════════════════════════════ */
echo "\n3. ENVIRONMENT FILE (core/.env)\n$SEP\n";

$envPath = __DIR__ . '/core/.env';

if (!file_exists($envPath)) {
    echo $FAIL . "core/.env NOT FOUND at: $envPath\n";
    echo "       -> Upload it manually via Hostinger File Manager or SSH.\n";
    echo "       -> It is intentionally excluded from Git and CI/CD.\n";
    $env = [];
} elseif (!is_readable($envPath)) {
    echo $FAIL . "core/.env exists but is NOT READABLE (check file permissions).\n";
    $env = [];
} else {
    echo $PASS . "core/.env present and readable.\n";
    $env = [];
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || $line[0] === ';' || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v, " \t\"'");
    }

    // List keys only — never values
    echo $INFO . "Keys defined  : " . implode(', ', array_keys($env)) . "\n";

    $required_keys = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
    $missing_keys  = array_diff($required_keys, array_keys($env));
    if (empty($missing_keys)) {
        echo $PASS . "All required DB_* keys are present.\n";
        echo $INFO . "DB_HOST value : " . ($env['DB_HOST'] ?? '—') . "\n";
        echo $INFO . "DB_NAME value : " . ($env['DB_NAME'] ?? '—') . "\n";
        echo $INFO . "DB_USER value : " . ($env['DB_USER'] ?? '—') . "\n";
        echo $INFO . "DB_PASS value : [REDACTED — " . strlen($env['DB_PASS'] ?? '') . " chars]\n";
    } else {
        echo $FAIL . "Missing required keys: " . implode(', ', $missing_keys) . "\n";
    }
}

/* ════════════════════════════════════════════════════════════════
   4. MYSQL CONNECTIVITY — PDO try/catch isolation
   ════════════════════════════════════════════════════════════════ */
echo "\n4. MYSQL DATABASE CONNECTIVITY\n$SEP\n";
echo $INFO . "Expected DB   : u713871298_lly_db\n";
echo $INFO . "Expected user : u713871298_lly_db_user\n";
echo $INFO . "Host          : localhost (Hostinger native socket)\n\n";

if (empty($env) || empty($env['DB_NAME'] ?? '')) {
    echo $FAIL . "Cannot test DB connection — core/.env unavailable or incomplete.\n";
    echo "       -> See section 3 above.\n";
} else {
    $dbHost = $env['DB_HOST'] ?? 'localhost';
    $dbName = $env['DB_NAME'] ?? '';
    $dbUser = $env['DB_USER'] ?? '';
    $dbPass = $env['DB_PASS'] ?? '';
    $dsn    = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 5,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'",
        ]);

        /* ── Connection confirmed ── */
        $meta = $pdo->query("SELECT VERSION() AS ver, DATABASE() AS db, USER() AS usr")->fetch();
        echo $PASS . "PDO connection established successfully.\n";
        echo $INFO . "MySQL version : " . ($meta['ver'] ?? '—') . "\n";
        echo $INFO . "Active DB     : " . ($meta['db']  ?? '—') . "\n";
        echo $INFO . "Auth user     : " . ($meta['usr'] ?? '—') . "\n";

        /* ── Table presence checks ── */
        echo "\nTable checks:\n";

        $tables_to_check = ['lly_users', 'lly_book_content'];
        foreach ($tables_to_check as $tbl) {
            $found = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($tbl))->fetchAll();
            if (!empty($found)) {
                $cnt = $pdo->query("SELECT COUNT(*) AS c FROM `{$tbl}`")->fetch()['c'];
                echo $PASS . "`{$tbl}` exists — {$cnt} row(s).\n";
            } else {
                echo $FAIL . "`{$tbl}` NOT FOUND.\n";
                if ($tbl === 'lly_users') {
                    echo "       -> Authentication will fail. Run schema SQL in Hostinger phpMyAdmin.\n";
                }
                if ($tbl === 'lly_book_content') {
                    echo "       -> Book editor will fall back to defaults. Run schema SQL.\n";
                }
            }
        }

    } catch (PDOException $e) {
        echo $FAIL . "PDO connection FAILED.\n";
        // Sanitize message — redact any credential fragment
        $safe = preg_replace('/\b' . preg_quote($dbPass, '/') . '\b/', '[REDACTED]', $e->getMessage());
        echo "       Error : {$safe}\n";
        echo "       Code  : " . $e->getCode() . "\n\n";
        echo "  Troubleshooting checklist:\n";
        echo "  -> Verify DB_PASS in core/.env matches Hostinger hPanel password.\n";
        echo "  -> Confirm user '{$dbUser}' is assigned to '{$dbName}' in hPanel.\n";
        echo "  -> Hostinger socket: DB_HOST must be 'localhost' (not 127.0.0.1).\n";
        echo "  -> hPanel path: Databases -> MySQL Databases -> Manage.\n";
    }
}

/* ════════════════════════════════════════════════════════════════
   5. DEPLOYMENT PATH QUICK REFERENCE
   ════════════════════════════════════════════════════════════════ */
echo "\n5. DEPLOYMENT REFERENCE\n$SEP\n";
echo $INFO . "FTP account root : public_html/\n";
echo $INFO . "Cockpit job      : server-dir /cockpit/  => public_html/cockpit/\n";
echo $INFO . "My-book job      : server-dir /my-book/  => public_html/my-book/\n";
echo $INFO . "Cockpit URL      : https://loverlipsyachts.com/cockpit/\n";
echo $INFO . "Book URL         : https://loverlipsyachts.com/my-book/\n";
echo $INFO . "hPanel Databases : https://hpanel.hostinger.com -> Databases -> MySQL\n";

echo "\n$SEP\n";
echo "Diagnostic complete — " . date('H:i:s') . "\n";
echo "IMPORTANT: Remove or disable this file after use.\n";
