<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar;

use Putyourlightson\Datastar\Helpers\Request;
use Putyourlightson\Datastar\Services\Sse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

trait DatastarEventStream
{
    /**
     * Returns an event stream.
     */
    protected function getEventStream(?callable $callable = null): StreamedResponse
    {
        return app(Sse::class)->getEventStream($callable);
    }

    /**
     * Returns the output of all events as a string.
     */
    protected function getEventOutput(): string
    {
        return app(Sse::class)->getEventOutput();
    }

    /**
     * Reads and returns the signals passed into the request.
     */
    protected function readSignals(): array
    {
        return Request::readSignals();
    }

    /**
     * Patches elements into the DOM.
     */
    protected function patchElements(string $data, array $options = []): static
    {
        app(Sse::class)->patchElements($data, $options);

        return $this;
    }

    /**
     * Removes elements from the DOM.
     */
    protected function removeElements(string $selector, array $options = []): static
    {
        app(Sse::class)->removeElements($selector, $options);

        return $this;
    }

    /**
     * Patches signals.
     */
    protected function patchSignals(array $signals, array $options = []): static
    {
        app(Sse::class)->patchSignals($signals, $options);

        return $this;
    }

    /**
     * Executes JavaScript in the browser.
     */
    protected function executeScript(string $script, array $options = []): static
    {
        app(Sse::class)->executeScript($script, $options);

        return $this;
    }

    /**
     * Redirects the browser by setting the location to the provided URI.
     */
    protected function location(string $uri, array $options = []): static
    {
        app(Sse::class)->location($uri, $options);

        return $this;
    }

    /**
     * Renders and returns Datastar view.
     */
    protected function renderDatastarView(string $view, array $variables = []): static
    {
        app(Sse::class)->renderDatastarView($view, $variables);

        return $this;
    }

    /**
     * Throws an exception with the appropriate formats for easier debugging.
     *
     * @phpstan-return never
     */
    protected function throwException(Throwable|string $exception): void
    {
        app(Sse::class)->throwException($exception);
    }
}
