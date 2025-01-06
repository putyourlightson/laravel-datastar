<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Services;

use starfederation\datastar\ServerSentEventGenerator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * Merges HTML fragments into the DOM.
     */
    public function mergeFragments(string $data, array $options = []): void
    {
        $options = $this->mergeEventOptions(
            config('datastar.defaultFragmentOptions', []),
            $options,
        );

        $this->callSse('mergeFragments', $data, $options);
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

        $this->callSse('removeFragments', $selector, $options);
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

        $this->callSse('mergeSignals', $signals, $options);
    }

    /**
     * Removes signal paths.
     */
    public function removeSignals(array $paths, array $options = []): void
    {
        $this->callSse('removeSignals', $paths, $options);
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

        $this->callSse('executeScript', $script, $options);
    }

    /**
     * Prepares the response for server sent events.
     */
    public function prepareResponse(StreamedResponse $response): void
    {
        foreach (ServerSentEventGenerator::HEADERS as $name => $value) {
            $response->headers->set($name, $value);
        }
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
     * Calls an SSE method with arguments and cleans output buffers.
     */
    private function callSse(string $method, ...$args): void
    {
        if ($this->sseMethodInProcess && $this->sseMethodInProcess !== $method) {
            $message = 'The SSE method `' . $method . '` cannot be called when `' . $this->sseMethodInProcess . '` is already in process.';
            if (in_array($method, ['mergeSignals', 'removeSignals'])) {
                $message .= ' Ensure that you are not setting or removing signals inside `{% fragment %}` or `{% executescript %}` tags.';
            }

            throw new BadRequestHttpException($message);
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
