<?php

declare(strict_types=1);

namespace Zeno\Laravel\Cli\Route;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class RouteType
{
    const SINGLE = 'single';
    const AGGREGATE = 'aggregate';

    protected string $routeType;

    public function __construct(string $routeType)
    {
        if (!in_array($routeType, [static::SINGLE, static::AGGREGATE])) {
            throw new \InvalidArgumentException(sprintf('Invalid route type "%s"', $routeType));
        }

        $this->routeType = $routeType;
    }

    public static function single(): RouteType
    {
        return new static(static::SINGLE);
    }

    public static function aggregate(): RouteType
    {
        return new static(static::AGGREGATE);
    }

    public function getRouteType(): string
    {
        return $this->routeType;
    }
}
