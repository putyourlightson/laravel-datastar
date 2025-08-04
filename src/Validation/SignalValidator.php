<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Validation;

use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class SignalValidator extends Validator
{
    public const ERROR_KEY = 'errors';

    /**
     * @inheritdoc
     */
    public function validate(): array
    {
        $validated = [];

        try {
            $validated = parent::validate();
            sse()->patchSignals([static::ERROR_KEY => []]);
        } catch (ValidationException $exception) {
            $this->sendErrorResponse($exception->errors());
        }

        return $validated;
    }

    /**
     * @inheritdoc
     */
    public function validateWithBag(string $errorBag): array
    {
        $validated = [];

        try {
            $validated = parent::validate();
            sse()->patchSignals([$errorBag => []]);
        } catch (ValidationException $exception) {
            $this->sendErrorResponse($exception->errors(), $exception->errorBag);
        }

        return $validated;
    }

    /**
     * @inheritdoc
     */
    public function validated(): array
    {
        $validated = [];

        try {
            $validated = parent::validated();
            sse()->patchSignals([static::ERROR_KEY => []]);
        } catch (ValidationException $exception) {
            $this->sendErrorResponse($exception->errors());
        }

        return $validated;
    }

    /**
     * Sends an error response with the validation errors.
     */
    private function sendErrorResponse(array $errors, string $errorBag = null): void
    {
        $errorKey = $errorBag ?? static::ERROR_KEY;
        sse()->patchSignals([$errorKey => $errors])
            ->getEventStream()
            ->send();
    }
}
