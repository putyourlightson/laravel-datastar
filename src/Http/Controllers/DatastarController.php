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

            $this->processRoute($config->route, $config->params);
        });
    }

    /**
     * Processes a route.
     */
    protected function processRoute(string|array $route, array $params = []): void
    {
        if (is_array($route)) {
            /** @var string|object|null $controller */
            $controller = $route[0] ?? null;
            if (!$controller || !class_exists($controller)) {
                $this->throwException("Controller `$controller` does not exist. Make sure you’re using a valid namespace and that the class is autoloaded.");
            }

            $method = $route[1] ?? 'index';
            $controllerInstance = app($controller);
            if (!method_exists($controllerInstance, $method)) {
                $this->throwException("Method `$method` does not exist on controller `$controller`.");
            }
            app()->call([$controllerInstance, $method], $params);
        } else {
            $this->renderDatastarView($route, $params);
        }
    }
}
