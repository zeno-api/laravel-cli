<?php

declare(strict_types=1);

namespace Zeno\Laravel\Cli\Provider;

use Closure;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Zeno\Laravel\Cli\Client;
use Zeno\Laravel\Cli\Console\SynchronizeConsole;
use Zeno\Laravel\Cli\Loader\RouteLoader;
use Zeno\Signature\Signer;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class ZenoLaravelCliServiceProvider extends ServiceProvider
{
    public function booting(Closure $callback)
    {
        $this->publishResources();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/zeno.php', 'zeno');

        $this->registerRouteLoader();
        $this->registerClient();
        $this->registerConsole();
    }

    protected function registerClient(): void
    {
        $endpoint = config('zeno.endpoint');
        $clientId = config('zeno.client_id');
        $clientSecret = config('zeno.client_secret');

        if ($endpoint && $clientId && $clientSecret) {
            $this->app->singleton(Client::class, function ($app) {
                return new Client(
                    config('zeno.endpoint'),
                    config('zeno.client_id'),
                    config('zeno.client_secret'),
                    $app->make(Signer::class)
                );
            });
        }
    }

    protected function registerRouteLoader(): void
    {
        $this->app->singleton(RouteLoader::class, function ($app) {
            return new RouteLoader($app['validator'], $app[Filesystem::class], $app['router']);
        });
    }

    protected function registerConsole(): void
    {
        $this->commands(SynchronizeConsole::class);
    }

    protected function publishResources(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/zeno.php' => config_path('zeno.php'),
            ], ['config', 'zeno']);
        }
    }
}
