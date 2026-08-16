<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/public/l.php
 * PG-AI Pink Glove AI — public redemption endpoint for the Self-Destruct
 * Link module.
 * See core/EphemeralLinkManager.php::redeem() — the view is consumed via
 * a single atomic UPDATE, so two people opening the same link at once can
 * never both get the "last" view.
 *
 * Usage: /api/public/l.php?t=<token>
 */

require __DIR__ . '/../../core/EphemeralLinkManager.php';
require __DIR__ . '/../conexion.php';

header('Content-Type: text/html; charset=utf-8');

function lly_l_page(string $title, string $bodyEn, string $bodyEs, int $code = 200): never
{
    http_response_code($code);
    echo '<!DOCTYPE html><html lang="en" data-theme="light"><head><meta charset="UTF-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
        . '<meta name="robots" content="noindex, nofollow">'
        . '<title>' . htmlspecialchars($title, ENT_QUOTES) . ' · Lover Lips Yachts</title>'
        . '<link rel="stylesheet" href="/assets/css/style.css?v=' . filemtime(__DIR__ . '/../../assets/css/style.css') . '"></head>'
        . '<body data-active-lang="en"><main class="section"><div class="container ephemeral-page ephemeral-page--notice">'
        . '<h1>' . htmlspecialchars($title, ENT_QUOTES) . '</h1>'
        . '<p data-lang="en">' . $bodyEn . '</p>'
        . '<p data-lang="es">' . $bodyEs . '</p>'
        . '</div></main></body></html>';
    exit;
}

$token = trim((string) ($_GET['t'] ?? ''));
if ($token === '' || !preg_match('/^[A-Za-z0-9\-_]{20,64}$/', $token)) {
    lly_l_page(
        'Link not found',
        'This private link is invalid or malformed.',
        'Este enlace privado no es válido.',
        404,
    );
}

try {
    $pdo = Conexion::getConnection();
} catch (RuntimeException $e) {
    error_log('[PG-AI · l.php] DB unavailable: ' . $e->getMessage());
    lly_l_page(
        'Temporarily unavailable',
        'This link cannot be opened right now — please try again in a moment.',
        'Este enlace no se puede abrir en este momento — intenta de nuevo en unos minutos.',
        503,
    );
}

$link = EphemeralLinkManager::redeem($pdo, $token);

if ($link === null) {
    lly_l_page(
        'This link has expired',
        'This private link has reached its view limit or was revoked by the owner. Please request a new one.',
        'Este enlace privado alcanzó su límite de vistas o fue revocado. Solicita uno nuevo.',
        410,
    );
}

if (!empty($link['target_url'])) {
    header('Location: ' . $link['target_url'], true, 302);
    exit;
}

$remaining = max(0, (int) $link['max_views'] - (int) $link['view_count']);

echo '<!DOCTYPE html><html lang="en" data-theme="light"><head><meta charset="UTF-8">'
    . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
    . '<meta name="robots" content="noindex, nofollow">'
    . '<title>' . htmlspecialchars($link['title'], ENT_QUOTES) . ' · Lover Lips Yachts</title>'
    . '<link rel="stylesheet" href="/assets/css/style.css?v=' . filemtime(__DIR__ . '/../../assets/css/style.css') . '"></head>'
    . '<body data-active-lang="en"><main class="section"><div class="container ephemeral-page">'
    . '<h1>' . htmlspecialchars($link['title'], ENT_QUOTES) . '</h1>'
    . '<div class="ephemeral-page-body">' . $link['payload_html'] . '</div>'
    . '<p class="ephemeral-page-remaining">'
    . '<span data-lang="en">This private link has ' . $remaining . ' view(s) left before it self-destructs.</span>'
    . '<span data-lang="es">Este enlace privado tiene ' . $remaining . ' vista(s) restante(s) antes de autodestruirse.</span>'
    . '</p></div></main></body></html>';
