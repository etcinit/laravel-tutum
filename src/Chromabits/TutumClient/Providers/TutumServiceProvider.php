<?php

namespace Chromabits\TutumClient\Providers;

use Chromabits\TutumClient\Cache\TutumRedisPool;
use Chromabits\TutumClient\Client;
use Chromabits\TutumClient\ClientFactory;
use Chromabits\TutumClient\Support\EnvUtils;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Console\CacheTableCommand;
use Illuminate\Cache\Console\ClearCommand;
use Illuminate\Cache\MemcachedConnector;
use Illuminate\Support\ServiceProvider;

class TutumServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Chromabits\TutumClient\Interfaces\ClientInterface', function ($app) {
            // Get environment information
            $envUtils = new EnvUtils();

            // Check if we can build the client from the environment
            if ($envUtils->hasBearerKey()) {
                return (new ClientFactory())->makeFromEnvironment();
            }

            // Otherwise, default to creating a client from local config
            return new Client(
                $app['config']->get('tutum.username'),
                $app['config']->get('tutum.apikey')
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'Chromabits\TutumClient\Interfaces\ClientInterface'
        ];
    }
}
