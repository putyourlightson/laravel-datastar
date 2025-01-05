<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Putyourlightson\Datastar\Models\Config;
use Putyourlightson\Datastar\Models\Signals;
use Putyourlightson\Datastar\Services\Sse;
use starfederation\datastar\ServerSentEventGenerator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DatastarController extends Controller
{
    public function __construct(
        private readonly Sse $sse,
    ) {
    }

    public function index(Request $request): StreamedResponse
    {
        $config = $request->input('config');

        if (strtolower($request->header('Content-Type')) === 'application/json') {
            // Clear out params to prevent them from being processed by controller actions.
            $request->query->replace();
            $request->request->replace();
        }

        $config = Config::fromHashed($config);
        if ($config === null) {
            throw new BadRequestHttpException('Submitted data was tampered.');
        }

        $view = $config->view;
        $signals = new Signals(ServerSentEventGenerator::readSignals());
        $variables = array_merge(
            [config('datastar.signalsVariableName', 'signals') => $signals],
            $config->variables,
        );

        $response = new StreamedResponse(function() use ($view, $variables) {
            view($view, $variables)->render();
        });

        $this->sse->setResponseHeaders($response);

        return $response;
    }
}
