<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use starfederation\datastar\Consts;

class RegisterScript
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!$response->isSuccessful()) {
            return $response;
        }

        if (!str_contains($response->headers->get('content-type'), 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        $path = asset('vendor/datastar/' . Consts::VERSION . '/datastar.js');
        $asset = '<script type="module" src="' . $path . '"></script>';
        $content = str_replace('</head>', $asset . '</head>', $content);
        $response->setContent($content);

        return $response;
    }
}
