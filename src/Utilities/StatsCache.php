<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Utilities;

use Closure;
use Illuminate\Contracts\Cache\Factory;

/** Cache counters only; hash the file so same-size rewrites also invalidate it. */
class StatsCache
{
    public function __construct(private Factory $cache) {}

    public function conteggi(string $path, Closure $calculate): array
    {
        if (! config('log-viewer.stats-cache.enabled', true)) {
            return $calculate();
        }

        $store = $this->cache->store(config('log-viewer.stats-cache.store', 'file'));
        $key = 'log-viewer:stats:v1:'.hash('sha256', $path);
        // Rotation may remove the file between discovery and hashing; a failed hash is never cached.
        $fingerprint = @hash_file('sha256', $path);
        $cached = $store->get($key);

        if ($fingerprint !== false && is_array($cached) && $cached['fingerprint'] === $fingerprint) {
            return $cached['counts'];
        }

        $counts = $calculate();
        // Do not cache a snapshot while another process is appending or rotating.
        if ($fingerprint !== false && $fingerprint === @hash_file('sha256', $path)) {
            $store->put($key, ['fingerprint' => $fingerprint, 'counts' => $counts],
                (int) config('log-viewer.stats-cache.ttl', 86400));
        }

        return $counts;
    }
}
