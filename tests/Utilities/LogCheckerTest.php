<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Tests\Utilities;

use Arcanedev\LogViewer\Tests\TestCase;
use Arcanedev\LogViewer\Utilities\LogChecker;

/**
 * Class     LogCheckerTest
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogCheckerTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Arcanedev\LogViewer\Utilities\LogChecker */
    private $checker;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->checker = $this->app->make(\Arcanedev\LogViewer\Contracts\Utilities\LogChecker::class);
    }

    protected function tearDown(): void
    {
        unset($this->checker);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_be_instantiated(): void
    {
        static::assertInstanceOf(LogChecker::class, $this->checker);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_must_fails(): void
    {
        static::assertFalse($this->checker->passes());
        static::assertTrue($this->checker->fails());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_messages(): void
    {
        $messages = $this->checker->messages();

        static::assertArrayHasKey('handler', $messages);
        static::assertArrayHasKey('files', $messages);
        static::assertEmpty($messages['handler']);
        static::assertCount(3, $messages['files']);
        static::assertArrayHasKey('laravel.log', $messages['files']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_requirements(): void
    {
        $requirements = $this->checker->requirements();

        static::assertArrayHasKey('status', $requirements);
        static::assertEquals($requirements['status'], 'success');
        static::assertArrayHasKey('header', $requirements);
        static::assertEquals($requirements['header'], 'Application requirements fulfilled.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_must_fail_the_requirements_on_handler(): void
    {
        config()->set('logging.default', 'stack');

        $requirements = $this->checker->requirements();

        static::assertArrayHasKey('status', $requirements);
        static::assertEquals($requirements['status'], 'failed');
        static::assertArrayHasKey('header', $requirements);
        static::assertEquals($requirements['header'], 'Application requirements failed.');
    }
    public static function loggingConfigurations(): iterable
    {
        $daily = ['driver' => 'daily'];
        $single = ['driver' => 'single'];
        yield 'daily directly' => ['daily', ['daily' => $daily], true];
        yield 'stack daily' => ['stack', ['stack' => ['driver' => 'stack', 'channels' => ['daily']], 'daily' => $daily], true];
        yield 'stack stderr daily' => ['stack', ['stack' => ['driver' => 'stack', 'channels' => ['stderr', 'daily']], 'stderr' => ['driver' => 'monolog'], 'daily' => $daily], true];
        yield 'named daily driver' => ['DailyApp', ['DailyApp' => $daily], true];
        yield 'misleading channel name' => ['daily', ['daily' => $single], false];
        yield 'stack single' => ['stack', ['stack' => ['driver' => 'stack', 'channels' => ['single']], 'single' => $single], false];
        yield 'nested stack' => ['stack', ['stack' => ['driver' => 'stack', 'channels' => ['nested']], 'nested' => ['driver' => 'stack', 'channels' => ['daily']], 'daily' => $daily], true];
        yield 'comma separated channels' => ['stack', ['stack' => ['driver' => 'stack', 'channels' => 'single,daily'], 'single' => $single, 'daily' => $daily], true];
        yield 'empty stack' => ['stack', ['stack' => ['driver' => 'stack', 'channels' => []]], false];
        yield 'missing channel' => ['missing', [], false];
        yield 'missing child' => ['stack', ['stack' => ['driver' => 'stack', 'channels' => ['missing']]], false];
        yield 'circular stacks' => ['stack', ['stack' => ['driver' => 'stack', 'channels' => ['nested']], 'nested' => ['driver' => 'stack', 'channels' => ['stack']]], false];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('loggingConfigurations')]
    public function it_checks_the_configured_drivers(string $default, array $channels, bool $valid): void
    {
        config(['logging.default' => $default, 'logging.channels' => $channels]);

        self::assertSame($valid ? 'success' : 'failed', $this->checker->requirements()['status']);
        self::assertSame($valid, $this->checker->messages()['handler'] === '');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_refreshes_channel_configuration_without_hiding_file_errors(): void
    {
        config(['logging.default' => 'stack', 'logging.channels.stack' => ['driver' => 'stack', 'channels' => ['single']]]);
        self::assertSame('failed', $this->checker->requirements()['status']);
        config(['logging.channels.stack.channels' => ['daily']]);
        self::assertSame('success', $this->checker->requirements()['status']);
        self::assertEmpty($this->checker->messages()['handler']);
        self::assertArrayHasKey('laravel.log', $this->checker->messages()['files']);
    }

}
