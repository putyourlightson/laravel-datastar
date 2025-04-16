<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Services;

use Illuminate\Support\Facades\View;
use Putyourlightson\Datastar\Models\Signals;
use starfederation\datastar\ServerSentEventGenerator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

class Sse
{
    /**
     * The server sent event generator.
     */
    private ServerSentEventGenerator|null $sseGenerator = null;

    /**
     * The server sent event method currently in process.
     */
    private ?string $sseMethodInProcess = null;

    /**
     * The server sent event options currently in process.
     */
    private array|null $sseOptionsInProcess = [];

    /**
     * Returns a streamed response.
     */
    public function getStreamedResponse(callable $callable): StreamedResponse
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
    public function getSignals(): Signals
    {
        return new Signals(ServerSentEventGenerator::readSignals());
    }

    /**
     * Merges HTML fragments into the DOM.
     */
    public function mergeFragments(string $data, array $options = []): void
    {
        $options = $this->mergeEventOptions(
            config('datastar.defaultFragmentOptions', []),
            $options,
        );

        $this->sendSseEvent('mergeFragments', $data, $options);
    }

    /**
     * Removes HTML fragments from the DOM.
     */
    public function removeFragments(string $selector, array $options = []): void
    {
        $options = $this->mergeEventOptions(
            config('datastar.defaultFragmentOptions', []),
            $options,
        );

        $this->sendSseEvent('removeFragments', $selector, $options);
    }

    /**
     * Merges signals.
     */
    public function mergeSignals(array $signals, array $options = []): void
    {
        $options = $this->mergeEventOptions(
            config('datastar.defaultSignalOptions', []),
            $options,
        );

        $this->sendSseEvent('mergeSignals', $signals, $options);
    }

    /**
     * Removes signal paths.
     */
    public function removeSignals(array $paths, array $options = []): void
    {
        $this->sendSseEvent('removeSignals', $paths, $options);
    }

    /**
     * Executes JavaScript in the browser.
     */
    public function executeScript(string $script, array $options = []): void
    {
        $options = $this->mergeEventOptions(
            config('datastar.defaultExecuteScriptOptions', []),
            $options,
        );

        $this->sendSseEvent('executeScript', $script, $options);
    }

    /**
     * Redirects the browser by setting the location to the provided URI.
     */
    public function location(string $uri, array $options = []): void
    {
        $options = $this->mergeEventOptions(
            config('datastar.defaultExecuteScriptOptions', []),
            $options,
        );

        $this->sendSseEvent('location', $uri, $options);
    }

    /**
     * Returns a rendered Datastar view.
     */
    public function renderDatastarView(string $view, array $variables = []): string
    {
        if (!View::exists($view)) {
            $this->throwException('View `' . $view . '` does not exist.');
        }

        $signals = $this->getSignals();
        $variables = array_merge(
            [config('datastar.signalsVariableName', 'signals') => $signals],
            $variables,
        );

        if (strtolower(request()->header('Content-Type')) === 'application/json') {
            // Clear out params to prevent them from being processed by controller actions.
            request()->query->replace();
            request()->request->replace();
        }

        try {
            $output = view($view, $variables)->render();
        } catch (Throwable $exception) {
            $this->throwException($exception);
        }

        return $output;
    }

    /**
     * Sets the server sent event method and options currently in process.
     */
    public function setSseInProcess(string $method, array $options = []): void
    {
        $this->sseMethodInProcess = $method;
        $this->sseOptionsInProcess = $options;
    }

    /**
     * Throws an exception with the appropriate formats for easier debugging.
     *
     * @phpstan-return never
     */
    public function throwException(Throwable|string $exception): void
    {
        request()->headers->set('Accept', 'text/html');

        if ($exception instanceof Throwable) {
            throw $exception;
        }

        throw new BadRequestHttpException($exception);
    }

    /**
     * Returns merged event options with null values removed.
     */
    private function mergeEventOptions(array ...$optionSets): array
    {
        $options = array_merge(
            config('datastar.defaultEventOptions', []),
            $this->sseOptionsInProcess,
        );

        $this->sseOptionsInProcess = [];

        foreach ($optionSets as $optionSet) {
            $options = array_merge($options, $optionSet);
        }

        return array_filter($options, fn($value) => $value !== null);
    }

    /**
     * Returns a server sent event generator.
     */
    private function getSseGenerator(): ServerSentEventGenerator
    {
        if ($this->sseGenerator === null) {
            $this->sseGenerator = new ServerSentEventGenerator();
        }

        return $this->sseGenerator;
    }

    /**
     * Sends an SSE event with arguments and cleans output buffers.
     */
    private function sendSseEvent(string $method, ...$args): void
    {
        if ($this->sseMethodInProcess && $this->sseMethodInProcess !== $method) {
            $message = 'The SSE method `' . $method . '` cannot be called when `' . $this->sseMethodInProcess . '` is already in process.';
            if (in_array($method, ['mergeSignals', 'removeSignals'])) {
                $message .= ' Ensure that you are not setting or removing signals inside `{% fragment %}` or `{% executescript %}` tags.';
            }

            $this->throwException($message);
        }

        // Clean and end all existing output buffers.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $this->getSseGenerator()->$method(...$args);

        $this->sseMethodInProcess = null;

        // Start a new output buffer to capture any subsequent inline content.
        ob_start();
    }
}
