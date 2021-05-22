<?php

declare(strict_types=1);

namespace Zeno\Laravel\Cli\Route;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class RouteMethods
{
    protected array $methods;

    public function __construct(array $methods)
    {
        foreach ($methods as $key => $method) {
            $method = strtolower($method);

            if (!in_array($method, ['get', 'post', 'patch', 'put', 'delete'])) {
                throw new \InvalidArgumentException(sprintf('Invalid method "%s"', $method));
            }

            $methods[$key] = $method;
        }

        $this->methods = $methods;
    }

    public static function create(): RouteMethods
    {
        return new static([]);
    }

    public function get(): RouteMethods
    {
        $this->methods = array_merge($this->methods, ['get']);

        return $this;
    }

    public function post(): RouteMethods
    {
        $this->methods = array_merge($this->methods, ['post']);

        return $this;
    }

    public function patch(): RouteMethods
    {
        $this->methods = array_merge($this->methods, ['patch']);

        return $this;
    }

    public function put(): RouteMethods
    {
        $this->methods = array_merge($this->methods, ['put']);

        return $this;
    }

    public function delete(): RouteMethods
    {
        $this->methods = array_merge($this->methods, ['delete']);

        return $this;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }
}
