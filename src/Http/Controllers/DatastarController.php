<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Http\Controllers;

use Illuminate\Routing\Controller;
use Putyourlightson\Datastar\DatastarEventStream;
use Putyourlightson\Datastar\Models\Config;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatastarController extends Controller
{
    use DatastarEventStream;

    /**
     * Default controller action.
     */
    public function index(): StreamedResponse
    {
        return $this->getStreamedResponse(function() {
            $hashedConfig = request()->input('config');
            $config = Config::fromHashed($hashedConfig);
            if ($config === null) {
                $this->throwException('Submitted data was tampered.');
            }

            $view = $config->view;
            $signals = $this->getSignals();
            $variables = array_merge(
                [config('datastar.signalsVariableName', 'signals') => $signals],
                $config->variables,
            );

            if (strtolower(request()->header('Content-Type')) === 'application/json') {
                // Clear out params to prevent them from being processed by controller actions.
                request()->query->replace();
                request()->request->replace();
            }

            $this->renderView($view, $variables);
        });
    }
}
