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
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DatastarController extends Controller
{
    use DatastarEventStream;

    /**
     * Default controller action.
     */
    public function index(): Response
{
        $hashedConfig = request()->input('config');
        $config = Config::fromHashed($hashedConfig);
        if ($config === null) {
            throw new BadRequestHttpException('Submitted data was tampered.');
        }

        if (is_string($config->route)) {
            return $this->getStreamedResponse(function() use ($config) {
                $this->renderDatastarView($config->route, $config->params);
            });
        }

        /** @var string|object|null $controllerName */
        $controllerName = $config->route[0] ?? null;
        if (empty($controllerName)) {
            throw new BadRequestHttpException('A controller must be specified in the route.');
        }

        if (!str_contains($controllerName, '\\')) {
            $controllerName = 'App\\Http\\Controllers\\' . $controllerName;
        }

        if (!class_exists($controllerName)) {
            throw new BadRequestHttpException("Controller `$controllerName` does not exist. Make sure you’re using a valid namespace and that the class is autoloaded.");
        }

        $method = $config->route[1] ?? 'index';
        $controller = app($controllerName);
        if (!method_exists($controller, $method)) {
            throw new BadRequestHttpException("Method `$method` does not exist on controller `$controllerName`.");
        }

        $boundParams = $this->resolveRouteBindings($controller, $method, $config->params);

        return app()->call([$controller, $method], $boundParams);
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
