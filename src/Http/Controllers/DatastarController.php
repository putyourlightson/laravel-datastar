<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Putyourlightson\Datastar\Services\SseService;
use starfederation\datastar\ServerSentEventGenerator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatastarController extends Controller
{
    protected SseService $sse;

    public function __construct(SseService $sse)
    {
        $this->sse = $sse;
    }

    public function index(Request $request): StreamedResponse
    {
        $config = $request->input('config');
        $signals = ServerSentEventGenerator::readSignals();

        if (strtolower($request->header('Content-Type')) === 'application/json') {
            // Clear out params to prevent them from being processed by controller actions.
//            $request->setQueryParams([]);
//            $request->setBodyParams([]);
        }

        // Set the response headers for the event stream.
        $response = new StreamedResponse(function() use ($config, $signals) {
            $this->sse->stream($config, $signals);
        });

        foreach (ServerSentEventGenerator::HEADERS as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }
}
