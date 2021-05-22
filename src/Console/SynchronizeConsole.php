<?php

declare(strict_types=1);

namespace Zeno\Laravel\Cli\Console;

use Illuminate\Console\Command;
use Zeno\Laravel\Cli\Client;
use Zeno\Laravel\Cli\Loader\RouteLoader;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class SynchronizeConsole extends Command
{
    protected $signature = 'zeno:sync {--file=}';
    protected $description = 'Synchronize zeno routes';

    public function handle(RouteLoader $routeLoader, Client $client): void
    {
        $this->comment('Synchronizing routes');

        $routes = $routeLoader->loadFromFile($this->getFile());
        $client->sync($routes);

        $this->info('Routes has been synchronized!');
    }

    private function getFile(): string
    {
        if (null !== $file = $this->option('file')) {
            return $file;
        }

        return base_path('zeno-routes.yaml');
    }
}
