<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Http\Controllers;

use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Pipeline;
use Illuminate\Support\Facades\App;
use Putyourlightson\Datastar\DatastarEventStream;
use Putyourlightson\Datastar\Models\Config;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DatastarController extends Controller
{
    use DatastarEventStream;

    /**
     * Default controller action.
     */
    public function __invoke(): ?Response
    {
        $hashedConfig = request()->input('config');
        $config = Config::fromHashed($hashedConfig);
        if ($config === null) {
            throw new BadRequestHttpException('Submitted data was tampered.');
        }

        $route = $config->route;
        $params = $config->params;

        if (is_string($route)) {
            return $this->getEventStream(function() use ($route, $params) {
                $this->renderDatastarView($route, $params);
            });
        }

        $route = $config->route[0] ?? null;
        if (empty($route)) {
            throw new BadRequestHttpException('A controller must be specified in the route.');
        }

        if (!str_contains($route, '\\')) {
            $route = 'App\\Http\\Controllers\\' . $route;
        }

        if (!class_exists($route)) {
            throw new BadRequestHttpException("Controller `$route` does not exist. Make sure you’re using a valid namespace and that the class is autoloaded.");
        }

        $controller = app($route);
        $method = $config->route[1] ?? '__invoke';

        if (!method_exists($controller, $method)) {
            throw new BadRequestHttpException("Method `$method` does not exist on controller `$route`.");
        }

        $params = $this->resolveRouteBindings($controller, $method, $params);

        $middlewareStack = $this->buildMiddlewareStack($controller, $method);

        return app(Pipeline::class)
            ->send(request())
            ->through($middlewareStack)
            ->then(function() use ($controller, $method, $params) {
                return app()->call([$controller, $method], $params);
            });
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

            if (!($type instanceof ReflectionNamedType) || $type->isBuiltin()) {
                $resolved[$name] = $rawParams[$name] ?? null;
                continue;
            }

            $className = $type->getName();

            if (is_subclass_of($className, Model::class) && isset($rawParams[$name])) {
                /** @var UrlRoutable $instance */
                $instance = App::make($className);
                $resolved[$name] = $instance->resolveRouteBinding($rawParams[$name]);
            } else {
                $resolved[$name] = $rawParams[$name] ?? App::make($className);
            }
        }

        return $resolved;
    }

    /**
     * Builds the middleware stack for the given controller and method.
     */
    protected function buildMiddlewareStack(object $controller, string $method): array
    {
        if (!($controller instanceof HasMiddleware)) {
            return [];
        }

        $middlewareStack = [];

        foreach ($controller::middleware() as $middleware) {
            if ($middleware instanceof Middleware) {
                if ($this->middlewareShouldApply($middleware, $method)) {
                    $middlewareStack[] = $middleware->middleware;
                }
            } else {
                $middlewareStack[] = $middleware;
            }
        }

        return $middlewareStack;
    }

    /**
     * Returns whether middleware should apply to the given method.
     */
    protected function middlewareShouldApply(Middleware $middleware, string $method): bool
    {
        if (!empty($middleware->only) && !in_array($method, $middleware->only)) {
            return false;
        }

        if (!empty($middleware->except) && in_array($method, $middleware->except)) {
            return false;
        }

        return true;
    }
}
