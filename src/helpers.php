<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

use Putyourlightson\Datastar\Helpers\Datastar;
use Putyourlightson\Datastar\Services\SseService;

if (!function_exists('datastar')) {
    function datastar(): Datastar
    {
        return new Datastar();
    }
}

if (!function_exists('sse')) {
    function sse(): SseService
    {
        return app(SseService::class);
    }
}
