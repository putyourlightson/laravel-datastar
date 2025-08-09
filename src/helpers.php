<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

use Putyourlightson\Datastar\Helpers\Datastar;
use Putyourlightson\Datastar\Services\Sse;

if (!function_exists('datastar')) {
    function datastar(): Datastar
    {
        return new Datastar();
    }
}

if (!function_exists('sse')) {
    function sse(): Sse
    {
        return app(Sse::class);
    }
}
