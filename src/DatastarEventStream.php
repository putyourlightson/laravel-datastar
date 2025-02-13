<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar;

use Illuminate\Support\Facades\View;
use Putyourlightson\Datastar\Models\Signals;
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
     * Returns a signals model populated with signals passed into the request.
     */
    protected function getSignals(): Signals
    {
        return app(Sse::class)->getSignals();
    }

    /**
     * Merges HTML fragments into the DOM.
     */
    protected function mergeFragments(string $data, array $options = []): void
    {
        app(Sse::class)->mergeFragments($data, $options);
    }

    /**
     * Removes HTML fragments from the DOM.
     */
    protected function removeFragments(string $selector, array $options = []): void
    {
        app(Sse::class)->removeFragments($selector, $options);
    }

    /**
     * Merges signals.
     */
    protected function mergeSignals(array $signals, array $options = []): void
    {
        app(Sse::class)->mergeSignals($signals, $options);
    }

    /**
     * Removes signal paths.
     */
    protected function removeSignals(array $paths, array $options = []): void
    {
        app(Sse::class)->removeSignals($paths, $options);
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
     * Renders a Datastar view.
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
