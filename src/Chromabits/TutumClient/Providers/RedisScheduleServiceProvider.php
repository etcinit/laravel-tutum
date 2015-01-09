<?php

namespace Chromabits\TutumClient\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class RedisScheduleServiceProvider
 *
 * @package Chromabits\TutumClient\Providers
 */
class RedisScheduleServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the service provider
     */
    public function boot()
    {
        $helper = $this->app->make('Chromabits\TutumClient\Cache\TutumRedisScheduleHelper');

        $helper->setup();
    }
}
