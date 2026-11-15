<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Tests;

use PHPUnit\Framework\Attributes\Test;

class DisabledRoutesTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('log-viewer.route.enabled', false);
    }

    #[Test]
    public function disabling_routes_keeps_the_api_available(): void
    {
        self::assertFalse($this->app['router']->has('log-viewer::dashboard'));
        $this->get('/log-viewer')->assertNotFound();
        self::assertGreaterThan(0, log_viewer()->count());
    }
}
