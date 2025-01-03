<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Models;

use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ConfigModel
{
    protected const HASH_LENGTH = 64;
    protected const HASH_ALGORITHM = 'sha256';

    public string $view = '';
    public array $variables = [];
    public bool $includeCsrfToken = false;
    public ?string $csrfToken = null;

    /**
     * Creates a new instance from an encrypted config string.
     */
    public static function fromHashed(string $config): ?self
    {
        $hash = substr($config, 0, static::HASH_LENGTH);
        $encoded = substr($config, static::HASH_LENGTH);
        $checksum = static::hash($encoded);

        if ($hash !== $checksum) {
            return null;
        }

        $attributes = json_decode($encoded, true);

        return new self($attributes);
    }

    /**
     * Hashes a string.
     */
    protected static function hash(string $value): string
    {
        $key = app('encrypter')->getKey();

        return hash_hmac(static::HASH_ALGORITHM, $value, $key);
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
        if ($this->includeCsrfToken) {
            $this->csrfToken = csrf_token();
        }

        $attributes = array_filter([
            'view' => $this->view,
            'variables' => $this->variables,
            'csrfToken' => $this->csrfToken,
        ]);
        $encoded = json_encode($attributes);

        $checksum = static::hash($encoded);

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
            'includeCsrfToken' => $this->includeCsrfToken,
        ], [
            'view' => 'required|string',
            'variables' => function(string $attribute, mixed $variables, Closure $fail) {
                $this->validateVariables($attribute, $variables, $fail);
            },
            'includeCsrfToken' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validates that none of the variables are objects, recursively.
     */
    protected function validateVariables(string $attribute, mixed $variables, Closure $fail): void
    {
//        $signalsVariableName = Datastar::getInstance()->settings->signalsVariableName;
        $signalsVariableName = 'signals';

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
