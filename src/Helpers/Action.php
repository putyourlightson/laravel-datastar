<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Helpers;

use Illuminate\Validation\ValidationException;
use Putyourlightson\Datastar\Http\Controllers\DatastarController;
use Putyourlightson\Datastar\Models\Config;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Action
{
    /**
     * Returns a Datastar action.
     */
    public static function getAction(string $method, string|array $route, array $params, array $options): string
    {
        $url = self::getUrl($route, $params);
        $args = ["'$url'"];

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

    /**
     * Returns a Datastar URL endpoint.
     */
    public static function getUrl(string|array $route, array $params = []): string
    {
        if (is_string($route) && (str_starts_with($route, 'http') || str_starts_with($route, '/'))) {
            foreach ($params as $key => $value) {
                $route = str_replace('{' . $key . '}', $value, $route);
            }

            return $route;
        }

        $config = new Config([
            'route' => $route,
            'params' => $params,
        ]);

        try {
            $config->validate();
        } catch (ValidationException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }

        return action(
            DatastarController::class,
            ['config' => $config->getHashed()],
        );
    }
}
