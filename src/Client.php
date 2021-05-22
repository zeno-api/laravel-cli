<?php

declare(strict_types=1);

namespace Zeno\Laravel\Cli;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\RequestOptions;
use Zeno\Laravel\Cli\Route\Routes;
use Zeno\Signature\Claim;
use Zeno\Signature\Signature;
use Zeno\Signature\Signer;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class Client
{
    private HttpClient $httpClient;
    private string $clientId;
    private string $clientSecret;
    private Signer $signer;

    public function __construct(string $endpoint, string $clientId, string $clientSecret, Signer $signer)
    {
        $this->httpClient = new HttpClient([
            'base_uri'    => $endpoint,
            'http_errors' => false,
        ]);

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->signer = $signer;
    }

    public function sync(Routes $routes): void
    {
        $path = '/v1/routes';
        $payload = ['routes' => $routes->toArray()];
        $signature = $this->sign($path, $payload);

        $response = $this->httpClient->post($path, [
            RequestOptions::JSON    => $payload,
            RequestOptions::HEADERS => $this->parseHeaders($signature),
        ]);

        if (200 !== $statusCode = $response->getStatusCode()) {
            throw new \Exception(sprintf('Failed sync api routes with response code "%s"', $statusCode), 400);
        }
    }

    private function parseHeaders(Signature $signature): array
    {
        $headers = [];

        foreach ($signature->getHeaders() as $header => $value) {
            $headers['X-' . $header] = $value;
        }

        return $headers;
    }

    private function sign(string $path, array $payload): Signature
    {
        return $this->signer->sign(
            $this->clientId,
            $this->clientSecret,
            new Claim($path, $payload)
        );
    }
}
