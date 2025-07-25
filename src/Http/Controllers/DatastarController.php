<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Putyourlightson\Datastar\DatastarEventStream;
use Putyourlightson\Datastar\Models\Config;
use Putyourlightson\Datastar\Services\Sse;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatastarController extends Controller
{
    use DatastarEventStream;

    /**
     * Default controller action.
     */
    public function index(): StreamedResponse
    {
        return app(Sse::class)->getStreamedResponse(function() {
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
        if (is_string($route)) {
            $this->renderDatastarView($route, $params);

            return;
        }

        /** @var string|object|null $controllerName */
        $controllerName = $route[0] ?? null;
        if (empty($controllerName)) {
            $this->throwException('A controller must be specified in the route.');
        }

        if (!str_contains($controllerName, '\\')) {
            $controllerName = 'App\\Http\\Controllers\\' . $controllerName;
        }

        if (!class_exists($controllerName)) {
            $this->throwException("Controller `$controllerName` does not exist. Make sure you’re using a valid namespace and that the class is autoloaded.");
        }

        $method = $route[1] ?? 'index';
        $controller = app($controllerName);
        if (!method_exists($controller, $method)) {
            $this->throwException("Method `$method` does not exist on controller `$controllerName`.");
        }

        $boundParams = $this->resolveRouteBindings($controller, $method, $params);

        app()->call([$controller, $method], $boundParams);
    }

    /**
     * Resolves route bindings for the given controller and method.
     */
    protected function resolveRouteBindings($controller, string $method, array $rawParams): array
    {
        $reflection = new ReflectionMethod($controller, $method);
        $resolved = [];

        foreach ($reflection->getParameters() as $param) {
            $name = $param->getName();
            $type = $param->getType();

            if (!$type || $type->isBuiltin()) {
                $resolved[$name] = $rawParams[$name] ?? null;
                continue;
            }

            $className = $type->getName();
            if (is_subclass_of($className, Model::class) && isset($rawParams[$name])) {
                $resolved[$name] = $className::findOrFail($rawParams[$name]);
            } else {
                $resolved[$name] = $rawParams[$name] ?? App::make($className);
            }
        }

        return $resolved;
    }
}
