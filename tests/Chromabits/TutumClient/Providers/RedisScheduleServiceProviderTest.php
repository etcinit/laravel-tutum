<?php

namespace Chromabits\Tests\TutumClient\Providers;

use Chromabits\Tests\Support\LaravelTestCase as TestCase;
use Chromabits\TutumClient\Providers\RedisScheduleServiceProvider;

/**
 * Class RedisScheduleServiceProviderTest
 *
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
