<?php

declare(strict_types=1);

namespace Zeno\Laravel\Cli\Route\Action;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class RouteAction
{
    protected string $destination;
    protected ActionMethod $method;
    protected string $serviceId;
    protected int $sequence = 0;
    protected ?string $responseKey = null;

    public function __construct(string $destination, ActionMethod $method, string $serviceId)
    {
        $this->destination = $destination;
        $this->method = $method;
        $this->serviceId = $serviceId;
    }

    public static function create(string $destination, string $serviceId): RouteAction
    {
        return new static($destination, ActionMethod::get(), $serviceId);
    }

    public function method(ActionMethod $actionMethod): RouteAction
    {
        $this->method = $actionMethod;

        return $this;
    }

    public function sequence(int $sequence, ?string $responseKey): RouteAction
    {
        $this->sequence = $sequence;
        $this->responseKey = $responseKey;

        return $this;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function getMethod(): ActionMethod
    {
        return $this->method;
    }

    public function getServiceId(): string
    {
        return $this->serviceId;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function getResponseKey(): ?string
    {
        return $this->responseKey;
    }

    public function toArray(): array
    {
        return [
            'destination'  => $this->destination,
            'service_id'   => $this->serviceId,
            'sequence'     => $this->sequence,
            'response_key' => $this->responseKey,
            'options'      => [
                'method' => $this->method->getMethod(),
            ],
        ];
    }
}
