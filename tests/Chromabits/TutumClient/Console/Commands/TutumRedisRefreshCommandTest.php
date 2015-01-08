<?php

namespace Chromabits\Tests\TutumClient\Console\Commands;

use Chromabits\Tests\Support\LaravelTestCase as TestCase;
use Chromabits\TutumClient\Console\Commands\TutumRedisRefreshCommand;
use Mockery as m;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Class TutumRedisRefreshCommandTest
 *
 * @package Chromabits\Tests\TutumClient\Console\Commands
 */
class TutumRedisRefreshCommandTest extends TestCase
{
    public function testHandle()
    {
        $this->mockFinder();

        $command = new TutumRedisRefreshCommand();

        $command->setLaravel($this->app);

        $command->run(new ArrayInput([]), new NullOutput());
    }

    protected function mockFinder()
    {
        $this->app->bind('Chromabits\TutumClient\Cache\TutumRedisPoolFinder', function () {
            $finder = m::mock('Chromabits\TutumClient\Cache\TutumRedisPoolFinder');

            $finder->shouldReceive('refresh');

            return $finder;
        });
    }
}
