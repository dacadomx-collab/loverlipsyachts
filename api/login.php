<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/login.php
 * 6-layer login endpoint:
 *   1. Transport/method guard      4. password_verify() against bcrypt hash
 *   2. Input sanitization/validation 5. Session regeneration (fixation-safe)
 *   3. Prepared-statement lookup    6. Optional remember-me cookie (HttpOnly)
 */

require __DIR__ . '/conexion.php';

header('Content-Type: application/json');

function ll_respond(string $status, string $message, int $httpCode, array $data = [])
{
    http_response_code($httpCode);
    echo json_encode([
        'status'  => $status,
        'message' => $message,
        'data'    => $data,
    ]);
    exit;
}

/* ── Layer 1: Transport guard ──────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ll_respond('error', 'Method not allowed. / Método no permitido.', 405);
}

/* ── Layer 2: Input sanitization & validation ──────────────────────── */
$rawEmail = trim((string) ($_POST['email'] ?? ''));
$email    = filter_var($rawEmail, FILTER_SANITIZE_EMAIL);
$password = (string) ($_POST['password'] ?? '');
$remember = !empty($_POST['remember']);

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    ll_respond('error', 'Invalid email or password format. / Formato de correo o contraseña inválido.', 400);
}

$pdo = Conexion::getConnection();

/* ── Layer 3: Prepared-statement lookup ────────────────────────────── */
$stmt = $pdo->prepare('SELECT id, email, password_hash FROM lly_users WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

/* ── Layer 4: bcrypt verification ──────────────────────────────────── */
if (!$user || !password_verify($password, $user['password_hash'])) {
    ll_respond('error', 'Invalid credentials. / Credenciales inválidas.', 401);
}

/* ── Layer 5: Session regeneration (fixation-safe) ─────────────────── */
session_start();
session_regenerate_id(true);
$_SESSION['lly_user_id'] = (int) $user['id'];
$_SESSION['lly_email']   = $user['email'];

/* ── Layer 6: Remember-me cookie (HttpOnly, Secure, SameSite=Strict) ─ */
if ($remember) {
    $token  = bin2hex(random_bytes(32));
    $expiry = (new DateTimeImmutable('+30 days'))->format('Y-m-d H:i:s');

    $update = $pdo->prepare('UPDATE lly_users SET remember_token = :token, token_expiry = :expiry WHERE id = :id');
    $update->execute([
        'token'  => $token,
        'expiry' => $expiry,
        'id'     => $user['id'],
    ]);

    setcookie('lly_remember', $token, [
        'expires'  => (new DateTimeImmutable('+30 days'))->getTimestamp(),
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

ll_respond('success', 'Login successful. / Inicio de sesión exitoso.', 200, [
    'email' => $user['email'],
]);
