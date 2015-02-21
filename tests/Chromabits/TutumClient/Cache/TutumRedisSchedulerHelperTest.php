<?php

namespace Tests\Chromabits\TutumClient\Cache;

use Chromabits\TutumClient\Cache\TutumRedisScheduleHelper;
use Illuminate\Console\Scheduling\Schedule;
use Tests\Chromabits\Support\LaravelTestCase as TestCase;

/**
 * Class TutumRedisSchedulerHelperTest
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Tests\TutumClient\Cache
 */
class TutumRedisSchedulerHelperTest extends TestCase
{
    public function testConstructor()
    {
        new TutumRedisScheduleHelper(
            $this->app->make('Illuminate\Console\Scheduling\Schedule'),
            $this->app->make('Illuminate\Contracts\Config\Repository')
        );
    }

    public function testSetup()
    {
        /** @var Schedule $schedule */
        $schedule = $this->app->make('Illuminate\Console\Scheduling\Schedule');

        $helper = new TutumRedisScheduleHelper(
            $schedule,
            $this->app->make('Illuminate\Contracts\Config\Repository')
        );

        $helper->setup();

        $this->assertGreaterThan(0, count($schedule->events()));
    }

    public function testSetupWithFiveConfig()
    {
        /** @var Schedule $schedule */
        $schedule = $this->app->make('Illuminate\Console\Scheduling\Schedule');

        $this->app['config']->set('tutum.redis.frequency', 'five');

        $helper = new TutumRedisScheduleHelper(
            $schedule,
            $this->app->make('Illuminate\Contracts\Config\Repository')
        );

        $helper->setup();

        $this->assertGreaterThan(0, count($schedule->events()));
    }

    public function testSetupWithThirtyConfig()
    {
        /** @var Schedule $schedule */
        $schedule = $this->app->make('Illuminate\Console\Scheduling\Schedule');

        $this->app['config']->set('tutum.redis.frequency', 'thirty');

        $helper = new TutumRedisScheduleHelper(
            $schedule,
            $this->app->make('Illuminate\Contracts\Config\Repository')
        );

        $helper->setup();

        $this->assertGreaterThan(0, count($schedule->events()));
    }

    /**
     * @expectedException \Exception
     */
    public function testSetupWithInvalidConfig()
    {
        /** @var Schedule $schedule */
        $schedule = $this->app->make('Illuminate\Console\Scheduling\Schedule');

        $this->app['config']->set('tutum.redis.frequency', 'blahblah');

        $helper = new TutumRedisScheduleHelper(
            $schedule,
            $this->app->make('Illuminate\Contracts\Config\Repository')
        );

        $helper->setup();
    }
}
