<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — core/auth_check.php
 * Shared session/remember-me validation. Used by every entry point that
 * needs to know "is this visitor logged in?" — index.php (gatekeeper) and
 * strategy.php (direct deep link) both call lly_is_authenticated() instead
 * of duplicating the PDO/remember-token logic in two places.
 *
 * Caller is responsible for requiring api/conexion.php first.
 */

function lly_is_authenticated(): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!empty($_SESSION['lly_user_id'])) {
        return true;
    }

    return lly_check_remember_me();
}

function lly_check_remember_me(): bool
{
    if (empty($_COOKIE['lly_remember'])) {
        return false;
    }

    $token = (string) $_COOKIE['lly_remember'];
    $pdo   = Conexion::getConnection();

    $stmt = $pdo->prepare(
        'SELECT id, email FROM lly_users WHERE remember_token = :token AND token_expiry > NOW() LIMIT 1'
    );
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['lly_user_id'] = (int) $user['id'];
    $_SESSION['lly_email']   = $user['email'];

    // Rotate the token on every remember-login: sliding 30-day window,
    // and the old cookie value stops working the moment it's used once.
    $newToken  = bin2hex(random_bytes(32));
    $newExpiry = (new DateTimeImmutable('+30 days'))->format('Y-m-d H:i:s');

    $update = $pdo->prepare('UPDATE lly_users SET remember_token = :token, token_expiry = :expiry WHERE id = :id');
    $update->execute(['token' => $newToken, 'expiry' => $newExpiry, 'id' => $user['id']]);

    setcookie('lly_remember', $newToken, [
        'expires'  => (new DateTimeImmutable('+30 days'))->getTimestamp(),
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    return true;
}
