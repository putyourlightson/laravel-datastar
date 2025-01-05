<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Helpers;

use Illuminate\Validation\ValidationException;
use Putyourlightson\Datastar\Http\Controllers\DatastarController;
use Putyourlightson\Datastar\Models\Config;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Datastar
{
    /**
     * Returns a Datastar `@get` action.
     */
    public function get(string $view, array $variables = [], array $options = []): string
    {
        return $this->getAction('get', $view, $variables, $options);
    }

    /**
     * Returns a Datastar `@post` action.
     */
    public function post(string $view, array $variables = [], array $options = []): string
    {
        return $this->getAction('post', $view, $variables, $options);
    }

    /**
     * Returns a Datastar `@put` action.
     */
    public function put(string $view, array $variables = [], array $options = []): string
    {
        return $this->getAction('put', $view, $variables, $options);
    }

    /**
     * Returns a Datastar `@patch` action.
     */
    public function patch(string $view, array $variables = [], array $options = []): string
    {
        return $this->getAction('patch', $view, $variables, $options);
    }

    /**
     * Returns a Datastar `@delete` action.
     */
    public function delete(string $view, array $variables = [], array $options = []): string
    {
        return $this->getAction('delete', $view, $variables, $options);
    }

    /**
     * Returns a Datastar action.
     */
    private function getAction(string $method, string $view, array $variables, array $options): string
    {
        $url = $this->getUrl($view, $variables);
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

        return "@$method($args)";
    }

    /**
     * Returns a Datastar action URL.
     */
    private function getUrl(string $view, array $variables = []): string
    {
        $config = new Config([
            'view' => $view,
            'variables' => $variables,
        ]);

        try {
            $config->validate();
        } catch (ValidationException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }

        return action(
            [DatastarController::class, 'index'],
            ['config' => $config->getHashed()],
        );
    }
}
