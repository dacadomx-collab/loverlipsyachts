<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/fleet_catalog_photos.php
 * Owner-only endpoint for the Fleet Catalog Editor's photo gallery
 * (pg_ai_config.php Section 1). Reads/writes `ll_fleet_catalog_photos`
 * (sql/013) plus the actual files under assets/img/fleet/{vessel_id}/.
 *
 * Security pipeline mirrors api/fleet_catalog.php: session auth, POST-only,
 * CSRF token (hash_equals + rotation). Image validation mirrors
 * api/book_editor.php's cover/card upload guard: real MIME via finfo,
 * getimagesize() re-check, GD decode, always re-encoded to WebP on disk —
 * the uploaded bytes are never written to disk verbatim.
 *
 * Actions (POST `action`):
 *   list   — every photo for one vessel (fleet_catalog_id required)
 *   upload — add one photo (fleet_catalog_id + multipart `photo` file)
 *   delete — remove one photo (id required) — also unlinks the file
 */

require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';
require __DIR__ . '/../core/FleetCatalogPhotoRepository.php';

header('Content-Type: application/json; charset=utf-8');

function lly_fcp_json(string $status, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!lly_is_authenticated()) {
    lly_fcp_json('error', ['message' => 'Unauthorized — please log in.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_fcp_json('error', ['message' => 'Method not allowed.'], 405);
}

$submitted = (string) ($_POST['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_fcp_json('error', ['message' => 'Invalid or expired CSRF token.'], 403);
}
// Same rotate-only-on-mutation rule as api/fleet_catalog.php — see that
// file's comment / docs/02_SYSTEM_CODEX_REGISTRY.md for why 'list' never
// rotates (concurrent panel loads sharing one initial token).
$lly_fcp_action_preview = (string) ($_POST['action'] ?? '');
if (in_array($lly_fcp_action_preview, ['upload', 'delete'], true)) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$rotatedCsrf = $_SESSION['csrf_token'];

try {
    $pdo = Conexion::getConnection();
} catch (RuntimeException $e) {
    error_log('[PG-AI · fleet_catalog_photos] DB unavailable: ' . $e->getMessage());
    lly_fcp_json('error', ['message' => 'Database unavailable. Please try again later.'], 503);
}

const MAX_PHOTO_BYTES = 10 * 1024 * 1024; // 10 MB, same ceiling as book_editor.php uploads

$action = (string) ($_POST['action'] ?? '');

try {
    switch ($action) {
        case 'list':
            $vesselId = (int) ($_POST['fleet_catalog_id'] ?? 0);
            if ($vesselId <= 0) {
                lly_fcp_json('error', ['message' => 'Invalid vessel id.', 'csrf_token' => $rotatedCsrf], 400);
            }
            lly_fcp_json('success', [
                'photos'     => FleetCatalogPhotoRepository::listForVessel($pdo, $vesselId),
                'csrf_token' => $rotatedCsrf,
            ]);
            // no break — lly_fcp_json exits

        case 'upload':
            $vesselId = (int) ($_POST['fleet_catalog_id'] ?? 0);
            if ($vesselId <= 0) {
                lly_fcp_json('error', ['message' => 'Invalid vessel id.', 'csrf_token' => $rotatedCsrf], 400);
            }
            if (empty($_FILES['photo']['tmp_name']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                lly_fcp_json('error', ['message' => 'No photo received.', 'csrf_token' => $rotatedCsrf], 400);
            }

            $file    = $_FILES['photo'];
            $tmpPath = $file['tmp_name'];

            if ($file['size'] > MAX_PHOTO_BYTES) {
                lly_fcp_json('error', ['message' => 'Photo exceeds the 10 MB limit.', 'csrf_token' => $rotatedCsrf], 400);
            }

            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $realMime = $finfo->file($tmpPath);

            if (!in_array($realMime, ['image/jpeg', 'image/png'], true)) {
                lly_fcp_json('error', ['message' => 'Only JPEG and PNG images are accepted.', 'csrf_token' => $rotatedCsrf], 400);
            }
            if (getimagesize($tmpPath) === false) {
                lly_fcp_json('error', ['message' => 'Uploaded file is not a valid image.', 'csrf_token' => $rotatedCsrf], 400);
            }
            if (!extension_loaded('gd')) {
                lly_fcp_json('error', ['message' => 'GD extension unavailable — WebP conversion failed.', 'csrf_token' => $rotatedCsrf], 500);
            }

            $src = match ($realMime) {
                'image/jpeg' => imagecreatefromjpeg($tmpPath),
                'image/png'  => imagecreatefrompng($tmpPath),
            };
            if ($src === false) {
                lly_fcp_json('error', ['message' => 'GD could not decode the uploaded image.', 'csrf_token' => $rotatedCsrf], 500);
            }

            $fleetDir = __DIR__ . '/../assets/img/fleet/' . $vesselId;
            if (!is_dir($fleetDir) && !mkdir($fleetDir, 0755, true) && !is_dir($fleetDir)) {
                imagedestroy($src);
                lly_fcp_json('error', ['message' => 'Could not create the vessel photo folder.', 'csrf_token' => $rotatedCsrf], 500);
            }

            $filename = time() . '_' . bin2hex(random_bytes(4)) . '.webp';
            $destPath = $fleetDir . '/' . $filename;

            if (!imagewebp($src, $destPath, 80)) {
                imagedestroy($src);
                lly_fcp_json('error', ['message' => 'WebP conversion failed. Check server permissions.', 'csrf_token' => $rotatedCsrf], 500);
            }
            imagedestroy($src);

            $relativePath = "assets/img/fleet/{$vesselId}/{$filename}";
            $displayOrder = count(FleetCatalogPhotoRepository::listForVessel($pdo, $vesselId));
            $id = FleetCatalogPhotoRepository::create($pdo, $vesselId, $relativePath, $displayOrder);

            lly_fcp_json('success', ['id' => $id, 'photo_path' => $relativePath, 'csrf_token' => $rotatedCsrf]);
            // no break

        case 'delete':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                lly_fcp_json('error', ['message' => 'Invalid photo id.', 'csrf_token' => $rotatedCsrf], 400);
            }
            $path = FleetCatalogPhotoRepository::delete($pdo, $id);
            if ($path !== null) {
                $absolute = __DIR__ . '/../' . $path;
                if (is_file($absolute)) {
                    @unlink($absolute);
                }
            }
            lly_fcp_json('success', ['csrf_token' => $rotatedCsrf]);
            // no break

        default:
            lly_fcp_json('error', ['message' => 'Unknown action.', 'csrf_token' => $rotatedCsrf], 400);
    }
} catch (\Throwable $e) {
    error_log('[PG-AI · fleet_catalog_photos] Unhandled error: ' . $e->getMessage());
    lly_fcp_json('error', ['message' => 'Unexpected server error.', 'csrf_token' => $rotatedCsrf], 500);
}
