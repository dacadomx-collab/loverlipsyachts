<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — core/pgai_templates.php
 * PG-AI Pink Glove AI — "PINK LIPS Experience" quick-fill quote templates.
 * Single source of truth, included by pg_ai_hub.php (Section D — Template
 * & Ephemeral Links Manager). Same "no DB table yet, hand-edited PHP
 * array" pattern as $lly_reportes in dashboard.php. Pricing/inclusions/
 * policy text authorized directly by the Architect; the deposit and
 * weather/captain clauses are reused verbatim from propuestas.php's
 * Navigation Policies accordion rather than re-worded here, so both
 * surfaces stay in sync.
 *
 * @return array<string, array{title_internal: string, html: string}>
 */
function lly_pgai_quote_templates(): array
{
    return [
        'balandra' => [
            'title_internal' => 'PINK LIPS Experience — Balandra Beach',
            'html' => '<h3><span data-lang="en">PINK LIPS Experience — Balandra Beach</span><span data-lang="es">Experiencia PINK LIPS — Playa Balandra</span></h3>'
                . '<p><span data-lang="en"><strong>Rate:</strong> $19,000 MXN</span><span data-lang="es"><strong>Tarifa:</strong> $19,000 MXN</span></p>'
                . '<p><span data-lang="en">A private half-day charter to one of the most photographed beaches in Mexico — calm turquoise water, the iconic &quot;mushroom rock,&quot; and a fully catered gourmet experience on board.</span>'
                . '<span data-lang="es">Un charter privado de medio día a una de las playas más fotografiadas de México — agua turquesa en calma, la icónica &quot;piedra hongo&quot; y una experiencia gourmet completamente atendida a bordo.</span></p>'
                . '<ul>'
                . '<li><span data-lang="en">Gourmet onboard menu, prepared and served by the crew</span><span data-lang="es">Menú gourmet a bordo, preparado y servido por la tripulación</span></li>'
                . '<li><span data-lang="en">Open bar — premium spirits, wine and soft drinks</span><span data-lang="es">Barra libre — licores premium, vino y refrescos</span></li>'
                . '<li><span data-lang="en">Snorkel gear and paddle boards included</span><span data-lang="es">Equipo de snorkel y tablas de paddle incluidos</span></li>'
                . '<li><span data-lang="en">Professional crew and captain</span><span data-lang="es">Tripulación y capitán profesional</span></li>'
                . '</ul>'
                . '<h4><span data-lang="en">Booking &amp; Weather Policy</span><span data-lang="es">Política de Reserva y Clima</span></h4>'
                . '<ul>'
                . '<li><span data-lang="en">Full-day and event charters require 30% deposit at confirmation.</span><span data-lang="es">Charters de día completo y eventos requieren 30% de anticipo al confirmar.</span></li>'
                . '<li><span data-lang="en">Captain reserves the right to modify or cancel the route due to weather conditions — guest safety is always the priority.</span><span data-lang="es">El capitán se reserva el derecho de modificar o cancelar la ruta por condiciones climáticas — la seguridad del pasajero es siempre la prioridad.</span></li>'
                . '</ul>',
        ],
        'espiritu_santo' => [
            'title_internal' => 'PINK LIPS Experience — Isla Espíritu Santo',
            'html' => '<h3><span data-lang="en">PINK LIPS Experience — Espíritu Santo Island</span><span data-lang="es">Experiencia PINK LIPS — Isla Espíritu Santo</span></h3>'
                . '<p><span data-lang="en"><strong>Rate:</strong> $24,000 MXN</span><span data-lang="es"><strong>Tarifa:</strong> $24,000 MXN</span></p>'
                . '<p><span data-lang="en">A full-day VIP expedition to the Espíritu Santo Island UNESCO World Heritage site — sea lion colony, hidden coves, and a fully catered gourmet experience on board.</span>'
                . '<span data-lang="es">Una expedición VIP de día completo a la Isla Espíritu Santo, Patrimonio de la Humanidad UNESCO — colonia de lobos marinos, calas escondidas y una experiencia gourmet completamente atendida a bordo.</span></p>'
                . '<ul>'
                . '<li><span data-lang="en">Gourmet onboard menu, prepared and served by the crew</span><span data-lang="es">Menú gourmet a bordo, preparado y servido por la tripulación</span></li>'
                . '<li><span data-lang="en">Open bar — premium spirits, wine and soft drinks</span><span data-lang="es">Barra libre — licores premium, vino y refrescos</span></li>'
                . '<li><span data-lang="en">Snorkel gear included — swim alongside the sea lion colony</span><span data-lang="es">Equipo de snorkel incluido — nada junto a la colonia de lobos marinos</span></li>'
                . '<li><span data-lang="en">Professional crew and captain</span><span data-lang="es">Tripulación y capitán profesional</span></li>'
                . '</ul>'
                . '<h4><span data-lang="en">Booking &amp; Weather Policy</span><span data-lang="es">Política de Reserva y Clima</span></h4>'
                . '<ul>'
                . '<li><span data-lang="en">Full-day and event charters require 30% deposit at confirmation.</span><span data-lang="es">Charters de día completo y eventos requieren 30% de anticipo al confirmar.</span></li>'
                . '<li><span data-lang="en">Captain reserves the right to modify or cancel the route due to weather conditions — guest safety is always the priority.</span><span data-lang="es">El capitán se reserva el derecho de modificar o cancelar la ruta por condiciones climáticas — la seguridad del pasajero es siempre la prioridad.</span></li>'
                . '</ul>',
        ],
    ];
}
