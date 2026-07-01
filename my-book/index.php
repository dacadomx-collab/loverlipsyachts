<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — my-book/index.php
 * Public entry point for the "Nine Lives. One True Love" book spotlight.
 * Accessible at:  https://loverlipsyachts.com/my-book/
 *
 * This wrapper delegates all rendering to book.php located inside the
 * cockpit/ management portal, resolving the path dynamically so the
 * same codebase works on both local XAMPP and Hostinger production.
 *
 * Path resolution:
 *   Production  → public_html/cockpit/book.php   (sibling of my-book/)
 *   Local XAMPP → loverlipsyachts/book.php        (parent directory)
 *
 * When PHP requires book.php, __DIR__ inside that file resolves to its
 * real location (cockpit/ or project root), so all internal require
 * paths (api/conexion.php, assets/) remain correct in both environments.
 */

// Production: my-book/ and cockpit/ are siblings inside public_html/
$productionPath = __DIR__ . '/../cockpit/book.php';

// Local XAMPP: my-book/ lives inside loverlipsyachts/ alongside book.php
$localPath      = __DIR__ . '/../book.php';

if (is_file($productionPath)) {
    require $productionPath;       // Hostinger: /public_html/cockpit/book.php
} elseif (is_file($localPath)) {
    require $localPath;            // XAMPP: /loverlipsyachts/book.php
} else {
    http_response_code(503);
    echo 'Book page temporarily unavailable. Please try again shortly.';
}
