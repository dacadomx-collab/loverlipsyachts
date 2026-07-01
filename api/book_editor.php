<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/book_editor.php  v2 (MySQL architecture)
 *
 * Receives authenticated AJAX POST from the Book Editor Studio.
 * Persists all book content fields to lly_book_content via UPSERT.
 * Image uploads are still converted to WebP/80% via GD; the resulting
 * path is stored under meta_key = 'book_cover_path'.
 *
 * Security pipeline:
 *   1. Auth check  (session + remember-me)
 *   2. Method guard (POST only)
 *   3. CSRF token  (hash_equals + immediate rotation)
 *   4. DB UPSERT   (prepared statements, no raw SQL)
 *   5. Image guard (MIME + getimagesize + GD conversion)
 */

require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

/* ── Output helper ───────────────────────────────────────────────── */

function lly_json(string $status, string $msg, int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ── Sanitizers ──────────────────────────────────────────────────── */

function lly_text(string $input): string
{
    return strip_tags(trim($input));
}

function lly_html(string $input): string
{
    return strip_tags(trim($input), ['p', 'em', 'strong', 'br']);
}

/* ── Layer 1: Auth ───────────────────────────────────────────────── */

if (!lly_is_authenticated()) {
    lly_json('error', 'Unauthorized — please log in.', 401);
}

/* ── Layer 2: Method ─────────────────────────────────────────────── */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_json('error', 'Method not allowed.', 405);
}

/* ── Layer 3: CSRF ───────────────────────────────────────────────── */

$submitted = (string) ($_POST['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');

if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_json('error', 'Invalid or expired CSRF token.', 403);
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

/* ── Layer 4: DB connection ──────────────────────────────────────── */

try {
    $pdo = Conexion::getConnection();
} catch (RuntimeException $e) {
    error_log('[LLY book_editor] DB unavailable: ' . $e->getMessage());
    lly_json('error', 'Database unavailable. Please try again later.', 503);
}

/* ── Layer 5: Image upload (optional) ───────────────────────────── */

$coverPath = null;

if (!empty($_FILES['cover_image']['tmp_name']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {

    $file    = $_FILES['cover_image'];
    $tmpPath = $file['tmp_name'];

    if ($file['size'] > 10 * 1024 * 1024) {
        lly_json('error', 'Cover image exceeds the 10 MB limit.', 400);
    }

    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $realMime = $finfo->file($tmpPath);

    if (!in_array($realMime, ['image/jpeg', 'image/png'], true)) {
        lly_json('error', 'Only JPEG and PNG images are accepted.', 400);
    }

    if (getimagesize($tmpPath) === false) {
        lly_json('error', 'Uploaded file is not a valid image.', 400);
    }

    if (!extension_loaded('gd')) {
        lly_json('error', 'GD extension unavailable — WebP conversion failed.', 500);
    }

    $src = match ($realMime) {
        'image/jpeg' => imagecreatefromjpeg($tmpPath),
        'image/png'  => imagecreatefrompng($tmpPath),
    };

    if ($src === false) {
        lly_json('error', 'GD could not decode the uploaded image.', 500);
    }

    $imgDir  = realpath(__DIR__ . '/../assets/img');
    $destPath = $imgDir . '/nine_live.webp';

    if (!imagewebp($src, $destPath, 80)) {
        imagedestroy($src);
        lly_json('error', 'WebP conversion failed. Check server permissions.', 500);
    }
    imagedestroy($src);

    $coverPath = 'assets/img/nine_live.webp';
}

/* ── Layer 6: Build flat field map ──────────────────────────────── */

$fields = [
    'hero_title'      => ['en' => lly_text($_POST['hero_title_en']   ?? ''), 'es' => lly_text($_POST['hero_title_es']   ?? '')],
    'hero_subtitle'   => ['en' => lly_text($_POST['hero_sub_en']     ?? ''), 'es' => lly_text($_POST['hero_sub_es']     ?? '')],
    'synopsis'        => ['en' => lly_html($_POST['synopsis_en']     ?? ''), 'es' => lly_html($_POST['synopsis_es']     ?? '')],
    'testimonial'     => ['en' => lly_html($_POST['testimonial_en']  ?? ''), 'es' => lly_html($_POST['testimonial_es']  ?? '')],
    'sample_chapter'  => ['en' => lly_html($_POST['sample_chapter_en'] ?? ''), 'es' => lly_html($_POST['sample_chapter_es'] ?? '')],
];

if ($coverPath !== null) {
    $fields['book_cover_path'] = ['en' => $coverPath, 'es' => $coverPath];
}

// Amazon conversion link (URL — language-agnostic, stored in both columns)
$amazonUrl = lly_text($_POST['amazon_link_url'] ?? '');
if ($amazonUrl !== '') {
    $fields['amazon_link_url'] = ['en' => $amazonUrl, 'es' => $amazonUrl];
}

// Blog synergy cluster: 3 articles × (tag, title, link)
for ($a = 1; $a <= 3; $a++) {
    $tagEn   = lly_text($_POST["article_{$a}_tag_en"]   ?? '');
    $tagEs   = lly_text($_POST["article_{$a}_tag_es"]   ?? '');
    $titleEn = lly_text($_POST["article_{$a}_title_en"] ?? '');
    $titleEs = lly_text($_POST["article_{$a}_title_es"] ?? '');
    $link    = lly_text($_POST["article_{$a}_link"]     ?? '');

    if ($tagEn !== '' || $titleEn !== '') {
        $fields["article_{$a}_tag"]   = ['en' => $tagEn,   'es' => $tagEs];
        $fields["article_{$a}_title"] = ['en' => $titleEn, 'es' => $titleEs];
    }
    if ($link !== '') {
        $fields["article_{$a}_link"] = ['en' => $link, 'es' => $link];
    }
}

// Curiosity cards: text (1-indexed) + icons + optional image uploads
$rawIcons    = $_POST['card_icon'] ?? [];
$rawEn       = $_POST['card_en']   ?? [];
$rawEs       = $_POST['card_es']   ?? [];
$rawCardImgs = $_FILES['card_img']['tmp_name']  ?? [];
$rawCardErrs = $_FILES['card_img']['error']     ?? [];
$rawCardSizes= $_FILES['card_img']['size']      ?? [];

$cardImgDir = realpath(__DIR__ . '/../assets/img/cards');

for ($i = 1; $i <= 7; $i++) {
    $icon = lly_text((string) ($rawIcons[$i] ?? ''));
    $en   = lly_text((string) ($rawEn[$i]   ?? ''));
    $es   = lly_text((string) ($rawEs[$i]   ?? ''));

    if ($en !== '' || $icon !== '') {
        $fields["card_{$i}"]      = ['en' => $en,   'es' => $es];
        $fields["card_{$i}_icon"] = ['en' => $icon, 'es' => $icon];
    }

    // Card image upload
    $imgTmp  = (string) ($rawCardImgs[$i] ?? '');
    $imgErr  = (int)    ($rawCardErrs[$i] ?? UPLOAD_ERR_NO_FILE);
    $imgSize = (int)    ($rawCardSizes[$i] ?? 0);

    if ($imgErr === UPLOAD_ERR_OK && $imgTmp !== '' && $cardImgDir !== false) {
        if ($imgSize > 10 * 1024 * 1024) {
            error_log("[LLY book_editor] card_{$i} image exceeds 10 MB, skipped.");
            continue;
        }
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($imgTmp);
        if (!in_array($realMime, ['image/jpeg', 'image/png'], true)) {
            error_log("[LLY book_editor] card_{$i} image MIME not accepted, skipped.");
            continue;
        }
        if (getimagesize($imgTmp) === false) { continue; }

        if (extension_loaded('gd')) {
            $srcImg = match ($realMime) {
                'image/jpeg' => imagecreatefromjpeg($imgTmp),
                'image/png'  => imagecreatefrompng($imgTmp),
            };
            if ($srcImg !== false) {
                $cardWebp = $cardImgDir . "/card_{$i}.webp";
                if (imagewebp($srcImg, $cardWebp, 80)) {
                    imagedestroy($srcImg);
                    $fields["card_{$i}_img"] = ['en' => "assets/img/cards/card_{$i}.webp", 'es' => "assets/img/cards/card_{$i}.webp"];
                } else {
                    imagedestroy($srcImg);
                    error_log("[LLY book_editor] card_{$i} WebP write failed.");
                }
            }
        }
    }
}

/* ── Layer 7: UPSERT loop ────────────────────────────────────────── */

$sql = 'INSERT INTO lly_book_content (meta_key, content_en, content_es)
        VALUES (:key, :en, :es)
        ON DUPLICATE KEY UPDATE
            content_en = VALUES(content_en),
            content_es = VALUES(content_es),
            updated_at = CURRENT_TIMESTAMP';

$stmt = $pdo->prepare($sql);

try {
    $pdo->beginTransaction();
    foreach ($fields as $metaKey => $values) {
        $stmt->execute([
            ':key' => $metaKey,
            ':en'  => $values['en'],
            ':es'  => $values['es'],
        ]);
    }
    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('[LLY book_editor] UPSERT failed: ' . $e->getMessage());
    lly_json('error', 'Database write failed. Please try again.', 500);
}

/* ── Layer 8: Success ────────────────────────────────────────────── */

echo json_encode(['status' => 'success', 'message' => 'Changes successfully saved to live database!']);
