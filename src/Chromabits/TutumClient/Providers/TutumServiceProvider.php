<?php

namespace Chromabits\TutumClient\Providers;

use Chromabits\TutumClient\Client;
use Chromabits\TutumClient\ClientFactory;
use Chromabits\TutumClient\Support\EnvUtils;
use Illuminate\Support\ServiceProvider;

/**
 * Class TutumServiceProvider
 *
 * Registers Tutum service providers
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\TutumClient\Providers
 */
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
        $this->app->bind(
            'Chromabits\TutumClient\Interfaces\ClientInterface',
            function ($app) {
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
            }
        );
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
