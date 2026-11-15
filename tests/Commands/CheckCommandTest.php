<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Tests\Commands;

use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     CheckCommandTest
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class CheckCommandTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_check(): void
    {
        $this->artisan('log-viewer:check')
             ->assertExitCode(0);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_reports_success_with_stack_daily(): void
    {
        config(['logging.default' => 'stack', 'logging.channels.stack' => ['driver' => 'stack', 'channels' => ['daily']]]);
        $this->artisan('log-viewer:check')
            ->expectsTable(['Status', 'Message'], [['success', 'Are you ready to rock ?']])
            ->assertSuccessful();
    }

}
