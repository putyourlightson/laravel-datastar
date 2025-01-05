<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

use Putyourlightson\Datastar\Helpers\Datastar;

if (!function_exists('datastar')) {
    function datastar(): Datastar
    {
        return new Datastar();
    }
}
