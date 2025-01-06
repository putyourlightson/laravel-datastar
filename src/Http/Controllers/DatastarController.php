<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Http\Controllers;

use Illuminate\Routing\Controller;
use Putyourlightson\Datastar\Models\Config;
use Putyourlightson\Datastar\Services\Sse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DatastarController extends Controller
{
    public function __construct(
        protected readonly Sse $sse,
    ) {
    }

    /**
     * Default controller action.
     */
    public function index(): StreamedResponse
    {
        $response = new StreamedResponse(function() {
            $this->stream();
        });

        $this->sse->prepareResponse($response);

        return $response;
    }

    /**
     * Streams the response.
     */
    protected function stream(): void
    {
        $hashedConfig = request()->input('config');
        $config = Config::fromHashed($hashedConfig);
        if ($config === null) {
            throw new BadRequestHttpException('Submitted data was tampered.');
        }

        $view = $config->view;
        $signals = $this->sse->getSignals();
        $variables = array_merge(
            [config('datastar.signalsVariableName', 'signals') => $signals],
            $config->variables,
        );

        if (strtolower(request()->header('Content-Type')) === 'application/json') {
            // Clear out params to prevent them from being processed by controller actions.
            request()->query->replace();
            request()->request->replace();
        }

        $this->sse->renderView($view, $variables);
    }
}
