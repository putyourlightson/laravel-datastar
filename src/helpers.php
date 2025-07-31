<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

use Putyourlightson\Datastar\Helpers\Datastar;
use Putyourlightson\Datastar\Helpers\Sse;

if (!function_exists('datastar')) {
    function datastar(): Datastar
    {
        return new Datastar();
    }
}

if (!function_exists('sse')) {
    function sse(): Sse
    {
        return new Sse();
    }
}
