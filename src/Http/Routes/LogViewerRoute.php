<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Http\Routes;

use Arcanedev\LogViewer\Http\Controllers\LogViewerController;
use Illuminate\Support\Facades\Route;

/**
 * Class     LogViewerRoute
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogViewerRoute
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Map all routes.
     */
    public function map(): void
    {
        $attributes = (array) config('log-viewer.route.attributes');

        Route::group($attributes, function() {
            Route::name('log-viewer::')->group(function () {
                Route::get('/', [LogViewerController::class, 'index'])
                     ->name('dashboard'); // log-viewer::dashboard

                $this->mapLogsRoutes();
            });
        });
    }

    /**
     * Map the logs routes.
     */
    private function mapLogsRoutes(): void
    {
        Route::prefix('logs')->name('logs.')->group(function() {
            Route::get('/', [LogViewerController::class, 'listLogs'])
                 ->name('list'); // log-viewer::logs.list

            Route::delete('delete', [LogViewerController::class, 'delete'])
                 ->name('delete'); // log-viewer::logs.delete

            Route::prefix('{date}')->group(function() {
                Route::get('/', [LogViewerController::class, 'show'])
                     ->name('show'); // log-viewer::logs.show

                Route::get('download', [LogViewerController::class, 'download'])
                     ->name('download'); // log-viewer::logs.download

                Route::get('{level}', [LogViewerController::class, 'showByLevel'])
                     ->name('filter'); // log-viewer::logs.filter

                Route::get('{level}/search', [LogViewerController::class, 'search'])
                     ->name('search'); // log-viewer::logs.search
            });
        });
    }
}
