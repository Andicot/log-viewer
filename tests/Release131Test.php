<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Tests;

use Arcanedev\LogViewer\Entities\LogEntry;
use Arcanedev\LogViewer\Exceptions\FilesystemException;
use Arcanedev\LogViewer\Utilities\StatsCache;
use PHPUnit\Framework\Attributes\Test;

class Release131Test extends TestCase
{
    #[Test]
    public function counters_are_cached_but_same_size_rewrites_and_rotation_invalidate_them(): void
    {
        config(['log-viewer.stats-cache.store' => 'array']);
        $path = tempnam(sys_get_temp_dir(), 'log-viewer-stats-');
        $cache = app(StatsCache::class);
        $calls = 0;
        $calculate = function () use (&$calls) { return ['all' => ++$calls]; };
        try {
            file_put_contents($path, 'first');
            self::assertSame(['all' => 1], $cache->conteggi($path, $calculate));
            self::assertSame(['all' => 1], $cache->conteggi($path, $calculate));
            file_put_contents($path, 'other'); // Same size, same timestamp granularity.
            self::assertSame(['all' => 2], $cache->conteggi($path, $calculate));
            file_put_contents($path, ' append', FILE_APPEND);
            self::assertSame(['all' => 3], $cache->conteggi($path, $calculate));
            unlink($path);
            file_put_contents($path, '');
            self::assertSame(['all' => 4], $cache->conteggi($path, $calculate));
            config(['log-viewer.stats-cache.enabled' => false]);
            self::assertSame(['all' => 5], $cache->conteggi($path, $calculate));
            self::assertSame(['all' => 6], $cache->conteggi($path, $calculate));
        } finally { unlink($path); }
    }

    #[Test]
    public function changing_files_are_not_cached_and_expired_counts_are_recomputed(): void
    {
        config(['log-viewer.stats-cache.store' => 'array', 'log-viewer.stats-cache.ttl' => 1]);
        $path = tempnam(sys_get_temp_dir(), 'log-viewer-stats-');
        try {
            $cache = app(StatsCache::class);
            $cache->conteggi($path, function () use ($path) {
                file_put_contents($path, 'changed during calculation');
                return ['all' => 1];
            });
            self::assertSame(['all' => 2], $cache->conteggi($path, fn () => ['all' => 2]));
            $this->travel(2)->seconds();
            self::assertSame(['all' => 3], $cache->conteggi($path, fn () => ['all' => 3]));
            $this->travel(2)->seconds();
            self::assertSame(['all' => 0], $cache->conteggi($path, function () use ($path) {
                unlink($path);
                return ['all' => 0];
            }));
            file_put_contents($path, 'new file');
            self::assertSame(['all' => 4], $cache->conteggi($path, fn () => ['all' => 4]));
        } finally { $this->travelBack(); unlink($path); }
    }

    #[Test]
    public function read_only_hides_delete_but_preserves_filters_downloads_and_copy_in_every_theme(): void
    {
        config(['log-viewer.read-only' => true]);
        foreach (['bootstrap-3', 'bootstrap-4', 'bootstrap-5'] as $theme) {
            config(['log-viewer.theme' => $theme]);
            $this->get(route('log-viewer::logs.list'))->assertOk()->assertDontSee('delete-log-modal');
            $this->get(route('log-viewer::logs.filter', ['2015-01-01', 'error']))
                ->assertOk()->assertDontSee('delete-log-modal')->assertSee('Copy details')->assertSee('Download');
        }
        $this->get(route('log-viewer::logs.download', ['2015-01-01']))->assertOk();
        $this->deleteJson(route('log-viewer::logs.delete'), ['date' => '2015-01-01'], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertForbidden();
        $this->artisan('log-viewer:clear')->expectsOutput('Log viewer is read-only.')->assertExitCode(1);
        self::assertFileExists($this->getLogPath('2015-01-01'));
    }

    #[Test]
    public function read_only_also_blocks_php_deletion_and_clearing(): void
    {
        config(['log-viewer.read-only' => true]);
        foreach (['delete', 'clear'] as $method) {
            try {
                log_viewer()->$method('2015-01-01');
                self::fail('Deletion must be rejected');
            } catch (FilesystemException $e) {
                self::assertSame('Log viewer is read-only.', $e->getMessage());
            }
        }
    }

    #[Test]
    public function bundled_assets_have_valid_hashes_mime_types_and_conditional_responses(): void
    {
        $base = dirname(__DIR__).'/assets/';
        $manifest = json_decode(file_get_contents($base.'manifest.json'), true);
        foreach ($manifest as $path => $hash) {
            self::assertSame($hash, hash_file('sha256', $base.$path));
            $url = route('log-viewer::assets', ['path' => $path]);
            $response = $this->get($url)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
            $this->get($url, ['If-None-Match' => $response->headers->get('ETag')])->assertStatus(304);
        }
        $this->get(route('log-viewer::assets', ['path' => 'copy.js']))->assertHeader('Content-Type', 'application/javascript');
        foreach (['../composer.json', 'manifest.json', 'licenses/bootstrap-5.txt', 'missing.js'] as $path) {
            $this->get(route('log-viewer::assets', ['path' => $path]))->assertNotFound();
        }
    }

    #[Test]
    public function copy_payload_keeps_plain_text_and_is_html_escaped(): void
    {
        $entry = new LogEntry('error', '[2026-11-15 12:00:00] testing.ERROR: <script>"test"</script>', "#0 stack\n#1 end");
        self::assertStringContainsString('<script>"test"</script>', $entry->dettagliDaCopiare());
        self::assertStringContainsString("#0 stack\n#1 end", $entry->dettagliDaCopiare());
        $html = view('log-viewer::partials.copy', ['entry' => $entry, 'key' => 0, 'buttonClass' => 'btn'])->render();
        self::assertStringNotContainsString('<script>', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
    }

    #[Test]
    public function all_theme_resource_urls_are_local(): void
    {
        foreach (['bootstrap-3', 'bootstrap-4', 'bootstrap-5'] as $theme) {
            config(['log-viewer.theme' => $theme]);
            $html = $this->get(route('log-viewer::dashboard'))->assertOk()->getContent();
            preg_match_all('/(?:src|href)=["\'](https?:\/\/[^"\']+)/', $html, $matches);
            foreach ($matches[1] as $url) {
                // External attribution links are allowed; stylesheets/scripts are local.
                if (str_contains($url, '.css') || str_contains($url, '.js')) {
                    self::assertStringContainsString('/log-viewer/assets/', $url);
                }
            }
        }
    }
}
