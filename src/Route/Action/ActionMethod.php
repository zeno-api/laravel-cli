<?php

declare(strict_types=1);

namespace Zeno\Laravel\Cli\Route\Action;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class ActionMethod
{
    private string $method;

    public function __construct(string $method)
    {
        $this->method = strtolower($method);
    }

    public static function get(): ActionMethod
    {
        return new static('get');
    }

    public static function post(): ActionMethod
    {
        return new static('post');
    }

    public static function patch(): ActionMethod
    {
        return new static('patch');
    }

    public static function put(): ActionMethod
    {
        return new static('put');
    }

    public static function delete(): ActionMethod
    {
        return new static('delete');
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
