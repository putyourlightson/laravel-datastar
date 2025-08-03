<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Http\Controllers;

use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Pipeline;
use Illuminate\Support\Facades\App;
use Putyourlightson\Datastar\Models\Config;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DatastarController
{
    /**
     * Renders a view in an event stream.
     */
    public function view(): StreamedResponse
    {
        $config = $this->getConfig();
        $view = $config->route;
        $variables = $config->params;

        return sse()->getEventStream(function() use ($view, $variables) {
            sse()->renderView($view, $variables);
        });
    }

    /**
     * Runs a controller action.
     */
    public function action(): ?Response
    {
        $config = $this->getConfig();

        $route = $config->route;
        $params = $config->params;
        $method = '__invoke';

        if (is_array($route)) {
            $route = $config->route[0] ?? null;
            if (empty($route)) {
                throw new BadRequestHttpException('A controller must be specified in the route.');
            }

            $method = $config->route[1] ?? null;
            if (empty($method)) {
                throw new BadRequestHttpException('A controller and method must be specified in the route.');
            }
        }

        if (!str_contains($route, '\\')) {
            $route = 'App\\Http\\Controllers\\' . $route;
        }

        if (!class_exists($route)) {
            throw new BadRequestHttpException("Controller `$route` does not exist. Make sure you’re using a valid namespace and that the class is autoloaded.");
        }

        $controller = app($route);

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
     * Returns the validated configuration from the request.
     *
     * @throws BadRequestHttpException if the configuration is invalid or tampered with.
     */
    private function getConfig(): Config
    {
        $hashedConfig = request()->input('config');
        $config = Config::fromHashed($hashedConfig);

        if ($config === null) {
            throw new BadRequestHttpException('Submitted data was tampered.');
        }

        return $config;
    }

    /**
     * Resolves route bindings for the given controller and method.
     */
    private function resolveRouteBindings($controller, string $method, array $rawParams): array
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
    private function buildMiddlewareStack(object $controller, string $method): array
    {
        if (!($controller instanceof HasMiddleware)) {
            return [];
        }

        $middlewareStack = [];
        $aliases = app('router')->getMiddleware();

        foreach ($controller::middleware() as $middleware) {
            if ($middleware instanceof Middleware) {
                if ($this->middlewareShouldApply($middleware, $method)) {
                    if (is_callable($middleware->middleware)) {
                        $resolved = $middleware->middleware;
                    } elseif (is_string($middleware->middleware)) {
                        $resolved = $aliases[$middleware->middleware] ?? $middleware->middleware;
                    } else {
                        throw new BadRequestHttpException('Invalid middleware type.');
                    }
                    $middlewareStack[] = $resolved;
                }
            } else {
                $resolved = $aliases[$middleware] ?? $middleware;
                $middlewareStack[] = $resolved;
            }
        }

        return $middlewareStack;
    }

    /**
     * Returns whether middleware should apply to the given method.
     */
    private function middlewareShouldApply(Middleware $middleware, string $method): bool
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
