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
        // Setup file cache store for redis server pool
        $this->app['config']->set('cache.stores.tutumredisconfig', [
            'driver' => 'file',
            'path' => storage_path() . '/tutum/redis'
        ]);

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

        $this->app->bind('Chromabits\TutumClient\Cache\TutumRedisPool', function ($app) {
            return new TutumRedisPool($app);
        });

        $this->app->singleton('cache', function($app)
        {
            $manager =  new CacheManager($app);

            $manager->extend('tutum_redis', function ($app, $config) {
                return $app->make('Chromabits\TutumClient\Cache\TutumRedisPool')->createRedisDriver($config);
            });

            return $manager;
        });

        $this->app->singleton('cache.store', function($app)
        {
            return $app['cache']->driver();
        });

        $this->app->singleton('memcached.connector', function()
        {
            return new MemcachedConnector;
        });

        $this->registerCommands();
    }

    /**
     * Boot the service provider
     */
    public function boot()
    {

    }

    /**
     * Register the cache related console commands.
     *
     * @return void
     */
    public function registerCommands()
    {
        $this->app->singleton('command.cache.clear', function($app)
        {
            return new ClearCommand($app['cache']);
        });

        $this->app->singleton('command.cache.table', function($app)
        {
            return new CacheTableCommand($app['files'], $app['composer']);
        });

        $this->commands('command.cache.clear', 'command.cache.table');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'cache', 'cache.store', 'memcached.connector', 'command.cache.clear', 'command.cache.table',
            'Chromabits\TutumClient\Interfaces\ClientInterface',
            'Chromabits\TutumClient\Cache\TutumRedisPool'
        ];
    }
}
