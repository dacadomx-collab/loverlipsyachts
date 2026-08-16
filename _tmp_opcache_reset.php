<?php
declare(strict_types=1);

/**
 * One-off diagnostic — forces a PHP OPcache reset if OPcache is active
 * and is what's serving stale compiled bytecode despite an on-disk file
 * update (confirmed via a cache-busted URL still returning old output).
 * Disposable — deleted immediately after confirming the fix.
 */

header('Content-Type: text/plain; charset=utf-8');

if (!function_exists('opcache_get_status')) {
    echo "OPcache extension not available.\n";
    exit;
}

$status = opcache_get_status(false);
if ($status === false) {
    echo "OPcache is installed but disabled (opcache.enable=0) — not the cause of the stale output.\n";
    exit;
}

echo "OPcache was active. enabled=" . var_export($status['opcache_enabled'] ?? null, true) . "\n";
echo "cache_full=" . var_export($status['cache_full'] ?? null, true) . "\n";

$reset = opcache_reset();
echo "opcache_reset() returned: " . var_export($reset, true) . "\n";
