<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Services;

use Illuminate\Validation\ValidationException;
use Putyourlightson\Datastar\Http\Controllers\DatastarController;
use Putyourlightson\Datastar\Models\ConfigModel;
use Putyourlightson\Datastar\Models\SignalsModel;
use starfederation\datastar\ServerSentEventGenerator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

class SseService
{
    /**
     * The server sent event generator.
     */
    private ServerSentEventGenerator|null $sse = null;

    /**
     * The server sent event method currently in process.
     */
    private ?string $sseMethodInProcess = null;

    /**
     * The server sent event options currently in process.
     */
    private array|null $sseOptionsInProcess = [];

    /**
     * Returns a Datastar action.
     */
    public function getAction(string $method, string $view, array $variables, array $options): string
    {
        if (empty($view)) {
            throw new BadRequestHttpException('A view must be provided.');
        }

        $url = $this->getUrl($method, $view, $variables);

        $args = ["'$url'"];
        if (!empty($options)) {
            $args[] = json_encode($options);
        }
        $args = implode(', ', $args);

        return "@$method($args)";
    }

    public function getUrl(string $method, string $view, array $variables = []): string
    {
        $config = new ConfigModel([
            'view' => $view,
            'variables' => $variables,
            'includeCsrfToken' => $method !== 'get',
        ]);

        try {
            $config->validate();
        } catch (ValidationException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }

        return action(
            [DatastarController::class, 'index'],
            ['config' => $config->getHashed()],
        );
    }

    /**
     * Merges HTML fragments into the DOM.
     *
     * @used-by FragmentNode
     */
    public function mergeFragments(string $data, array $options = []): void
    {
        $options = $this->mergeEventOptions(
            config('datastar.defaultFragmentOptions') ?? [],
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
            config('datastar.defaultFragmentOptions') ?? [],
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
            config('datastar.defaultSignalOptions') ?? [],
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
     *
     * @used-by ExecuteScriptNode
     */
    public function executeScript(string $script, array $options = []): void
    {
        $options = $this->mergeEventOptions(
            config('datastar.defaultExecuteScriptOptions') ?? [],
            $options,
        );

        $this->callSse('executeScript', $script, $options);
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
     * Streams the response and returns an empty array.
     */
    public function stream(string $config, array $signals): void
    {
        $config = ConfigModel::fromHashed($config);
        if ($config === null) {
            $this->throwException('Submitted data was tampered.');
        }

        $signals = new SignalsModel($signals);
        $variables = array_merge(
            //[Datastar::getInstance()->settings->signalsVariableName => $signals],
            ['signals' => $signals],
            $config->variables,
        );

        view($config->view, $variables)->render();
    }

    /**
     * Returns merged event options with null values removed.
     */
    private function mergeEventOptions(array ...$optionSets): array
    {
        $options = array_merge([], //Datastar::getInstance()->settings->defaultEventOptions;
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
    private function getSse(): ServerSentEventGenerator
    {
        if ($this->sse === null) {
            $this->sse = new ServerSentEventGenerator();
        }

        return $this->sse;
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
            $this->throwException($message);
        }

        // Clean and end all existing output buffers.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $this->getSse()->$method(...$args);

        $this->sseMethodInProcess = null;

        // Start a new output buffer to capture any subsequent inline content.
        ob_start();
    }

    /**
     * Throws an exception with the appropriate formats for easier debugging.
     *
     * @phpstan-return never
     */
    private function throwException(Throwable|string $exception): void
    {
        request()->headers->set('Accept', 'text/html');

        if ($exception instanceof Throwable) {
            throw $exception;
        }

        throw new BadRequestHttpException($exception);
    }
}
