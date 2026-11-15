<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Tests;

use Arcanedev\LogViewer\LogViewerServiceProvider;
use Illuminate\Support\ServiceProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class Laravel13CompatibilityTest extends TestCase
{
    public static function themesAndLevels(): iterable
    {
        foreach (['bootstrap-3', 'bootstrap-4', 'bootstrap-5'] as $theme) {
            foreach (['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'] as $level) {
                yield "$theme/$level" => [$theme, $level];
            }
        }
    }

    #[Test]
    #[DataProvider('themesAndLevels')]
    public function list_links_open_only_the_selected_level(string $theme, string $level): void
    {
        config(['log-viewer.theme' => $theme]);
        $url = route('log-viewer::logs.filter', ['2015-01-01', $level]);
        $this->get(route('log-viewer::logs.list'))->assertOk()->assertSee($url, false);
        $response = $this->get($url)->assertOk();
        $entries = $response->viewData('entries');
        self::assertGreaterThan(0, $entries->total());
        foreach ($entries as $entry) {
            self::assertSame($level, $entry->level);
        }
        $this->get(route('log-viewer::dashboard'))->assertOk();
    }

    #[Test]
    public function pagination_preserves_the_selected_level_and_search(): void
    {
        config(['log-viewer.per-page' => 1]);
        $response = $this->get(route('log-viewer::logs.search', [
            'date' => '2015-01-01', 'level' => 'all', 'query' => 'log', 'page' => 2,
        ]))->assertOk();
        $entries = $response->viewData('entries');
        self::assertSame(2, $entries->currentPage());
        self::assertCount(1, $entries);
        self::assertGreaterThan(1, $entries->total());
        self::assertStringContainsString('/all/search?', $entries->nextPageUrl());
        self::assertStringContainsString('query=log', $entries->nextPageUrl());
    }

    #[Test]
    public function empty_search_results_and_missing_files_are_preserved(): void
    {
        $response = $this->get(route('log-viewer::logs.search', [
            'date' => '2015-01-01', 'level' => 'error', 'query' => 'no-such-entry-compatibility',
        ]))->assertOk()->assertSee('The list of logs is empty!');
        self::assertSame(0, $response->viewData('entries')->total());
        $this->get(route('log-viewer::logs.show', ['2099-01-01']))->assertNotFound();
    }

    #[Test]
    public function bootstrap_five_mobile_menu_uses_native_collapse_attributes(): void
    {
        config(['log-viewer.theme' => 'bootstrap-5']);
        $this->get(route('log-viewer::dashboard'))
            ->assertOk()
            ->assertSee('data-bs-toggle="collapse" data-bs-target="#navbarNav"', false)
            ->assertDontSee('data-toggle="collapse" data-target="#navbarNav"', false);
    }

    #[Test]
    public function publishing_keeps_all_legacy_tags_and_paths(): void
    {
        $provider = LogViewerServiceProvider::class;
        foreach (['config', 'translations', 'views'] as $tag) {
            $paths = ServiceProvider::pathsToPublish($provider, $tag);
            self::assertCount(1, $paths);
            self::assertSame($paths, ServiceProvider::pathsToPublish($provider, 'log-viewer-'.$tag));
        }
        self::assertCount(3, ServiceProvider::pathsToPublish($provider, 'arcanedev'));
        self::assertCount(3, ServiceProvider::pathsToPublish($provider, 'log-viewer'));
        self::assertContains(config_path('log-viewer.php'), ServiceProvider::pathsToPublish($provider, 'config'));
        self::assertContains(lang_path('vendor/log-viewer'), ServiceProvider::pathsToPublish($provider, 'translations'));
        self::assertContains(config('view.paths')[0].'/vendor/log-viewer', ServiceProvider::pathsToPublish($provider, 'views'));
    }
}
