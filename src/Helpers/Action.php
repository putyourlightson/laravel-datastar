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
    public static function getAction(string $method, string $uri, array $options): string
    {
        $args = ["'$uri'"];

        if ($method !== 'get') {
            $headers = $options['headers'] ?? [];
            $headers['X-CSRF-TOKEN'] = csrf_token();
            $options['headers'] = $headers;
        }

        if (!empty($options)) {
            $args[] = json_encode($options);
        }

        $args = implode(', ', $args);

        return '@' . $method . '(' . $args . ')';
    }
}
