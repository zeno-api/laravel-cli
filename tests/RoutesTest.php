<?php

declare(strict_types=1);

use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use PHPUnit\Framework\TestCase;
use Zeno\Laravel\Cli\Loader\RouteLoader;
use Zeno\Laravel\Cli\Route\Action\RouteAction;
use Zeno\Laravel\Cli\Route\Route;
use Zeno\Laravel\Cli\Route\Routes;
use Illuminate\Routing\Router as LaravelRouter;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class RoutesTest extends TestCase
{
    public function testFromLaravelRoute()
    {
        $router = new LaravelRouter(new Dispatcher());
        $router->get('/v1/ping')->name('ping');

        $route = Route::fromLaravelRoute($router->getRoutes()->getRoutes()[0], Str::uuid()->toString());

        $this->assertSame('/v1/ping', $route->getPath());
        $this->assertSame(['get'], $route->getMethods()->getMethods());
        $this->assertSame('single', $route->getType()->getRouteType());
        $this->assertCount(1, $route->getActions());

        /** @var RouteAction $firstAction */
        $firstAction = $route->getActions()[0];

        $this->assertSame('get', $firstAction->getMethod()->getMethod());
        $this->assertSame('/v1/ping', $firstAction->getDestination());
    }

    public function testLoadFromArray()
    {
        $this->assertRoutes($this->routeLoader()->load($this->routes()));
    }

    public function testLoadFromYaml()
    {
        $this->assertRoutes($this->routeLoader()->loadFromFile(__DIR__.'/routes.yaml'));
    }

    public function testFromJson()
    {
        $this->assertRoutes($this->routeLoader()->loadFromFile(__DIR__.'/routes.json'));
    }

    protected function assertRoutes(Routes $routes): void
    {
        $routes = $routes->all();
        $this->assertCount(1, $routes);

        /** @var Route $firstRoute */
        $firstRoute = $routes[0];

        $this->assertInstanceOf(Route::class, $firstRoute);
        $this->assertSame('/v2/ping', $firstRoute->getPath());
        $this->assertSame('single', $firstRoute->getType()->getRouteType());
        $this->assertSame(['get'], $firstRoute->getMethods()->getMethods());

        $actions = $firstRoute->getActions();

        $this->assertCount(1, $actions);

        /** @var RouteAction $firstAction */
        $firstAction = $actions[0];

        $this->assertSame('/v1/ping', $firstAction->getDestination());
        $this->assertSame('get', $firstAction->getMethod()->getMethod());
    }

    protected function routeLoader(): RouteLoader
    {
        return new RouteLoader(
            new Factory(
                new Translator(
                    new ArrayLoader(),
                    'id'
                )
            ),
            new Filesystem(),
            new LaravelRouter(new Dispatcher())
        );
    }

    protected function routes(): array
    {
        return [
            [
                'path'    => '/v2/ping',
                'methods' => ['get'],
                'actions' => [
                    [
                        'service_id'  => '998a7c0b-3837-486c-8c66-490c248e005a',
                        'destination' => '/v1/ping',
                    ],
                ],
            ],
        ];
    }
}
