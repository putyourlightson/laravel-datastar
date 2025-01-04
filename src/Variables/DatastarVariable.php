<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Variables;

use Putyourlightson\Datastar\Services\SseService;

class DatastarVariable
{
    public function __construct(
        protected SseService $sse,
    ) {
    }

    /**
     * Returns a Datastar `@get` action.
     */
    public function get(string $view, array $variables = [], array $options = []): string
    {
        return $this->sse->getAction('get', $view, $variables, $options);
    }

    /**
     * Returns a Datastar `@post` action.
     */
    public function post(string $view, array $variables = [], array $options = []): string
    {
        return $this->sse->getAction('post', $view, $variables, $options);
    }

    /**
     * Returns a Datastar `@put` action.
     */
    public function put(string $view, array $variables = [], array $options = []): string
    {
        return $this->sse->getAction('put', $view, $variables, $options);
    }

    /**
     * Returns a Datastar `@patch` action.
     */
    public function patch(string $view, array $variables = [], array $options = []): string
    {
        return $this->sse->getAction('patch', $view, $variables, $options);
    }

    /**
     * Returns a Datastar `@delete` action.
     */
    public function delete(string $view, array $variables = [], array $options = []): string
    {
        return $this->sse->getAction('delete', $view, $variables, $options);
    }
}
