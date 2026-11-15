<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer;

use Illuminate\Support\ServiceProvider;

/**
 * Register the original LogViewer resources using Laravel's native APIs.
 *
 * @author ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogViewerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->getBasePath().'/config/log-viewer.php', 'log-viewer');

        $this->booted(function () {
            $this->app->register(Providers\RouteServiceProvider::class);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\PublishCommand::class,
                Commands\StatsCommand::class,
                Commands\CheckCommand::class,
                Commands\ClearCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        $base = $this->getBasePath();
        $translations = $this->app->langPath('vendor/log-viewer');

        $this->loadViewsFrom($base.'/views', 'log-viewer');
        $this->loadTranslationsFrom($base.'/translations', 'log-viewer');
        $this->loadJsonTranslationsFrom(file_exists($translations) ? $translations : $base.'/translations');

        if ($this->app->runningInConsole()) {
            $this->publishResources();
        }
    }

    public function getBasePath(): string
    {
        return dirname(__DIR__);
    }

    protected function publishResources(): void
    {
        $base = $this->getBasePath();
        $resources = [
            'config' => [$base.'/config/log-viewer.php' => config_path('log-viewer.php')],
            'translations' => [$base.'/translations' => $this->app->langPath('vendor/log-viewer')],
            'views' => [$base.'/views' => $this->app['config']['view.paths'][0].'/vendor/log-viewer'],
        ];

        foreach ($resources as $tag => $paths) {
            $this->publishes($paths, ['arcanedev', 'log-viewer', $tag, 'log-viewer-'.$tag]);
        }
    }
}
