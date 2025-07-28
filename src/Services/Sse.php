<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Services;

use Illuminate\Support\Facades\View;
use Putyourlightson\Datastar\Helpers\Request;
use starfederation\datastar\events\EventInterface;
use starfederation\datastar\events\ExecuteScript;
use starfederation\datastar\events\Location;
use starfederation\datastar\events\PatchElements;
use starfederation\datastar\events\PatchSignals;
use starfederation\datastar\events\RemoveElements;
use starfederation\datastar\ServerSentEventGenerator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

class Sse
{
    /**
     * The response data.
     */
    private string $responseData = '';

    /**
     * Whether SSE events should be sent when processed.
     */
    private bool $shouldSendSseEvents = false;

    /**
     * Server sent event options to send.
     */
    private array $sseEventOptions = [];

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
     * Returns a streamed response that sends an event stream.
     */
    public function getEventStream(): void
    {
        $this->getStreamedResponse(function() {
            echo $this->responseData;
        });
    }

    /**
     * Sets whether SSE events should be sent when processed.
     */
    public function shouldSendSseEvents(bool $value = true): void
    {
        $this->shouldSendSseEvents = $value;
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
    public function patchElements(string $data, array $options = []): void
    {
        $options = $this->patchEventOptions(
            config('datastar.defaultElementOptions', []),
            $this->sseEventOptions,
            $options,
        );
        $event = new PatchElements($data, $options);

        $this->processEvent($event);
    }

    /**
     * Removes elements from the DOM.
     */
    public function removeElements(string $selector, array $options = []): void
    {
        $options = $this->patchEventOptions(
            config('datastar.defaultElementOptions', []),
            $this->sseEventOptions,
            $options,
        );
        $event = new RemoveElements($selector, $options);

        $this->processEvent($event);
    }

    /**
     * Patches signals.
     */
    public function patchSignals(array $signals, array $options = []): void
    {
        $options = $this->patchEventOptions(
            config('datastar.defaultSignalOptions', []),
            $this->sseEventOptions,
            $options,
        );
        $event = new PatchSignals($signals, $options);

        $this->processEvent($event);
    }

    /**
     * Executes JavaScript in the browser.
     */
    public function executeScript(string $script, array $options = []): void
    {
        $options = $this->patchEventOptions(
            config('datastar.defaultExecuteScriptOptions', []),
            $this->sseEventOptions,
            $options,
        );
        $event = new ExecuteScript($script, $options);

        $this->processEvent($event);
    }

    /**
     * Redirects the browser by setting the location to the provided URI.
     */
    public function location(string $uri, array $options = []): void
    {
        $options = $this->patchEventOptions(
            config('datastar.defaultExecuteScriptOptions', []),
            $this->sseEventOptions,
            $options,
        );
        $event = new Location($uri, $options);

        $this->processEvent($event);
    }

    /**
     * Returns a rendered Datastar view.
     */
    public function renderDatastarView(string $view, array $variables = []): void
    {
        if (!View::exists($view)) {
            $this->throwException('View `' . $view . '` does not exist.');
        }

        $signals = $this->readSignals();
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

        if (trim($output) !== '') {
            $this->patchElements($output);
        }
    }

    /**
     * Sets server sent event options for the current request.
     */
    public function setSseEventOptions(array $options): void
    {
        $this->sseEventOptions = $options;
    }

    /**
     * Sets the server sent event method and options currently in process.
     */
    public function setSseInProcess(?string $method, array $options = []): void
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
     * Returns patch event options with null values removed.
     */
    private function patchEventOptions(array ...$optionSets): array
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
     * Processes an event.
     */
    private function processEvent(EventInterface $event): void
    {
        $this->verifySseMethodInProcess($event);

        if ($this->shouldSendSseEvents) {
            // Clean and end all existing output buffers.
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
        }

        $output = $event->getOutput();

        if ($this->shouldSendSseEvents) {
            echo $output;

            if (ob_get_contents()) {
                ob_end_flush();
            }
            flush();
        }

        // Append the resulting output to the response data.
        $this->responseData .= $output;

        if ($this->shouldSendSseEvents) {
            // Start a new output buffer to capture any subsequent inline content.
            ob_start();
        }

        $this->setSseInProcess(null);
    }

    /**
     * Verifies that another SSE method is not already in process.
     */
    private function verifySseMethodInProcess(EventInterface $event): void
    {
        if ($this->sseMethodInProcess === null) {
            return;
        }

        $sseMethods = [
            PatchElements::class => 'patchElements',
            RemoveElements::class => 'removeElements',
            PatchSignals::class => 'patchSignals',
            ExecuteScript::class => 'executeScript',
        ];

        $method = $sseMethods[$event::class] ?? null;
        if ($method === null) {
            return;
        }

        if ($method !== $this->sseMethodInProcess) {
            $message = 'The SSE method `' . $method . '` cannot be called when `' . $this->sseMethodInProcess . '` is already in process.';
            if ($method === 'patchElements') {
                $message .= ' Ensure that you are not setting or removing signals inside `@patchelements` or `@executescript` directives.';
            }
            $this->throwException($message);
        }
    }
}
