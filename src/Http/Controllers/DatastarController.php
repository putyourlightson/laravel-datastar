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

            $output = $this->renderDatastarView($config->view, $config->variables);

            if ($config->getFragments) {
                $this->mergeFragments($output);
            }
        });
    }
}
