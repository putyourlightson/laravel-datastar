<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Helpers;

class Datastar
{
    /**
     * Returns a Datastar `@get` action.
     */
    public function get(string|array $route, array $params = [], array $options = []): string
    {
        return Action::getAction('get', $route, $params, $options);
    }

    /**
     * Returns a Datastar `@post` action.
     */
    public function post(string|array $route, array $params = [], array $options = []): string
    {
        return Action::getAction('post', $route, $params, $options);
    }

    /**
     * Returns a Datastar `@put` action.
     */
    public function put(string|array $route, array $params = [], array $options = []): string
    {
        return Action::getAction('put', $route, $params, $options);
    }

    /**
     * Returns a Datastar `@patch` action.
     */
    public function patch(string|array $route, array $params = [], array $options = []): string
    {
        return Action::getAction('patch', $route, $params, $options);
    }

    /**
     * Returns a Datastar `@delete` action.
     */
    public function delete(string|array $route, array $params = [], array $options = []): string
    {
        return Action::getAction('delete', $route, $params, $options);
    }

    /**
     * Reads and returns the signals passed into the request.
     */
    public function readSignals(): array
    {
        return Request::readSignals();
    }
}
