<?php

namespace Tests\Chromabits\TutumClient\Cache;

use Chromabits\TutumClient\Cache\TutumRedisPool;
use Chromabits\TutumClient\Entities\ContainerLink;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Tests\Chromabits\Support\LaravelTestCase as TestCase;

/**
 * Class TutumRedisPoolTest
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Tests\TutumClient\Cache
 */
class TutumRedisPoolTest extends TestCase
{
    public function testConstructor()
    {
        $driver = new TutumRedisPool($this->app);

        $this->assertInstanceOf(
            'Chromabits\TutumClient\Cache\TutumRedisPool',
            $driver
        );
    }

    public function testCreateRedisDriver()
    {
        $this->createMockPool();

        $factory = new TutumRedisPool($this->app);

        $driver = $factory->createRedisDriver([]);

        $this->assertInstanceOf(
            'Illuminate\Cache\Repository',
            $driver
        );
    }

    /**
     * Create a cached mock pool of servers
     */
    protected function createMockPool()
    {
        $pool = [];

        $link1 = new ContainerLink('REDIS_1', 'testing-1', 'testing-3', [
            '1000/tpc' => 'tcp://10.0.0.1:1000'
        ]);

        $link2 = new ContainerLink('REDIS_2', 'testing-2', 'testing-3', [
            '1000/tpc' => 'tcp://10.0.0.1:1000'
        ]);

        $pool[] = $link1;
        $pool[] = $link2;

        $this->app['cache']->store('tutumredisconfig')
            ->forever('redis_pool', $pool);
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateRedisDriverWithMissing()
    {
        $factory = new TutumRedisPool($this->app);

        $factory->createRedisDriver([]);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->app['config']->set('cache.stores.tutumredisconfig', [
            'driver' => 'array'
        ]);

        $this->app['config']->set('tutum.redis.password', 'xxxxxxxx');
    }

    /**
     * Create an barebones Laravel application
     */
    protected function createApplication()
    {
        $this->app = new Application(__DIR__ . '/../../../../');

        $this->app->instance('config', new Repository([]));

        $this->app['config']->set('app', [
            'providers' => [
                'Illuminate\Cache\CacheServiceProvider',
                'Illuminate\Redis\RedisServiceProvider',
                'Illuminate\Filesystem\FilesystemServiceProvider',
            ]
        ]);

        $this->app->registerConfiguredProviders();

        $this->app->boot();
    }
}
