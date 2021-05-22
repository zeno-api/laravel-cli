<?php

declare(strict_types=1);

namespace Zeno\Laravel\Cli\Test;

use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Router as LaravelRouter;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Monolog\Test\TestCase;
use Zeno\Laravel\Cli\Client;
use Zeno\Laravel\Cli\Loader\RouteLoader;
use Zeno\Signature\Signer;
use GuzzleHttp\Client as GuzzleClient;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class ClientTest extends TestCase
{
    private string $endpoint = 'http://localhost:1215';

    public function testSync()
    {
        $routes = $this->routeLoader()->load($this->routes());

        $this->client()->sync($routes);

        $response = (new GuzzleClient(['base_uri' => $this->endpoint]))->get('/v3/ping');

        $this->assertSame(200, $response->getStatusCode());
    }

    protected function client(): Client
    {
        return new Client(
            $this->endpoint,
            '0c15a0db-eccf-429d-b386-2bcf2b97cd67',
            'e6b8a5a13eb18f6d9b22fb91d116bf450b9870cc',
            new Signer()
        );
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
                'path'    => '/v3/ping',
                'methods' => ['get'],
                'actions' => [
                    [
                        'service_id'  => 'd10455f6-a97e-4dbf-a730-122cb8095d46',
                        'destination' => '/v1/ping',
                    ],
                ],
            ],
        ];
    }
}
