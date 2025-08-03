<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Helpers;

use Illuminate\Validation\ValidationException;
use Putyourlightson\Datastar\Http\Controllers\DatastarController;
use Putyourlightson\Datastar\Models\Config;
use Putyourlightson\Datastar\Services\SseService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Datastar
{
    /**
     * Returns a Datastar `@get` action to render a view.
     */
    public function view(string $view, array $variables = [], array $options = []): string
    {
        $config = $this->getConfig($view, $variables);
        $uri = action([DatastarController::class, 'view'], ['config' => $config->getHashed()]);

        return $this->get($uri, $options);
    }

    /**
     * Returns a Datastar `@post` action to a controller action.
     */
    public function action(string|array $route, array $params = [], array $options = []): string
    {
        $config = $this->getConfig($route, $params);
        $uri = action([DatastarController::class, 'action'], ['config' => $config->getHashed()]);

        return $this->post($uri, $options);
    }

    /**
     * Returns a Datastar `@get` action to the given URI.
     */
    public function get(string $uri, array $options = []): string
    {
        return Action::getAction('get', $uri, $options);
    }

    /**
     * Returns a Datastar `@post` action to the given URI.
     */
    public function post(string $uri, array $options = []): string
    {
        return Action::getAction('post', $uri, $options);
    }

    /**
     * Returns a Datastar `@put` action to the given URI.
     */
    public function put(string $uri, array $options = []): string
    {
        return Action::getAction('put', $uri, $options);
    }

    /**
     * Returns a Datastar `@patch` action to the given URI.
     */
    public function patch(string $uri, array $options = []): string
    {
        return Action::getAction('patch', $uri, $options);
    }

    /**
     * Returns a Datastar `@delete` action to the given URI.
     */
    public function delete(string $uri, array $options = []): string
    {
        return Action::getAction('delete', $uri, $options);
    }

    /**
     * Reads and returns the signals passed into the request.
     */
    public function readSignals(): array
    {
        return Request::readSignals();
    }

    /**
     * Sets server sent event options.
     */
    public function setSseEventOptions(array $options = []): void
    {
        app(SseService::class)->setSseEventOptions($options);
    }


    /**
     * Returns a Datastar config for the given route and parameters.
     */
    private function getConfig(string|array $route, array $params = []): Config
    {
        $config = new Config([
            'route' => $route,
            'params' => $params,
        ]);

        try {
            $config->validate();
        } catch (ValidationException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }

        return $config;
    }
}
