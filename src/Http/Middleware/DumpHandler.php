<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\HtmlDumper;
use Illuminate\Http\Request;
use starfederation\datastar\enums\ElementPatchMode;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\VarDumper;

class DumpHandler
{
    /**
     * Handles calls to `dump()` and `dd()`.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (empty($request->headers->get('Datastar-Request'))) {
            return $next($request);
        }

        VarDumper::setHandler(function($var) {
            $cloner = new VarCloner();
            $dumper = new HtmlDumper(base_path(), config('view.compiled'));

            $stream = fopen('php://memory', 'r+');
            $dumper->setOutput($stream);
            $data = $cloner->cloneVar($var);
            $dumper->dumpWithSource($data);

            rewind($stream);
            $output = stream_get_contents($stream);
            fclose($stream);

            if ($output !== false) {
                sse()->dump($output);
            }
        });

        return $next($request);
    }
}
