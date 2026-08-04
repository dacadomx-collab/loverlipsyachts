<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — core/EphemeralLinkManager.php
 * PG-AI Pink Glove AI — Self-Destruct Link module. See
 * modulos/MOD_OPERADOR_COGNITIVO_OMNICANAL.md,
 * section on "Enlaces Efimeros / Self-Destruct Link Architecture", for the
 * agnostic blueprint this class implements for this project.
 *
 * A link dies after `max_views` reads (default 3, owner-configurable both
 * globally and per link). The view gate is a single atomic UPDATE — no
 * check-then-act race between two people opening the same link at once.
 *
 * Schema: sql/004_create_ll_ephemeral_links.sql (`ll_ephemeral_links`,
 * `ll_app_settings`).
 */
final class EphemeralLinkManager
{
    private const DEFAULT_SETTING_KEY = 'ephemeral_link_default_max_views';
    private const FALLBACK_MAX_VIEWS  = 3;
    private const MAX_ALLOWED_VIEWS   = 50;

    public static function getDefaultMaxViews(PDO $pdo): int
    {
        $stmt = $pdo->prepare('SELECT setting_value FROM ll_app_settings WHERE setting_key = :k LIMIT 1');
        $stmt->execute(['k' => self::DEFAULT_SETTING_KEY]);
        $value = $stmt->fetchColumn();

        return $value !== false ? max(1, (int) $value) : self::FALLBACK_MAX_VIEWS;
    }

    public static function setDefaultMaxViews(PDO $pdo, int $maxViews): void
    {
        $maxViews = self::clampMaxViews($maxViews);

        $stmt = $pdo->prepare(
            'INSERT INTO ll_app_settings (setting_key, setting_value) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = :v2'
        );
        $stmt->execute(['k' => self::DEFAULT_SETTING_KEY, 'v' => (string) $maxViews, 'v2' => (string) $maxViews]);
    }

    /**
     * Creates a new ephemeral link. Exactly one of $payloadHtml / $targetUrl
     * must be provided — payload is self-contained content, target_url is
     * an internal redirect after the view gate.
     */
    public static function create(
        PDO $pdo,
        int $createdBy,
        string $title,
        string $resourceType,
        ?string $payloadHtml,
        ?string $targetUrl,
        ?int $maxViews = null,
    ): array {
        if (($payloadHtml === null || $payloadHtml === '') && ($targetUrl === null || $targetUrl === '')) {
            throw new InvalidArgumentException('Either payloadHtml or targetUrl is required.');
        }
        if (!in_array($resourceType, ['quote', 'itinerary', 'custom'], true)) {
            throw new InvalidArgumentException('Invalid resource_type.');
        }

        $maxViews = $maxViews !== null ? self::clampMaxViews($maxViews) : self::getDefaultMaxViews($pdo);
        $token    = self::generateToken();

        $stmt = $pdo->prepare(
            'INSERT INTO ll_ephemeral_links
                (token, title, resource_type, payload_html, target_url, max_views, created_by)
             VALUES (:token, :title, :resource_type, :payload_html, :target_url, :max_views, :created_by)'
        );
        $stmt->execute([
            'token'         => $token,
            'title'         => $title,
            'resource_type' => $resourceType,
            'payload_html'  => $payloadHtml !== '' ? $payloadHtml : null,
            'target_url'    => $targetUrl !== '' ? $targetUrl : null,
            'max_views'     => $maxViews,
            'created_by'    => $createdBy,
        ]);

        return self::findById($pdo, (int) $pdo->lastInsertId());
    }

    /**
     * Atomically consumes one view. Returns the link row on success, or
     * null if the token doesn't exist / is already expired-revoked / has
     * hit its view cap. The WHERE clause conditions on view_count < max_views
     * so two simultaneous requests can never both "win" the last view.
     */
    public static function redeem(PDO $pdo, string $token): ?array
    {
        $stmt = $pdo->prepare(
            "UPDATE ll_ephemeral_links
             SET view_count = view_count + 1,
                 last_viewed_at = NOW(),
                 status = IF(view_count + 1 >= max_views, 'expired', status)
             WHERE token = :token AND status = 'active' AND view_count < max_views"
        );
        $stmt->execute(['token' => $token]);

        if ($stmt->rowCount() === 0) {
            return null;
        }

        $select = $pdo->prepare('SELECT * FROM ll_ephemeral_links WHERE token = :token LIMIT 1');
        $select->execute(['token' => $token]);
        $row = $select->fetch();

        return $row ?: null;
    }

    public static function listRecent(PDO $pdo, int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));
        $stmt  = $pdo->query(
            "SELECT id, token, title, resource_type, max_views, view_count, status, created_at, last_viewed_at
             FROM ll_ephemeral_links
             ORDER BY created_at DESC
             LIMIT {$limit}"
        );

        return $stmt->fetchAll();
    }

    /** Cannot set below the views already consumed — that would make an already-viewed link look alive. */
    public static function updateMaxViews(PDO $pdo, int $id, int $maxViews): bool
    {
        $maxViews = self::clampMaxViews($maxViews);

        $stmt = $pdo->prepare(
            "UPDATE ll_ephemeral_links
             SET max_views = :max_views,
                 status = IF(view_count >= :max_views2, 'expired', 'active')
             WHERE id = :id AND view_count <= :max_views3 AND status != 'revoked'"
        );
        $stmt->execute([
            'max_views'  => $maxViews,
            'max_views2' => $maxViews,
            'max_views3' => $maxViews,
            'id'         => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function revoke(PDO $pdo, int $id): bool
    {
        $stmt = $pdo->prepare("UPDATE ll_ephemeral_links SET status = 'revoked' WHERE id = :id AND status != 'revoked'");
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    private static function findById(PDO $pdo, int $id): array
    {
        $stmt = $pdo->prepare('SELECT * FROM ll_ephemeral_links WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new RuntimeException('Ephemeral link not found immediately after insert.');
        }

        return $row;
    }

    private static function clampMaxViews(int $maxViews): int
    {
        return max(1, min(self::MAX_ALLOWED_VIEWS, $maxViews));
    }

    /** URL-safe token, no padding — matches CHAR(43) in the schema. */
    private static function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
