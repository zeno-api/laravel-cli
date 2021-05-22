<?php

declare(strict_types=1);

namespace Zeno\Laravel\Cli\Route;

use Illuminate\Routing\Route as LaravelRoute;
use Zeno\Laravel\Cli\Route\Action\ActionMethod;
use Zeno\Laravel\Cli\Route\Action\RouteAction;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class Route
{
    protected string $path;
    protected RouteMethods $methods;
    protected bool $freeze = false;
    protected ?int $freezeTtl = null;
    protected ?string $authId = null;
    protected ?array $forwardHeaders = null;
    protected RouteType $routeType;
    protected array $actions = [];

    public function __construct(string $path, RouteType $routeType, RouteMethods $methods)
    {
        $this->path = $path;
        $this->routeType = $routeType;
        $this->methods = $methods;
    }

    public static function create(string $path): Route
    {
        return new static($path, RouteType::single(), RouteMethods::create()->get());
    }

    public static function fromLaravelRoute(LaravelRoute $laravelRoute, string $serviceId): Route
    {
        $path = '/'.$laravelRoute->uri();
        $methods = $laravelRoute->methods();
        $headIndex = array_search('HEAD', $methods);

        if ($headIndex >= 0) {
            array_splice($methods, $headIndex, 1);
        }

        $route = new static($path, RouteType::single(), new RouteMethods($methods));
        $route->action(
            RouteAction::create($path, $serviceId)
                ->method(new ActionMethod($laravelRoute->methods[0]))
        );

        return $route;
    }

    public function type(RouteType $routeType): Route
    {
        $this->routeType = $routeType;

        return $this;
    }

    public function methods(RouteMethods $methods): Route
    {
        $this->methods = $methods;

        return $this;
    }

    public function freeze(bool $freeze, ?int $ttl = null): Route
    {
        $this->freeze = $freeze;
        $this->freezeTtl = $ttl;

        return $this;
    }

    public function auth(string $authId): Route
    {
        $this->authId = $authId;

        return $this;
    }

    public function forwardHeaders(array $headers): Route
    {
        $this->forwardHeaders = $headers;

        return $this;
    }

    public function action(RouteAction $routeAction): Route
    {
        $this->actions[] = $routeAction;

        return $this;
    }

    public function actions(array $routeActions): Route
    {
        foreach ($routeActions as $routeAction) {
            $this->action($routeAction);
        }

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethods(): RouteMethods
    {
        return $this->methods;
    }

    public function isFreeze(): bool
    {
        return $this->freeze;
    }

    public function getFreezeTtl(): ?int
    {
        return $this->freezeTtl;
    }

    public function getAuthId(): ?string
    {
        return $this->authId;
    }

    public function getForwardHeaders(): ?array
    {
        return $this->forwardHeaders;
    }

    public function getType(): RouteType
    {
        return $this->routeType;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function toArray(): array
    {
        return [
            'path'            => $this->path,
            'methods'         => $this->methods->getMethods(),
            'type'            => $this->routeType->getRouteType(),
            'freeze'          => $this->freeze,
            'freeze_ttl'      => $this->freezeTtl,
            'auth_id'         => $this->authId,
            'forward_headers' => $this->forwardHeaders,
            'actions' => array_map(
                function (RouteAction $routeAction) {
                    return $routeAction->toArray();
                },
                $this->actions
            )
        ];
    }
}
