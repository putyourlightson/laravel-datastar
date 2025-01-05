<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Models;

use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Config
{
    private const HASH_ALGORITHM = 'sha256';
    private const HASH_LENGTH = 64;

    public string $view = '';
    public array $variables = [];

    /**
     * Creates a new instance from a hashed config string.
     */
    public static function fromHashed(string $config): ?self
    {
        $hash = substr($config, 0, self::HASH_LENGTH);
        $encoded = substr($config, self::HASH_LENGTH);
        $checksum = self::hash($encoded);

        if ($hash !== $checksum) {
            return null;
        }

        $attributes = json_decode($encoded, true);

        return new self($attributes);
    }

    /**
     * Hashes a string.
     */
    public static function hash(string $value): string
    {
        $key = app('encrypter')->getKey();

        return hash_hmac(self::HASH_ALGORITHM, $value, $key);
    }

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Returns a hashed, JSON-encoded array of attributes.
     */
    public function getHashed(): string
    {
        $attributes = array_filter([
            'view' => $this->view,
            'variables' => $this->variables,
        ]);
        $encoded = json_encode($attributes);

        $checksum = self::hash($encoded);

        return $checksum . $encoded;
    }

    /**
     * Validates the model.
     */
    public function validate(): void
    {
        $validator = Validator::make([
            'view' => $this->view,
            'variables' => $this->variables,
        ], [
            'view' => 'required|string',
            'variables' => function(string $attribute, mixed $variables, Closure $fail) {
                $this->validateVariables($attribute, $variables, $fail);
            },
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validates that none of the variables are objects, recursively.
     */
    private function validateVariables(string $attribute, mixed $variables, Closure $fail): void
    {
        $signalsVariableName = config('datastar.signalsVariableName');

        foreach ($variables as $key => $value) {
            if ($key === $signalsVariableName) {
                $fail('Variable `' . $signalsVariableName . '` is reserved. Use a different name or modify the name of the signals variable using the `signalsVariableName` config setting.');
                return;
            }

            if (is_object($value)) {
                $fail('Variable `' . $key . '` is an object, which is a forbidden variable type in the context of a Datastar request.');
                return;
            }

            if (is_array($value)) {
                $this->validateVariables($attribute, $value, $fail);
            }
        }
    }
}
