<?php

namespace Tests\Chromabits\TutumClient\Console\Commands;

use Chromabits\TutumClient\Console\Commands\TutumRedisRefreshCommand;
use Chromabits\TutumClient\Entities\ContainerLink;
use Mockery as m;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Tests\Chromabits\Support\LaravelTestCase as TestCase;

/**
 * Class TutumRedisRefreshCommandTest
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
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

    /**
     * Make a mock redis pool finder
     *
     * @param bool $return
     */
    protected function mockFinder($return = false)
    {
        $this->app->bind(
            'Chromabits\TutumClient\Cache\TutumRedisPoolFinder',
            function () use ($return) {
                $finder = m::mock(
                    'Chromabits\TutumClient\Cache\TutumRedisPoolFinder'
                );

                $link = new ContainerLink('LINK_1', 'uuid1', 'uuid2', [
                    '3030/tpc' => 'tcp://10.0.0.1:3030'
                ]);

                $links = [];
                $links[] = $link;

                if ($return) {
                    $finder->shouldReceive('refresh')->andReturn($links);
                } else {
                    $finder->shouldReceive('refresh');
                }

                return $finder;
            }
        );
    }

    public function testHandleWithReturn()
    {
        $this->mockFinder(true);

        $command = new TutumRedisRefreshCommand();

        $command->setLaravel($this->app);

        $command->run(new ArrayInput([]), new NullOutput());
    }
}
