<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RegisterScript
{
    public const VERSION = '1.0.1';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$response->isSuccessful()
            || !str_contains($response->headers->get('content-type'), 'text/html')
        ) {
            return $response;
        }

        $content = $response->getContent();
        $path = asset('vendor/datastar/' . static::VERSION . '/datastar.js');
        $asset = '<script type="module" src="' . $path . '"></script>';
        $content = str_replace('</head>', $asset . '</head>', $content);
        $response->setContent($content);

        return $response;
    }
}
