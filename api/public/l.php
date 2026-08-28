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
 * (2026-08-18) Rebuilt: the stylesheet was linked with a root-relative
 * path (`/assets/css/style.css`), which 404s on this project — it never
 * lives at the domain root, it's mounted under a subfolder in every
 * environment (/loverlipsyachts/ locally, /cockpit/ on production —
 * same lesson as core/PgAiActionProcessor.php::publicUrl()). With no CSS
 * loaded, the data-lang="en"/"es" spans this page (and every quote
 * template) relies on to hide one language had nothing hiding either one
 * — both rendered at once, unstyled, which is what actually looked like
 * "English and Spanish mixed on one line." The template content itself
 * (core/pgai_templates.php) was never broken. Reads APP_URL_LOCAL/
 * APP_COCKPIT_URL (core/.env) for every asset path, adds the page chrome
 * (gradient header, language toggle, security badge, WhatsApp CTA) around
 * whatever payload_html this link carries — quote or plain owner-typed
 * content.
 *
 * Usage:
 *   /api/public/l.php?t=<token>              real, self-destructing link
 *   /api/public/l.php?sample=balandra|espiritu_santo   stable demo preview,
 *     never expires, no DB/token involved — for owners sharing "what a
 *     quote looks like" without burning a real link's view count.
 */

require __DIR__ . '/../../core/EphemeralLinkManager.php';
require __DIR__ . '/../../core/pgai_templates.php';
require_once __DIR__ . '/../../core/PgAiActionProcessor.php';
require __DIR__ . '/../conexion.php';

header('Content-Type: text/html; charset=utf-8');

/**
 * Reads the APP_URL_LOCAL/APP_COCKPIT_URL pair already provisioned in
 * core/.env (project scaffolding, 2026-05-30) — picked via the same
 * local-vs-production detection api/conexion.php::isLocalRequest() uses
 * for DB_HOST_LOCAL. Sibling copy of core/PgAiActionProcessor.php's helper
 * — this file has no shared "core bootstrap" to hang a common one off of.
 */
function lly_l_read_app_base_url(): string
{
    $path = __DIR__ . '/../../core/.env';
    $env  = [];
    if (is_readable($path)) {
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || $line[0] === ';' || !str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $env[trim($k)] = trim($v, " \t\"'");
        }
    }

    $httpHost   = (string) ($_SERVER['HTTP_HOST']   ?? '');
    $serverAddr = (string) ($_SERVER['SERVER_ADDR'] ?? '');
    $remoteAddr = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $isLocal    = in_array($httpHost, ['localhost', '127.0.0.1'], true)
        || str_starts_with($httpHost, 'localhost:') || str_starts_with($httpHost, '127.0.0.1:')
        || in_array($serverAddr, ['127.0.0.1', '::1'], true)
        || in_array($remoteAddr, ['127.0.0.1', '::1'], true);

    return $isLocal ? ($env['APP_URL_LOCAL'] ?? '') : rtrim($env['APP_COCKPIT_URL'] ?? '', '/');
}

function lly_l_asset_base(): string
{
    $appUrl = lly_l_read_app_base_url();
    if ($appUrl !== '') {
        return rtrim($appUrl, '/');
    }
    // Fallback only — used if neither env var is configured yet. Assumes
    // the codebase lives at the domain root, which is wrong on this
    // project in every real environment — see this file's docblock.
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

const LLY_WHATSAPP_CONTACT = '17022048894'; // published business number (loverlipsyachts.com structured data) — wa.me format, no leading +

function lly_l_page(string $title, string $bodyEn, string $bodyEs, int $code = 200): never
{
    $base = lly_l_asset_base();
    http_response_code($code);
    echo '<!DOCTYPE html><html lang="en" data-theme="light"><head><meta charset="UTF-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
        . '<meta name="robots" content="noindex, nofollow">'
        . '<title>' . htmlspecialchars($title, ENT_QUOTES) . ' · Lover Lips Yachts</title>'
        . '<link rel="stylesheet" href="' . $base . '/assets/css/style.css?v=' . filemtime(__DIR__ . '/../../assets/css/style.css') . '"></head>'
        . '<body data-active-lang="en"><main class="section"><div class="container ephemeral-page ephemeral-page--notice">'
        . '<h1>' . htmlspecialchars($title, ENT_QUOTES) . '</h1>'
        . '<p data-lang="en">' . $bodyEn . '</p>'
        . '<p data-lang="es">' . $bodyEs . '</p>'
        . '</div></main></body></html>';
    exit;
}

/** Shared by the real (self-destructing) path and the ?sample= preview path — same chrome (header, lang toggle, WhatsApp CTA), only the security-badge text and payload differ. */
function lly_l_render_quote_page(string $title, string $payloadHtml, string $badgeHtml): never
{
    $base      = lly_l_asset_base();
    $safeTitle = htmlspecialchars($title, ENT_QUOTES);
    $waText    = rawurlencode('Hi! I\'d like to confirm my reservation — ' . $title);
    $waHref    = 'https://wa.me/' . LLY_WHATSAPP_CONTACT . '?text=' . $waText;

    echo '<!DOCTYPE html><html lang="en" data-theme="light"><head><meta charset="UTF-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
        . '<meta name="robots" content="noindex, nofollow">'
        . '<title>' . $safeTitle . ' · Lover Lips Yachts</title>'
        . '<link rel="stylesheet" href="' . $base . '/assets/css/style.css?v=' . filemtime(__DIR__ . '/../../assets/css/style.css') . '">'
        . '<link rel="icon" type="image/png" href="' . $base . '/assets/img/logo.png"></head>'
        . '<body data-active-lang="en"><main class="quote-page">'

        . '<header class="quote-page-header">'
        . '<img class="quote-page-logo" src="' . $base . '/assets/img/logo.png" alt="Lover Lips Yachts" />'
        . '<div class="lang-toggle quote-page-lang" role="group" aria-label="Language / Idioma">'
        . '<button type="button" class="lang-btn active" id="btn-en" aria-pressed="true">EN</button>'
        . '<button type="button" class="lang-btn" id="btn-es" aria-pressed="false">ES</button>'
        . '</div>'
        . '<p class="quote-page-eyebrow"><span data-lang="en">Concierge IA Lover Lips · Private Quote</span><span data-lang="es">Concierge IA Lover Lips · Cotización Privada</span></p>'
        . '<h1>' . $safeTitle . '</h1>'
        . '</header>'

        . '<div class="container quote-page-body">'
        . $badgeHtml
        . '<div class="quote-card-grid">' . $payloadHtml . '</div>'
        . '<a class="quote-whatsapp-cta" href="' . htmlspecialchars($waHref, ENT_QUOTES) . '" target="_blank" rel="noopener noreferrer">'
        . '<span data-lang="en">💬 Confirm Reservation via WhatsApp</span>'
        . '<span data-lang="es">💬 Confirmar Reserva por WhatsApp</span>'
        . '</a>'
        . '</div>'
        . '</main>'
        . '<script src="' . $base . '/assets/js/main.js?v=' . filemtime(__DIR__ . '/../../assets/js/main.js') . '" defer></script>'
        . '</body></html>';
    exit;
}

/* ── ?sample= preview mode — no DB, no token, never expires ──────────── */
$sampleRoute = trim((string) ($_GET['sample'] ?? ''));
if ($sampleRoute !== '') {
    $templates = lly_pgai_quote_templates();
    if (!isset($templates[$sampleRoute])) {
        $sampleRoute = 'balandra'; // unknown/typo'd route — fall back to a valid demo rather than 404 a "sample" link
    }
    $template = $templates[$sampleRoute];

    $badge = '<p class="quote-security-badge quote-security-badge--sample">🎓 '
        . '<span data-lang="en">Sample quote — for demonstration only, not a real reservation.</span>'
        . '<span data-lang="es">Cotización de muestra — solo para demostración, no es una reserva real.</span>'
        . '</p>';

    lly_l_render_quote_page($template['title_internal'], PgAiActionProcessor::buildQuotePayloadHtml($template), $badge);
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
$badge = '<p class="quote-security-badge">🔒 '
    . '<span data-lang="en">Private link — ' . $remaining . ' view(s) left before it self-destructs.</span>'
    . '<span data-lang="es">Enlace privado — ' . $remaining . ' vista(s) restante(s) antes de autodestruirse.</span>'
    . '</p>';

lly_l_render_quote_page($link['title'], $link['payload_html'], $badge);
