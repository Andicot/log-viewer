<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AssetController extends Controller
{
    public function mostra(Request $request, string $path): BinaryFileResponse
    {
        $base = dirname(__DIR__, 3).'/assets/';
        $manifest = json_decode(file_get_contents($base.'manifest.json'), true, 512, JSON_THROW_ON_ERROR);
        abort_unless(isset($manifest[$path]), 404);
        $type = match (pathinfo($path, PATHINFO_EXTENSION)) {
            'css' => 'text/css', 'js' => 'application/javascript',
            'woff' => 'font/woff', 'woff2' => 'font/woff2', 'ttf' => 'font/ttf',
            'svg' => 'image/svg+xml', 'eot' => 'application/vnd.ms-fontobject',
            default => 'application/octet-stream',
        };
        $response = response()->file($base.$path, ['Content-Type' => $type, 'X-Content-Type-Options' => 'nosniff']);
        $response->setPrivate()->setMaxAge(86400)->setEtag($manifest[$path]);
        $response->isNotModified($request);

        return $response;
    }
}
