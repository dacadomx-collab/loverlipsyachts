<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — core/FleetCatalogPhotoRepository.php
 * Photo gallery for one Fleet Catalog vessel (`ll_fleet_catalog_photos`,
 * sql/015). Separate from FleetCatalogRepository because this is a 1:many
 * child table (several photos per vessel), not another flat column — kept
 * as its own small repository the same way NotificationTemplateRepository
 * is split out from the vessel-facts repository next to it.
 */
final class FleetCatalogPhotoRepository
{
    /** @return list<array<string, mixed>> Photos for one vessel, in display order. Empty (never throws) if the table isn't provisioned yet. */
    public static function listForVessel(PDO $pdo, int $fleetCatalogId): array
    {
        try {
            $stmt = $pdo->prepare(
                'SELECT id, photo_path, display_order
                 FROM ll_fleet_catalog_photos
                 WHERE fleet_catalog_id = :id
                 ORDER BY display_order ASC, id ASC'
            );
            $stmt->execute(['id' => $fleetCatalogId]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('[PG-AI · FleetCatalogPhotoRepository] ll_fleet_catalog_photos not ready: ' . $e->getMessage());
            return [];
        }
    }

    public static function create(PDO $pdo, int $fleetCatalogId, string $photoPath, int $displayOrder): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO ll_fleet_catalog_photos (fleet_catalog_id, photo_path, display_order)
             VALUES (:fleet_catalog_id, :photo_path, :display_order)'
        );
        $stmt->execute([
            'fleet_catalog_id' => $fleetCatalogId,
            'photo_path'       => $photoPath,
            'display_order'    => $displayOrder,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /** Returns the deleted row's photo_path (so the caller can unlink the file), or null if no such row existed. */
    public static function delete(PDO $pdo, int $photoId): ?string
    {
        $stmt = $pdo->prepare('SELECT photo_path FROM ll_fleet_catalog_photos WHERE id = :id');
        $stmt->execute(['id' => $photoId]);
        $path = $stmt->fetchColumn();
        if ($path === false) {
            return null;
        }

        $del = $pdo->prepare('DELETE FROM ll_fleet_catalog_photos WHERE id = :id');
        $del->execute(['id' => $photoId]);

        return (string) $path;
    }

    /** @return list<string> Every photo_path still on file for one vessel — used to clean up assets/img/fleet/ when the vessel itself is deleted. */
    public static function listPathsForVessel(PDO $pdo, int $fleetCatalogId): array
    {
        try {
            $stmt = $pdo->prepare('SELECT photo_path FROM ll_fleet_catalog_photos WHERE fleet_catalog_id = :id');
            $stmt->execute(['id' => $fleetCatalogId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            return [];
        }
    }
}
