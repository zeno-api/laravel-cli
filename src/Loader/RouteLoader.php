<?php

declare(strict_types=1);

namespace Zeno\Laravel\Cli\Loader;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use Symfony\Component\Yaml\Yaml;
use Zeno\Laravel\Cli\Route\Action\ActionMethod;
use Zeno\Laravel\Cli\Route\Action\RouteAction;
use Zeno\Laravel\Cli\Route\Route;
use Zeno\Laravel\Cli\Route\RouteMethods;
use Zeno\Laravel\Cli\Route\Routes;
use Zeno\Laravel\Cli\Route\RouteType;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class RouteLoader
{
    private Factory $validator;
    private Filesystem $files;
    private RouteCollection $routes;

    public function __construct(Factory $validator, Filesystem $files, Router $router)
    {
        $this->validator = $validator;
        $this->files = $files;
        $this->routes = $router->getRoutes();
    }

    public function loadFromFile(string $file): Routes
    {
        if (!$this->files->exists($file)) {
            throw new \Exception(sprintf('File "%s" is not found.', $file));
        }

        $contents = $this->files->get($file);
        $extension = $this->files->extension($file);

        switch ($extension) {
            case 'yaml':
            case 'yml':
                $routes = Yaml::parse($contents);
                break;
            case 'json':
                $routes = json_decode($contents, true);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported format "%s".', $extension));
        }

        return $this->load($routes);
    }

    public function load(array $routesData): Routes
    {
        $routes = new Routes();

        foreach ($routesData as $route) {
            if (null !== $laravelRoute = $route['from_route'] ?? null) {
                if (null === $serviceId = $route['service_id'] ?? null) {
                    throw new \InvalidArgumentException(sprintf('Missing param "service id" for route "%s"', $laravelRoute));
                }

                $routes->route($this->parseLaravelRoute($laravelRoute, $serviceId));

                continue;
            }

            $routes->route($this->parseRoute($route));
        }

        return $routes;
    }

    protected function parseLaravelRoute(string $routeName, string $serviceId): Route
    {
        if (null === $route = $this->routes->getByName($routeName)) {
            throw new \Exception(sprintf('Route with name "%s" not found.', $routeName));
        }

        return Route::fromLaravelRoute($route, $serviceId);
    }

    protected function parseRoute(array $routeData): Route
    {
        $validator = $this->validator->make($routeData, [
            'path'                   => 'required|string',
            'methods'                => 'nullable|array',
            'type'                   => 'nullable|in:single,aggregate',
            'freeze'                 => 'nullable|bool',
            'freeze_ttl'             => 'nullable|numeric',
            'auth_id'                => 'nullable|uuid',
            'forward_headers'        => 'nullable|array',
            'actions'                => 'required|array',
            'actions.*.service_id'   => 'required|uuid',
            'actions.*.destination'  => 'required|string',
            'actions.*.sequence'     => 'nullable|numeric',
            'actions.*.response_key' => 'nullable',
            'actions.*.method'       => 'nullable|in:get,put,post,patch,delete',
        ]);

        $routeData = $validator->validate();
        $route = Route::create($routeData['path']);

        if ($methods = $routeData['methods'] ?? null) {
            $routeMethods = RouteMethods::create();

            foreach ($methods as $method) {
                $routeMethods->{$method}();
            }

            $route->methods($routeMethods);
        }

        if ($type = $routeData['type'] ?? null) {
            $route->type(RouteType::{$type}());
        }

        if ($freeze = $routeData['freeze'] ?? null) {
            $route->freeze($freeze, $routeData['freeze_ttl'] ?? null);
        }

        if ($authId = $routeData['auth_id'] ?? null) {
            $route->auth($authId);
        }

        if ($forwardHeaders = $routeData['forward_headers'] ?? null) {
            $route->forwardHeaders($forwardHeaders);
        }

        foreach ($routeData['actions'] as $action) {
            $route->action($this->parseRouteAction($action));
        }

        return $route;
    }

    protected function parseRouteAction(array $action): RouteAction
    {
        $routeAction = RouteAction::create($action['destination'], $action['service_id']);

        if ($method = $action['method'] ?? null) {
            $routeAction->method(ActionMethod::{$method}());
        }

        if ($sequence = $action['method'] ?? null) {
            $routeAction->sequence($sequence, $action['response_key'] ?? null);
        }

        return $routeAction;
    }
}
