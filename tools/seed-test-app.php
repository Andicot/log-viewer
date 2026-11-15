<?php
$root = dirname(__DIR__).'/build/laravel13';
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (! $app->environment('local') || realpath(storage_path('logs')) !== realpath($root.'/storage/logs')) {
    throw new RuntimeException('Run only in the disposable build/laravel13 app.');
}
foreach (['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'] as $level) {
    Illuminate\Support\Facades\Log::log($level, 'Verifica livello '.$level, ['scenario' => 'fork-laravel13', 'livello' => $level]);
}
for ($i = 1; $i <= 65; $i++) {
    Illuminate\Support\Facades\Log::error('Errore sintetico '.$i, ['ordine' => $i]);
}
Illuminate\Support\Facades\Log::error('Eccezione sintetica', ['exception' => new RuntimeException('Solo dati di verifica')]);
file_put_contents(storage_path('logs/laravel-2026-11-14.log'), "[2026-11-14 10:00:00] local.WARNING: Log eliminabile di verifica\n");
file_put_contents(storage_path('logs/laravel-2026-11-13.log'), "[2026-11-13 10:00:00] local.INFO: Secondo log eliminabile\n");
echo json_encode(['framework' => app()->version(), 'cache' => config('cache.default'), 'session' => config('session.driver'), 'stack' => config('logging.channels.stack.channels'), 'max_files' => config('logging.channels.daily.max_files')]).PHP_EOL;
