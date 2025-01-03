<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Globals;

use Putyourlightson\Datastar\Services\SseService;

class DatastarGlobal
{
    private SseService $sse;

    public function __construct(SseService $sse)
    {
        $this->sse = $sse;
    }

    public function get(string $view, array $variables = [], array $options = []): string
    {
        return $this->sse->getAction('get', $view, $variables, $options);
    }

    public function post(string $view, array $variables = [], array $options = []): string
    {
        return $this->sse->getAction('post', $view, $variables, $options);
    }

    public function put(string $view, array $variables = [], array $options = []): string
    {
        return $this->sse->getAction('put', $view, $variables, $options);
    }

    public function patch(string $view, array $variables = [], array $options = []): string
    {
        return $this->sse->getAction('patch', $view, $variables, $options);
    }

    public function delete(string $view, array $variables = [], array $options = []): string
    {
        return $this->sse->getAction('delete', $view, $variables, $options);
    }
}
