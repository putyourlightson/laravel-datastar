<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Services;

use Illuminate\Support\Facades\View;
use Putyourlightson\Datastar\Helpers\Request;
use Putyourlightson\Datastar\Validation\SignalValidator;
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
     * Whether the response is a streamed response.
     */
    private bool $isStreamedResponse = false;

    /**
     * Server sent events to send.
     *
     * @var EventInterface[]
     */
    private array $sseEvents = [];

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
     * Returns an event stream.
     */
    public function getEventStream(?callable $callable = null): StreamedResponse
    {
        // Abort the process if the client closes the connection.
        ignore_user_abort(false);

        $this->isStreamedResponse = true;

        $eventStream = function() use ($callable) {
            echo $this->getEventOutput();
            ob_flush();
            flush();

            if (is_callable($callable)) {
                $callable();
            }
        };

        return new StreamedResponse($eventStream, 200, ServerSentEventGenerator::headers());
    }

    /**
     * Returns the output of all events as a string.
     */
    public function getEventOutput(bool $reset = true): string
    {
        $data = '';
        foreach ($this->sseEvents as $event) {
            $data .= $event->getOutput();
        }

        if ($reset) {
            $this->resetEvents();
        }

        return $data;
    }

    /**
     * Returns a validator for the signals passed into the request.
     */
    public function getValidator(array $rules, array $messages = [], array $attributes = []): SignalValidator
    {
        return Request::getValidator($rules, $messages, $attributes);
    }

    /**
     * Validates the signals passed into the request.
     */
    public function validate(array $rules, array $messages = [], array $attributes = []): array
    {
        return Request::getValidator($rules, $messages, $attributes)
            ->validate();
    }

    /**
     * Validates the signals passed into the request using a provided error bag.
     */
    public function validateWithBag(string $errorBag, array $rules, array $messages = [], array $attributes = []): array
    {
        return Request::getValidator($rules, $messages, $attributes)
            ->validateWithBag($errorBag);
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
        $options = $this->patchEventOptions(
            config('datastar.defaultElementOptions', []),
            $this->sseEventOptions,
            $options,
        );
        $event = new PatchElements($data, $options);

        $this->processEvent($event);

        return $this;
    }

    /**
     * Removes elements from the DOM.
     */
    public function removeElements(string $selector, array $options = []): static
    {
        $options = $this->patchEventOptions(
            config('datastar.defaultElementOptions', []),
            $this->sseEventOptions,
            $options,
        );
        $event = new RemoveElements($selector, $options);

        $this->processEvent($event);

        return $this;
    }

    /**
     * Patches signals.
     */
    public function patchSignals(array $signals, array $options = []): static
    {
        $options = $this->patchEventOptions(
            config('datastar.defaultSignalOptions', []),
            $this->sseEventOptions,
            $options,
        );
        $event = new PatchSignals($signals, $options);

        $this->processEvent($event);

        return $this;
    }

    /**
     * Executes JavaScript in the browser.
     */
    public function executeScript(string $script, array $options = []): static
    {
        $options = $this->patchEventOptions(
            config('datastar.defaultExecuteScriptOptions', []),
            $this->sseEventOptions,
            $options,
        );
        $event = new ExecuteScript($script, $options);

        $this->processEvent($event);

        return $this;
    }

    /**
     * Redirects the browser by setting the location to the provided URI.
     */
    public function location(string $uri, array $options = []): static
    {
        $options = $this->patchEventOptions(
            config('datastar.defaultExecuteScriptOptions', []),
            $this->sseEventOptions,
            $options,
        );
        $event = new Location($uri, $options);

        $this->processEvent($event);

        return $this;
    }

    /**
     * Renders a view.
     */
    public function renderView(string $view, array $variables = []): static
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

        return $this;
    }

    /**
     * Resets the current events.
     */
    public function resetEvents(): static
    {
        $this->sseEvents = [];

        return $this;
    }

    /**
     * Sets server sent event options for the current request.
     */
    public function setSseEventOptions(array $options): static
    {
        $this->sseEventOptions = $options;

        return $this;
    }

    /**
     * Sets the server sent event method and options currently in process.
     */
    public function setSseInProcess(?string $method, array $options = []): static
    {
        $this->sseMethodInProcess = $method;
        $this->sseOptionsInProcess = $options;

        return $this;
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

        $this->sseEvents[] = $event;

        if ($this->isStreamedResponse) {
            // Clean and end all existing output buffers.
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            echo $event->getOutput();

            if (ob_get_contents()) {
                ob_end_flush();
            }
            flush();

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
