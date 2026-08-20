<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — my-book/invitation.php
 * Public entry point for the "Nine Lives. One True Love" launch VIP invitation.
 * Accessible at:  https://loverlipsyachts.com/my-book/invitation.php
 *
 * Same delegation pattern as my-book/index.php: renders invitation.php from
 * the cockpit/ management portal, resolving the path dynamically so the
 * same codebase works on both local XAMPP and Hostinger production.
 *
 * Path resolution:
 *   Production  → public_html/cockpit/invitation.php  (sibling of my-book/)
 *   Local XAMPP → loverlipsyachts/invitation.php       (parent directory)
 */

// Production: my-book/ and cockpit/ are siblings inside public_html/
$productionPath = __DIR__ . '/../cockpit/invitation.php';

// Local XAMPP: my-book/ lives inside loverlipsyachts/ alongside invitation.php
$localPath      = __DIR__ . '/../invitation.php';

if (is_file($productionPath)) {
    require $productionPath;       // Hostinger: /public_html/cockpit/invitation.php
} elseif (is_file($localPath)) {
    require $localPath;            // XAMPP: /loverlipsyachts/invitation.php
} else {
    http_response_code(503);
    echo 'Invitation page temporarily unavailable. Please try again shortly.';
}
