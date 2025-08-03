<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Validation;

use Illuminate\Validation\Validator;

class SignalValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function validate()
    {
        return $this->validated();
    }
}
