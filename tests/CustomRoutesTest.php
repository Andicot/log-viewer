<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Tests;

use Closure;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;

class CustomRoutesTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('log-viewer.route.attributes', [
            'prefix' => 'admin/registro',
            'middleware' => [DenyLogViewerAccess::class],
        ]);
    }

    #[Test]
    public function custom_prefix_and_authorization_cover_every_endpoint(): void
    {
        $endpoints = [
            ['GET', 'dashboard', []],
            ['GET', 'logs.list', []],
            ['GET', 'logs.show', ['2015-01-01']],
            ['GET', 'logs.filter', ['2015-01-01', 'error']],
            ['GET', 'logs.search', ['2015-01-01', 'error']],
            ['GET', 'logs.download', ['2015-01-01']],
            ['DELETE', 'logs.delete', []],
        ];
        foreach ($endpoints as [$method, $route, $parameters]) {
            $url = route('log-viewer::'.$route, $parameters);
            self::assertStringContainsString('/admin/registro', $url);
            $this->call($method, $url)->assertForbidden();
        }
        $this->get('/log-viewer')->assertNotFound();
    }
}

class DenyLogViewerAccess
{
    public function handle(Request $request, Closure $next)
    {
        abort(403);
    }
}
