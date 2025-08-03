<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Helpers;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Facades\Validator;
use Putyourlightson\Datastar\Validation\SignalValidator;
use starfederation\datastar\ServerSentEventGenerator;

class Request
{
    /**
     * Returns a validator for the signals passed into the request.
     */
    public function getValidator(array $rules, array $messages = [], array $attributes = [])
    {
        Validator::resolver(function(Translator $translator, array $data, array $rules, array $messages, array $attributes) {
            return new SignalValidator($translator, $data, $rules, $messages, $attributes);
        });

        return Validator::make(Request::readSignals(), $rules, $messages, $attributes);
    }

    /**
     * Reads and returns the signals passed into the request.
     */
    public static function readSignals(): array
    {
        return ServerSentEventGenerator::readSignals();
    }
}
