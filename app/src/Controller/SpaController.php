<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SpaController
{
    #[Route('/{reactRouting}', name: 'spa', requirements: ['reactRouting' => '^(?!api|_profiler|_wdt|build|sw\.js).*'], defaults: ['reactRouting' => null], priority: -1)]
    public function index(): Response
    {
        $manifestPath = __DIR__ . '/../../public/build/manifest.json';
        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        $cssFile = $manifest['build/app.css'] ?? '/build/app.css';
        $runtimeJs = $manifest['build/runtime.js'] ?? '/build/runtime.js';
        $appJs = $manifest['build/app.js'] ?? '/build/app.js';
        
        // Find the vendor chunk (451.*.js or similar)
        $vendorJs = '';
        foreach ($manifest as $key => $value) {
            if (preg_match('/build\/\d+\.\w+\.js/', $key)) {
                $vendorJs = $value;
                break;
            }
        }
        
        $vendorScript = $vendorJs ? "<script src=\"{$vendorJs}\"></script>" : '';
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TV Watchlist</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>⚫️</text></svg>">
    
    <link rel="preconnect" href="https://artworks.thetvdb.com" crossorigin>
    <link rel="dns-prefetch" href="https://artworks.thetvdb.com">
    
    <link rel="stylesheet" href="{$cssFile}">
</head>
<body>
    <div id="root"></div>
    
    <script src="{$runtimeJs}"></script>
    {$vendorScript}
    <script src="{$appJs}"></script>
    
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }
    </script>
</body>
</html>
HTML;
        
        return new Response($html);
    }
}
