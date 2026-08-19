<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — core/pgai_templates.php
 * PG-AI Pink Glove AI — "PINK LIPS Experience" quick-fill quote templates.
 * Single source of truth, consumed by api/public/l.php (the redeemed
 * quote page) via core/PgAiActionProcessor.php::resolveQuoteLink().
 * Pricing/inclusions/policy text authorized directly by the Architect;
 * the deposit and weather/captain clauses are reused verbatim from
 * propuestas.php's Navigation Policies accordion rather than re-worded
 * here, so both surfaces stay in sync.
 *
 * (2026-08-18) Restructured from one HTML blob per route into explicit
 * fields — api/public/l.php renders each into its own "Clear Luxury /
 * Pink Glove" card (Experience / Rate / Inclusions / Policies) instead
 * of dumping a single unstyled paragraph. Every guest-facing string is
 * an [en, es] pair, rendered through the same data-lang toggle every
 * other page on this site already uses — never concatenated into one
 * line, which is what caused the EN/ES mixing bug this restructure fixes.
 *
 * @return array<string, array{
 *   title_internal: string,
 *   title: array{en: string, es: string},
 *   rate_mxn: int,
 *   description: array{en: string, es: string},
 *   inclusions: list<array{icon: string, en: string, es: string}>,
 *   policies: list<array{en: string, es: string}>,
 * }>
 */
function lly_pgai_quote_templates(): array
{
    return [
        'balandra' => [
            'title_internal' => 'PINK LIPS Experience — Balandra Beach',
            'title' => [
                'en' => 'PINK LIPS Experience — Balandra Beach',
                'es' => 'Experiencia PINK LIPS — Playa Balandra',
            ],
            'rate_mxn' => 19000,
            'description' => [
                'en' => 'A private half-day charter to one of the most photographed beaches in Mexico — calm turquoise water, the iconic "mushroom rock," and a fully catered gourmet experience on board.',
                'es' => 'Un charter privado de medio día a una de las playas más fotografiadas de México — agua turquesa en calma, la icónica "piedra hongo" y una experiencia gourmet completamente atendida a bordo.',
            ],
            'inclusions' => [
                ['icon' => '🥂', 'en' => 'Gourmet onboard menu, prepared and served by the crew', 'es' => 'Menú gourmet a bordo, preparado y servido por la tripulación'],
                ['icon' => '🥂', 'en' => 'Open bar — premium spirits, wine and soft drinks', 'es' => 'Barra libre — licores premium, vino y refrescos'],
                ['icon' => '🤿', 'en' => 'Snorkel gear and paddle boards included', 'es' => 'Equipo de snorkel y tablas de paddle incluidos'],
                ['icon' => '👨‍✈️', 'en' => 'Professional crew and captain', 'es' => 'Tripulación y capitán profesional'],
            ],
            'policies' => [
                ['en' => 'Full-day and event charters require 30% deposit at confirmation.', 'es' => 'Charters de día completo y eventos requieren 30% de anticipo al confirmar.'],
                ['en' => 'Captain reserves the right to modify or cancel the route due to weather conditions — guest safety is always the priority.', 'es' => 'El capitán se reserva el derecho de modificar o cancelar la ruta por condiciones climáticas — la seguridad del pasajero es siempre la prioridad.'],
            ],
        ],
        'espiritu_santo' => [
            'title_internal' => 'PINK LIPS Experience — Isla Espíritu Santo',
            'title' => [
                'en' => 'PINK LIPS Experience — Espíritu Santo Island',
                'es' => 'Experiencia PINK LIPS — Isla Espíritu Santo',
            ],
            'rate_mxn' => 24000,
            'description' => [
                'en' => 'A full-day VIP expedition to the Espíritu Santo Island UNESCO World Heritage site — sea lion colony, hidden coves, and a fully catered gourmet experience on board.',
                'es' => 'Una expedición VIP de día completo a la Isla Espíritu Santo, Patrimonio de la Humanidad UNESCO — colonia de lobos marinos, calas escondidas y una experiencia gourmet completamente atendida a bordo.',
            ],
            'inclusions' => [
                ['icon' => '🥂', 'en' => 'Gourmet onboard menu, prepared and served by the crew', 'es' => 'Menú gourmet a bordo, preparado y servido por la tripulación'],
                ['icon' => '🥂', 'en' => 'Open bar — premium spirits, wine and soft drinks', 'es' => 'Barra libre — licores premium, vino y refrescos'],
                ['icon' => '🤿', 'en' => 'Snorkel gear included — swim alongside the sea lion colony', 'es' => 'Equipo de snorkel incluido — nada junto a la colonia de lobos marinos'],
                ['icon' => '👨‍✈️', 'en' => 'Professional crew and captain', 'es' => 'Tripulación y capitán profesional'],
            ],
            'policies' => [
                ['en' => 'Full-day and event charters require 30% deposit at confirmation.', 'es' => 'Charters de día completo y eventos requieren 30% de anticipo al confirmar.'],
                ['en' => 'Captain reserves the right to modify or cancel the route due to weather conditions — guest safety is always the priority.', 'es' => 'El capitán se reserva el derecho de modificar o cancelar la ruta por condiciones climáticas — la seguridad del pasajero es siempre la prioridad.'],
            ],
        ],
    ];
}
