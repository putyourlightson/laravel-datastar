<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Helpers;

use Putyourlightson\Datastar\Services\Sse as SseService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class Sse
{
    /**
     * Returns an event stream.
     */
    public function getEventStream(?callable $callable = null): StreamedResponse
    {
        return app(SseService::class)->getEventStream($callable);
    }

    /**
     * Returns the output of all events as a string.
     */
    public function getEventOutput(): string
    {
        return app(SseService::class)->getEventOutput();
    }

    /**
     * Reads and returns the signals passed into the request.
     */
    public function readSignals(): array
    {
        return Request::readSignals();
    }

    /**
     * Patches elements into the DOM.
     */
    public function patchElements(string $data, array $options = []): static
    {
        app(SseService::class)->patchElements($data, $options);

        return $this;
    }

    /**
     * Removes elements from the DOM.
     */
    public function removeElements(string $selector, array $options = []): static
    {
        app(SseService::class)->removeElements($selector, $options);

        return $this;
    }

    /**
     * Patches signals.
     */
    public function patchSignals(array $signals, array $options = []): static
    {
        app(SseService::class)->patchSignals($signals, $options);

        return $this;
    }

    /**
     * Executes JavaScript in the browser.
     */
    public function executeScript(string $script, array $options = []): static
    {
        app(SseService::class)->executeScript($script, $options);

        return $this;
    }

    /**
     * Redirects the browser by setting the location to the provided URI.
     */
    public function location(string $uri, array $options = []): static
    {
        app(SseService::class)->location($uri, $options);

        return $this;
    }

    /**
     * Renders and returns Datastar view.
     */
    public function renderDatastarView(string $view, array $variables = []): static
    {
        app(SseService::class)->renderDatastarView($view, $variables);

        return $this;
    }

    /**
     * Throws an exception with the appropriate formats for easier debugging.
     *
     * @phpstan-return never
     */
    public function throwException(Throwable|string $exception): void
    {
        app(SseService::class)->throwException($exception);
    }
}
