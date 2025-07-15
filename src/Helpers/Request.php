<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Helpers;

use starfederation\datastar\ServerSentEventGenerator;

class Request
{
    /**
     * Reads and returns the signals passed into the request.
     */
    public static function readSignals(): array
    {
        return ServerSentEventGenerator::readSignals();
    }
}
