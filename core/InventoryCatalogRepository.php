<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — core/InventoryCatalogRepository.php
 * Inventory Checklist module (checklist.php) — Kitchen Utensils tab.
 * Reads/writes `ll_inventory_catalog` (sql/013_create_ll_crew_and_inventory_
 * catalog.sql): a flat, per-vessel catalog (no separate lookup table — each
 * row IS one utensil for one vessel, e.g. "Toaster x1 on NOMADA"). `category`
 * defaults to 'kitchen' (the only category this v1 UI exposes) but the
 * column is reserved so a future equipment group doesn't need a migration.
 *
 * Mirrors core/FleetCatalogRepository.php's whitelisted-field CRUD pattern.
 */
final class InventoryCatalogRepository
{
    private const EDITABLE_FIELDS = [
        'vessel_name', 'category', 'name_en', 'name_es',
        'quantity', 'condition_status', 'note', 'display_order',
    ];

    /** @return list<array<string, mixed>> Catalog rows for one vessel + category, in display order. */
    public static function listItems(PDO $pdo, string $vesselName, string $category = 'kitchen'): array
    {
        try {
            $stmt = $pdo->prepare(
                'SELECT id, vessel_name, category, name_en, name_es, quantity, condition_status, note, display_order
                 FROM ll_inventory_catalog
                 WHERE vessel_name = :vessel AND category = :category
                 ORDER BY display_order ASC, name_en ASC'
            );
            $stmt->execute(['vessel' => $vesselName, 'category' => $category]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('[LLY · InventoryCatalogRepository] ll_inventory_catalog not ready: ' . $e->getMessage());
            return [];
        }
    }

    /** @return list<string> Distinct vessel names already used in this catalog (for the vessel autocomplete). */
    public static function listVesselNames(PDO $pdo): array
    {
        try {
            $stmt = $pdo->query('SELECT DISTINCT vessel_name FROM ll_inventory_catalog ORDER BY vessel_name ASC');
            return array_column($stmt->fetchAll(), 'vessel_name');
        } catch (\PDOException) {
            return [];
        }
    }

    public static function create(PDO $pdo, array $data): int
    {
        $fields = self::filter($data);
        $columns = array_keys($fields);
        $stmt = $pdo->prepare(
            'INSERT INTO ll_inventory_catalog (' . implode(', ', $columns) . ')
             VALUES (' . implode(', ', array_map(static fn (string $c): string => ':' . $c, $columns)) . ')'
        );
        $stmt->execute($fields);
        return (int) $pdo->lastInsertId();
    }

    public static function update(PDO $pdo, int $id, array $data): void
    {
        $fields = self::filter($data);
        if ($fields === []) {
            return;
        }
        $assignments = array_map(static fn (string $c): string => "{$c} = :{$c}", array_keys($fields));
        $stmt = $pdo->prepare('UPDATE ll_inventory_catalog SET ' . implode(', ', $assignments) . ' WHERE id = :id');
        $stmt->execute($fields + ['id' => $id]);
    }

    public static function delete(PDO $pdo, int $id): void
    {
        $stmt = $pdo->prepare('DELETE FROM ll_inventory_catalog WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    private static function filter(array $data): array
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
