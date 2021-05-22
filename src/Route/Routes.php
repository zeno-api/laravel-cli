<?php

declare(strict_types=1);

namespace Zeno\Laravel\Cli\Route;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class Routes
{
    protected array $routes = [];

    public function route(Route $route): Routes
    {
        $this->routes[] = $route;

        return $this;
    }

    public function routes(array $routes): Routes
    {
        foreach ($routes as $route) {
            $this->route($route);
        }

        return $this;
    }

    public function all(): array
    {
        return $this->routes;
    }

    public function toArray(): array
    {
        return array_map(function (Route $route) {
            return $route->toArray();
        }, $this->routes);
    }
}
