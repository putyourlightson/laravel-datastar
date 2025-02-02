<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar;

use Illuminate\Support\Facades\View;
use Putyourlightson\Datastar\Models\Signals;
use Putyourlightson\Datastar\Services\Sse;
use starfederation\datastar\ServerSentEventGenerator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

trait DatastarEventStream
{
    /**
     * Returns a streamed response.
     */
    protected function getStreamedResponse(callable $callable): StreamedResponse
    {
        $response = new StreamedResponse($callable);

        foreach (ServerSentEventGenerator::headers() as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }

    /**
     * Returns a signals model populated with signals passed into the request.
     */
    protected function getSignals(): Signals
    {
        return new Signals(ServerSentEventGenerator::readSignals());
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
    public function executeScript(string $script, array $options = []): void
    {
        app(Sse::class)->executeScript($script, $options);
    }

    /**
     * Redirects the browser by setting the location to the provided URI.
     */
    public function location(string $uri, array $options = []): void
    {
        app(Sse::class)->location($uri, $options);
    }

    /**
     * Renders a view, catching exceptions.
     */
    protected function renderView(string $view, array $variables): void
    {
        if (!View::exists($view)) {
            $this->throwException('View `' . $view . '` does not exist.');
        }

        try {
            view($view, $variables)->render();
        } catch (Throwable $exception) {
            $this->throwException($exception);
        }
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
