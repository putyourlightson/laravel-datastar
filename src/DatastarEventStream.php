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
     * Returns a streamed response.
     */
    protected function getStreamedResponse(callable $callable): StreamedResponse
    {
        return app(Sse::class)->getStreamedResponse($callable);
    }

    /**
     * Sends an event stream.
     */
    protected function sendEventStream(): void
    {
        app(Sse::class)->sendEventStream();
    }

    /**
     * Sets whether SSE events should be sent when processed.
     */
    protected function shouldSendSseEvents(bool $value = true): void
    {
        app(Sse::class)->shouldSendSseEvents($value);
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
    protected function patchElements(string $data, array $options = []): void
    {
        app(Sse::class)->patchElements($data, $options);
    }

    /**
     * Removes elements from the DOM.
     */
    protected function removeElements(string $selector, array $options = []): void
    {
        app(Sse::class)->removeElements($selector, $options);
    }

    /**
     * Patches signals.
     */
    protected function patchSignals(array $signals, array $options = []): void
    {
        app(Sse::class)->patchSignals($signals, $options);
    }

    /**
     * Executes JavaScript in the browser.
     */
    protected function executeScript(string $script, array $options = []): void
    {
        app(Sse::class)->executeScript($script, $options);
    }

    /**
     * Redirects the browser by setting the location to the provided URI.
     */
    protected function location(string $uri, array $options = []): void
    {
        app(Sse::class)->location($uri, $options);
    }

    /**
     * Renders and returns Datastar view.
     */
    protected function renderDatastarView(string $view, array $variables = []): void
    {
        app(Sse::class)->renderDatastarView($view, $variables);
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
