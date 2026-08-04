<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — core/FleetCatalogRepository.php
 * PG-AI Pink Glove AI — single read path for `ll_fleet_catalog`
 * (sql/005_create_ll_fleet_catalog.sql). Two consumers share this instead
 * of each hand-maintaining its own copy of the same 3 verified vessels:
 *
 *   - propuestas.php (Accordion 1, private) — human-facing table.
 *   - core/ProxyBridge.php — substitutes the {{FLEET_CATALOG_TABLE}}
 *     placeholder in core/prompts/pg_ai_lester_master.md §2 with a
 *     markdown table built from the same rows, so the AI's fleet facts
 *     can never drift from what propuestas.php shows Lester.
 *
 * Mandamiento 4 (Anti-Alucinación) still applies to the CRUD methods added
 * 2026-08-03 (api/fleet_catalog.php, pg_ai_hub.php Section E — Lester's own
 * Fleet Catalog editor): a human (Lester) is the one flipping
 * `verification_status` to 'verified' from the dashboard UI — this class
 * never does that automatically, and `listVerified()`/
 * `renderPromptMarkdownTable()` (what the AI actually sees) keep ignoring
 * every row still marked 'pending', new or old.
 */
final class FleetCatalogRepository
{
    /** Fixed business fact (fleet size), not derived from the catalog table — most vessels have no row yet. */
    public const TOTAL_FLEET_SIZE = 42;

    /** Whitelisted columns the owner-only CRUD editor may write — never expose id/created_at/updated_at to client input. */
    private const EDITABLE_FIELDS = [
        'vessel_name', 'vessel_slug', 'role_label_en', 'role_label_es',
        'max_pax', 'length_ft', 'rate_note_en', 'rate_note_es',
        'status_pill', 'verification_status', 'display_order',
    ];

    /** @return list<array<string, mixed>> Verified vessels, in display order. Empty array (never throws) if the table isn't provisioned yet. */
    public static function listVerified(PDO $pdo): array
    {
        try {
            $stmt = $pdo->query(
                "SELECT vessel_name, vessel_slug, role_label_en, role_label_es,
                        max_pax, rate_note_en, rate_note_es, status_pill
                 FROM ll_fleet_catalog
                 WHERE verification_status = 'verified'
                 ORDER BY display_order ASC, vessel_name ASC"
            );
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('[PG-AI · FleetCatalogRepository] ll_fleet_catalog not ready: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Renders the exact markdown table that replaces {{FLEET_CATALOG_TABLE}}
     * in the system prompt. Falls back to a short, honest sentence (never a
     * blank table, never invented numbers) if the DB has no verified rows.
     */
    public static function renderPromptMarkdownTable(PDO $pdo): string
    {
        $rows = self::listVerified($pdo);

        if ($rows === []) {
            return '_(Catálogo de flota temporalmente no disponible — no cites capacidad ni tarifas de ninguna embarcación hasta que este dato regrese.)_';
        }

        $lines   = [];
        $lines[] = '| Embarcación | Rol | Capacidad | Tarifa |';
        $lines[] = '|---|---|---|---|';

        foreach ($rows as $row) {
            $pax = $row['max_pax'] !== null
                ? 'Hasta ' . (int) $row['max_pax'] . ' invitados'
                : 'Por confirmar — nunca inventes un número';

            $lines[] = sprintf(
                '| **%s** | %s | %s | Por definir — nunca inventes un número |',
                self::mdEscape((string) $row['vessel_name']),
                self::mdEscape((string) $row['role_label_es']),
                $pax
            );
        }

        $pending = self::TOTAL_FLEET_SIZE - count($rows);
        if ($pending > 0) {
            $lines[] = '';
            $lines[] = "Las {$pending} embarcaciones restantes están pendientes de catalogación completa.";
        }

        return implode("\n", $lines);
    }

    private static function mdEscape(string $text): string
    {
        return str_replace('|', '\\|', $text);
    }

    /** @return list<array<string, mixed>> Every vessel (any verification_status), in display order — for the owner-only editor, not the AI. */
    public static function listAll(PDO $pdo): array
    {
        $stmt = $pdo->query(
            "SELECT id, vessel_name, vessel_slug, role_label_en, role_label_es,
                    max_pax, length_ft, rate_note_en, rate_note_es, status_pill,
                    verification_status, display_order
             FROM ll_fleet_catalog
             ORDER BY display_order ASC, vessel_name ASC"
        );
        return $stmt->fetchAll();
    }

    /** Inserts one vessel from whitelisted fields; returns the new row's id. Caller (api/fleet_catalog.php) validates required fields before calling. */
    public static function create(PDO $pdo, array $data): int
    {
        $fields = self::filterEditable($data);

        $columns = array_keys($fields);
        $placeholders = array_map(static fn (string $c): string => ':' . $c, $columns);

        $stmt = $pdo->prepare(
            'INSERT INTO ll_fleet_catalog (' . implode(', ', $columns) . ')
             VALUES (' . implode(', ', $placeholders) . ')'
        );
        $stmt->execute($fields);

        return (int) $pdo->lastInsertId();
    }

    /** Updates only the whitelisted fields present in $data for one vessel id. No-op (not an error) if $data has no editable fields. */
    public static function update(PDO $pdo, int $id, array $data): void
    {
        $fields = self::filterEditable($data);
        if ($fields === []) {
            return;
        }

        $assignments = array_map(static fn (string $c): string => "{$c} = :{$c}", array_keys($fields));

        $stmt = $pdo->prepare(
            'UPDATE ll_fleet_catalog SET ' . implode(', ', $assignments) . ' WHERE id = :id'
        );
        $stmt->execute($fields + ['id' => $id]);
    }

    public static function delete(PDO $pdo, int $id): void
    {
        $stmt = $pdo->prepare('DELETE FROM ll_fleet_catalog WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** Keeps only known columns from client input — the actual XSS/SQLi guard against a caller mistake, prepared statements handle the rest. */
    private static function filterEditable(array $data): array
    {
        $out = [];
        foreach (self::EDITABLE_FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                $out[$field] = $data[$field];
            }
        }
        return $out;
    }
}
