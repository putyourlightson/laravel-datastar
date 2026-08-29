<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Helpers;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Facades\Validator;
use Putyourlightson\Datastar\Validation\SignalValidator;
use starfederation\datastar\Consts;

class Request
{
    /**
     * Returns a validator for the signals passed into the request.
     */
    public static function getValidator(array $rules, array $messages = [], array $attributes = []): SignalValidator
    {
        Validator::resolver(function(Translator $translator, array $data, array $rules, array $messages, array $attributes) {
            return new SignalValidator($translator, $data, $rules, $messages, $attributes);
        });

        /** @var SignalValidator $validator */
        $validator = Validator::make(Request::readSignals(), $rules, $messages, $attributes);

        return $validator;
    }

    /**
     * Reads and returns the signals passed into the request.
     */
    public static function readSignals(): array
    {
        if (in_array(request()->method(), ['GET', 'DELETE'], true)) {
            $input = request()->query(Consts::DATASTAR_KEY);
        } else {
            $input = request()->getContent();
        }
        $signals = $input ? json_decode($input, true) : [];

        return is_array($signals) ? $signals : [];
    }
}
