<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — core/CrewRepository.php
 * Inventory Checklist module (checklist.php) — Crew tab. Two related tables
 * (sql/013_create_ll_crew_and_inventory_catalog.sql):
 *
 *   - ll_crew_roles   — global position catalog (Captain, Chef, etc.),
 *     shared across every vessel. Add/edit/delete a position type here.
 *   - ll_crew_members — the roster of actual people, one row per person,
 *     scoped to one vessel via `vessel_name` (free text, same convention
 *     as ll_inventory_checklists.vessel_name — no FK to ll_fleet_catalog,
 *     which only has 3/42 vessels populated today).
 *
 * Mirrors core/FleetCatalogRepository.php's whitelisted-field CRUD pattern.
 */
final class CrewRepository
{
    private const ROLE_EDITABLE_FIELDS = ['label_en', 'label_es', 'display_order'];

    private const MEMBER_EDITABLE_FIELDS = [
        'vessel_name', 'role_id', 'full_name', 'phone', 'whatsapp',
        'email', 'status', 'note', 'display_order',
    ];

    // ── Roles (positions catalog) ───────────────────────────────────────

    /** @return list<array<string, mixed>> Every role, in display order. Empty (never throws) if the table isn't provisioned yet. */
    public static function listRoles(PDO $pdo): array
    {
        try {
            $stmt = $pdo->query(
                'SELECT id, label_en, label_es, display_order FROM ll_crew_roles
                 ORDER BY display_order ASC, label_en ASC'
            );
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('[LLY · CrewRepository] ll_crew_roles not ready: ' . $e->getMessage());
            return [];
        }
    }

    public static function createRole(PDO $pdo, array $data): int
    {
        $fields = self::filter($data, self::ROLE_EDITABLE_FIELDS);
        $columns = array_keys($fields);
        $stmt = $pdo->prepare(
            'INSERT INTO ll_crew_roles (' . implode(', ', $columns) . ')
             VALUES (' . implode(', ', array_map(static fn (string $c): string => ':' . $c, $columns)) . ')'
        );
        $stmt->execute($fields);
        return (int) $pdo->lastInsertId();
    }

    public static function updateRole(PDO $pdo, int $id, array $data): void
    {
        $fields = self::filter($data, self::ROLE_EDITABLE_FIELDS);
        if ($fields === []) {
            return;
        }
        $assignments = array_map(static fn (string $c): string => "{$c} = :{$c}", array_keys($fields));
        $stmt = $pdo->prepare('UPDATE ll_crew_roles SET ' . implode(', ', $assignments) . ' WHERE id = :id');
        $stmt->execute($fields + ['id' => $id]);
    }

    /**
     * Deletes a role — blocked (FK RESTRICT) while any crew member still
     * references it. Returns false instead of letting the PDOException
     * bubble, so the API layer can turn it into a clean 409 message.
     */
    public static function deleteRole(PDO $pdo, int $id): bool
    {
        try {
            $stmt = $pdo->prepare('DELETE FROM ll_crew_roles WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return true;
        } catch (\PDOException $e) {
            error_log('[LLY · CrewRepository] deleteRole blocked (likely in use): ' . $e->getMessage());
            return false;
        }
    }

    // ── Crew members (roster, per vessel) ───────────────────────────────

    /** @return list<array<string, mixed>> Roster for one vessel, role label joined in, in display order. */
    public static function listMembers(PDO $pdo, string $vesselName): array
    {
        try {
            $stmt = $pdo->prepare(
                'SELECT m.id, m.vessel_name, m.role_id, r.label_en AS role_label_en, r.label_es AS role_label_es,
                        m.full_name, m.phone, m.whatsapp, m.email, m.status, m.note, m.display_order
                 FROM ll_crew_members m
                 JOIN ll_crew_roles r ON r.id = m.role_id
                 WHERE m.vessel_name = :vessel
                 ORDER BY m.display_order ASC, m.full_name ASC'
            );
            $stmt->execute(['vessel' => $vesselName]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('[LLY · CrewRepository] ll_crew_members not ready: ' . $e->getMessage());
            return [];
        }
    }

    /** @return list<string> Distinct vessel names already used across the crew roster (for the vessel autocomplete). */
    public static function listVesselNames(PDO $pdo): array
    {
        try {
            $stmt = $pdo->query('SELECT DISTINCT vessel_name FROM ll_crew_members ORDER BY vessel_name ASC');
            return array_column($stmt->fetchAll(), 'vessel_name');
        } catch (\PDOException) {
            return [];
        }
    }

    public static function createMember(PDO $pdo, array $data): int
    {
        $fields = self::filter($data, self::MEMBER_EDITABLE_FIELDS);
        $columns = array_keys($fields);
        $stmt = $pdo->prepare(
            'INSERT INTO ll_crew_members (' . implode(', ', $columns) . ')
             VALUES (' . implode(', ', array_map(static fn (string $c): string => ':' . $c, $columns)) . ')'
        );
        $stmt->execute($fields);
        return (int) $pdo->lastInsertId();
    }

    public static function updateMember(PDO $pdo, int $id, array $data): void
    {
        $fields = self::filter($data, self::MEMBER_EDITABLE_FIELDS);
        if ($fields === []) {
            return;
        }
        $assignments = array_map(static fn (string $c): string => "{$c} = :{$c}", array_keys($fields));
        $stmt = $pdo->prepare('UPDATE ll_crew_members SET ' . implode(', ', $assignments) . ' WHERE id = :id');
        $stmt->execute($fields + ['id' => $id]);
    }

    public static function deleteMember(PDO $pdo, int $id): void
    {
        $stmt = $pdo->prepare('DELETE FROM ll_crew_members WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** Keeps only known columns from client input — filters the actual XSS/SQLi guard against a caller mistake, prepared statements handle the rest. */
    private static function filter(array $data, array $allowed): array
    {
        $out = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $out[$field] = $data[$field];
            }
        }
        return $out;
    }
}
