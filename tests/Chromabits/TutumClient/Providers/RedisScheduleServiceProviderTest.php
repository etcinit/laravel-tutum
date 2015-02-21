<?php

namespace Tests\Chromabits\TutumClient\Providers;

use Chromabits\TutumClient\Providers\RedisScheduleServiceProvider;
use Tests\Chromabits\Support\LaravelTestCase as TestCase;

/**
 * Class RedisScheduleServiceProviderTest
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Tests\TutumClient\Providers
 */
class RedisScheduleServiceProviderTest extends TestCase
{
    public function testRegister()
    {
        $provider = new RedisScheduleServiceProvider($this->app);

        $provider->register();
    }

    /**
     * @depends testRegister
     */
    public function testBoot()
    {
        $provider = new RedisScheduleServiceProvider($this->app);

        $provider->register();

        $provider->boot();
    }
}
