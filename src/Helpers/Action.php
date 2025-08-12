<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Helpers;

class Action
{
    /**
     * Returns a Datastar action to a URI.
     */
    public static function getAction(string $method, string $uri, array|string $options): string
    {
        $args = ["'$uri'"];

        if ($method !== 'get') {
            $options = self::addCsrfToken($options);
        } else {
            $options = is_array($options) ? json_encode($options) : $options;
        }

        if (!empty($options)) {
            $args[] = json_encode($options);
        }

        $args = implode(', ', $args);

        return '@' . $method . '(' . $args . ')';
    }

    private static function addCsrfToken(array|string $options): string
    {
        $csrfHeader = 'X-CSRF-TOKEN';

        // Get the CSRF token from the request or use an empty string if not available.
        $token = csrf_token() ?? '';

        if (is_array($options)) {
            return self::addCsrfToArray($options, $csrfHeader, $token);
        }

        return self::addCsrfToString($options, $csrfHeader, $token);
    }

    private static function addCsrfToArray(array $options, string $csrfHeader, string $token): string
    {
        $headers = $options['headers'] ?? [];
        $headers[$csrfHeader] = $token;
        $options['headers'] = $headers;

        return json_encode($options);
    }

    private static function addCsrfToString(string $options, string $csrfHeader, string $token): string
    {
        if (preg_match('/headers:\s*\{/i', $options)) {
            return preg_replace(
                '/headers:\s*\{/i',
                'headers: {"' . $csrfHeader . '": "' . $token . '", ',
                $options
            );
        }

        if (preg_match('/}\s*$/', $options)) {
            return preg_replace(
                '/}\s*$/',
                ', headers: {"' . $csrfHeader . '": "' . $token . '"}}',
                $options
            );
        }

        return json_encode(['headers' => [$csrfHeader => $token]]);
    }
}
