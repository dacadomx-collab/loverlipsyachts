<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — core/seed_owner.php
 * One-shot, idempotent Owner account repair utility.
 *
 * CLI-ONLY. Run with:  php core/seed_owner.php
 *
 * It cannot be loaded from a browser — and that's by design, not a bug to
 * work around: the project's root .htaccess blocks every request under
 * /core/ at the Apache level (RewriteRule ^core(/.*)?$ - [F,L]), before PHP
 * ever runs, because core/ holds core/.env (DB credentials, SMTP password,
 * encryption key). That same .htaccess governs Hostinger production, so
 * carving out a browser-reachable exception for this one file would open
 * the same hole in production. If you see "Acceso denegado" hitting this
 * URL in a browser, that's Apache's ErrorDocument 403 — expected, not this
 * script's doing.
 *
 * What it does:
 *   1. Reads the seed email + both candidate passwords from core/.env
 *      (SEED_OWNER_EMAIL, SEED_OWNER_PASSWORD, SEED_OWNER_PASSWORD_LEGACY)
 *      — never hardcoded here, so no plaintext password ships in a
 *      git-tracked file (core/.env is gitignored).
 *   2. If the user row doesn't exist, creates it.
 *   3. If it exists but neither password verifies against the stored
 *      hash(es), re-hashes SEED_OWNER_PASSWORD with bcrypt and stores the
 *      legacy password's hash in password_hash_legacy (requires
 *      sql/002_add_password_hash_legacy.sql to have been run first).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Forbidden — run this from a terminal: php core/seed_owner.php\n");
}

/**
 * api/conexion.php picks its DSN from $_SERVER superglobals
 * (HTTP_HOST / SERVER_ADDR / REMOTE_ADDR). The CLI SAPI never populates
 * those, so without this it silently falls into the "production" branch —
 * DB_HOST="localhost" with the *remote* Hostinger credentials — which
 * fails with a confusing "Access denied ...@'localhost'" that has nothing
 * to do with the real Hostinger connection. This utility only ever runs
 * by a developer on their own machine, so it always wants the same
 * local→TCP→Hostinger path a real localhost page request would take.
 */
$_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

require __DIR__ . '/../api/conexion.php';

/** Minimal .env reader — mirrors Conexion::loadEnv() (private, can't reuse directly). */
function lly_seed_read_env(string $path): array
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

$env = lly_seed_read_env(__DIR__ . '/.env');

$email       = $env['SEED_OWNER_EMAIL']          ?? '';
$password    = $env['SEED_OWNER_PASSWORD']        ?? '';
$passwordOld = $env['SEED_OWNER_PASSWORD_LEGACY']  ?? '';

if ($email === '' || $password === '') {
    exit("Missing SEED_OWNER_EMAIL / SEED_OWNER_PASSWORD in core/.env — nothing to do.\n");
}

$dbHostLocal = $env['DB_HOST_LOCAL'] ?? '145.223.105.68';

try {
    $pdo = Conexion::getConnection();
} catch (RuntimeException $e) {
    $prev = $e->getPrevious();
    $driverMsg = $prev instanceof PDOException ? $prev->getMessage() : $e->getMessage();

    echo "Database connection failed.\n";
    echo "Raw error: {$driverMsg}\n\n";

    if (preg_match('/Access denied for user/i', $driverMsg)) {
        echo "Diagnosis: Hostinger reached the connection request and rejected the\n";
        echo "credentials — this is a DB_USER / DB_PASS problem, NOT a firewall/IP\n";
        echo "problem (if it were IP/host access, MySQL would refuse the connection\n";
        echo "before ever checking a password). Check core/.env:\n";
        echo "  DB_USER = " . ($env['DB_USER'] ?? '(missing)') . "\n";
        echo "  DB_PASS = " . (isset($env['DB_PASS']) ? '(set, ' . strlen($env['DB_PASS']) . ' chars)' : '(missing)') . "\n";
        echo "Confirm these still match the password on Hostinger hPanel → Databases → MySQL Databases.\n";
    } elseif (preg_match('/(getaddrinfo|Connection timed out|Operation timed out|No route to host|Connection refused|SQLSTATE\[HY000\] \[2002\])/i', $driverMsg)) {
        echo "Diagnosis: PHP never reached a MySQL server at all — this is a\n";
        echo "DB_HOST_LOCAL / network reachability problem, not a credentials problem.\n";
        echo "  DB_HOST_LOCAL = {$dbHostLocal}\n";
        echo "Confirm this IP is correct and port 3306 is open from this machine\n";
        echo "(Hostinger hPanel → Databases → Remote MySQL must list your current\n";
        echo "public IP or %, and a local firewall must allow outbound 3306).\n";
    } else {
        echo "Diagnosis: unrecognized failure mode — check DB_HOST_LOCAL, DB_NAME,\n";
        echo "DB_USER and DB_PASS in core/.env against Hostinger hPanel directly.\n";
    }
    exit(1);
}

echo "Database connection: OK ({$dbHostLocal})\n";

$hasLegacyColumn = true;
try {
    $pdo->query('SELECT password_hash_legacy FROM lly_users LIMIT 1');
} catch (PDOException) {
    $hasLegacyColumn = false;
}

$stmt = $pdo->prepare('SELECT id, password_hash' . ($hasLegacyColumn ? ', password_hash_legacy' : '') . ' FROM lly_users WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

$newHash    = password_hash($password, PASSWORD_BCRYPT);
$legacyHash = $passwordOld !== '' ? password_hash($passwordOld, PASSWORD_BCRYPT) : null;

if (!$user) {
    $insert = $pdo->prepare(
        'INSERT INTO lly_users (email, password_hash' . ($hasLegacyColumn ? ', password_hash_legacy' : '') . ')'
        . ' VALUES (:email, :hash' . ($hasLegacyColumn ? ', :legacy' : '') . ')'
    );
    $params = ['email' => $email, 'hash' => $newHash];
    if ($hasLegacyColumn) {
        $params['legacy'] = $legacyHash;
    }
    $insert->execute($params);
    echo "Created Owner account for {$email}.\n";
    echo "\n✅ Cuenta de Lester Keizer configurada exitosamente. Ya puedes iniciar sesión.\n";
    if (!$hasLegacyColumn && $passwordOld !== '') {
        echo "Note: password_hash_legacy column not found — run sql/002_add_password_hash_legacy.sql to enable the fallback password.\n";
    }
    exit(0);
}

$currentMatches = password_verify($password, $user['password_hash'])
    || ($hasLegacyColumn && !empty($user['password_hash_legacy']) && password_verify($password, $user['password_hash_legacy']));
$legacyMatches = $passwordOld !== '' && (
    password_verify($passwordOld, $user['password_hash'])
    || ($hasLegacyColumn && !empty($user['password_hash_legacy']) && password_verify($passwordOld, $user['password_hash_legacy']))
);

if ($currentMatches && ($passwordOld === '' || $legacyMatches)) {
    echo "Owner account for {$email} already accepts the configured password(s) — no change made.\n";
    echo "\n✅ Cuenta de Lester Keizer configurada exitosamente. Ya puedes iniciar sesión.\n";
    exit(0);
}

$update = $pdo->prepare(
    'UPDATE lly_users SET password_hash = :hash'
    . ($hasLegacyColumn ? ', password_hash_legacy = :legacy' : '')
    . ' WHERE id = :id'
);
$params = ['hash' => $newHash, 'id' => $user['id']];
if ($hasLegacyColumn) {
    $params['legacy'] = $legacyHash;
}
$update->execute($params);

echo "Updated password hash for {$email}.\n";
if (!$hasLegacyColumn && $passwordOld !== '') {
    echo "Note: password_hash_legacy column not found — run sql/002_add_password_hash_legacy.sql to enable the fallback password.\n";
}
echo "\n✅ Cuenta de Lester Keizer configurada exitosamente. Ya puedes iniciar sesión.\n";
