<?php

declare(strict_types=1);

$root = dirname(__DIR__).'/build/laravel13';
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function verify(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
    echo "PASS: $message\n";
}

verify(str_starts_with(app()->version(), '13.'), 'Laravel 13');
verify(! class_exists('Arcanedev\\Support\\Providers\\ServiceProvider'), 'No arcanedev/support');
verify(config('cache.default') === 'file' && config('session.driver') === 'file', 'File cache and sessions');
verify(in_array('daily', config('logging.channels.stack.channels'), true), 'Daily channel in stack');
verify((int) config('logging.channels.daily.max_files') === 14, '14 daily files retained');
$viewer = log_viewer();
$date = $viewer->dates()[0];
foreach ((new ReflectionClass(Psr\Log\LogLevel::class))->getConstants() as $level) {
    $entries = $viewer->entries($date, $level);
    verify($entries->count() > 0, 'Log level '.$level);
    foreach ($entries as $entry) {
        if ($entry->level !== $level) {
            throw new RuntimeException('Unexpected level in '.$level);
        }
    }
}
foreach (['dashboard', 'logs.list', 'logs.show', 'logs.filter', 'logs.search', 'logs.download', 'logs.delete'] as $route) {
    verify(app('router')->has('log-viewer::'.$route), 'Route '.$route);
}
$raw = '';
for ($i = 0; $i < 10000; $i++) {
    $raw .= "[2026-11-15 12:00:00] local.ERROR: Synthetic performance entry $i\n";
}
$start = hrtime(true);
$entries = Arcanedev\LogViewer\Entities\LogEntryCollection::load($raw);
verify($entries->count() === 10000, 'Parse 10000 synthetic entries');
printf("Parser: %.3f s, peak process memory %.1f MiB\n", (hrtime(true)-$start)/1e9, memory_get_peak_usage(true)/1048576);
