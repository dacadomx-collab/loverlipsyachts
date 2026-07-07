<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — core/dev_bypass.php
 * LOCAL-DEV-ONLY session seed so pages under core/ can be smoke-tested on
 * XAMPP without a manual login.
 *
 * Deliberately NOT gated on $_SERVER['HTTP_HOST'] — the Host header is
 * client-supplied and trivially spoofed (curl -H "Host: localhost", a
 * misconfigured reverse proxy, etc.), so trusting it would open a real
 * backdoor if this file ever shipped to production. REMOTE_ADDR is set by
 * the web server from the actual TCP connection, so loopback here means
 * the request physically originated on this machine.
 *
 * Seeds the SAME session keys lly_is_authenticated() checks (lly_user_id),
 * so this integrates with the real auth system instead of running a
 * second, parallel gate that could diverge from it.
 *
 * Caller is responsible for requiring api/conexion.php and
 * core/auth_check.php first.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$lly_is_loopback = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);

if ($lly_is_loopback && empty($_SESSION['lly_user_id'])) {
    $_SESSION['lly_user_id'] = 1;
    $_SESSION['lly_email']   = 'dev-local@loverlipsyachts.test';
}
