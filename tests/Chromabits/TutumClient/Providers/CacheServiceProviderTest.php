<?php

namespace Chromabits\Tests\TutumClient\Providers;

use Chromabits\Tests\Support\LaravelTestCase as TestCase;
use Chromabits\TutumClient\Entities\ContainerLink;
use Chromabits\TutumClient\Providers\CacheServiceProvider;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use Mockery as m;

class CacheServiceProviderTest extends TestCase
{
    public function testRegister()
    {
        $provider = new CacheServiceProvider($this->app);

        $provider->register();

        $this->assertTrue($this->app->bound('Chromabits\TutumClient\Interfaces\ClientInterface'));
        $this->assertTrue($this->app->bound('Chromabits\TutumClient\Cache\TutumRedisPool'));

        $this->assertInstanceOf(
            'Chromabits\TutumClient\Interfaces\ClientInterface',
            $this->app->make('Chromabits\TutumClient\Interfaces\ClientInterface')
        );

        $this->assertTrue($this->app['config']->has('cache.stores.tutumredisconfig'));

        $this->assertInstanceOf(
            'Chromabits\TutumClient\Cache\TutumRedisPool',
            $this->app->make('Chromabits\TutumClient\Cache\TutumRedisPool')
        );

        $this->assertInstanceOf(
            'Illuminate\Cache\CacheManager',
            $this->app->make('cache')
        );

        $this->app['config']->set('cache.default', 'array');
        $this->app['config']->set('cache.stores', [
            'array' => [
                'driver' => 'array'
            ]
        ]);

        $this->assertInstanceOf(
            'Illuminate\Cache\Repository',
            $this->app->make('cache.store')
        );

        $this->assertInstanceOf(
            'Illuminate\Cache\MemcachedConnector',
            $this->app->make('memcached.connector')
        );

        $this->assertInstanceOf(
            'Illuminate\Console\Command',
            $this->app->make('command.cache.clear')
        );

        $this->assertInstanceOf(
            'Illuminate\Console\Command',
            $this->app->make('command.cache.table')
        );
    }

    public function testRegisterWithEnv()
    {
        $provider = new CacheServiceProvider($this->app);

        $provider->register();

        putenv('TUTUM_AUTH=Bearer somekey');

        $client = $this->app->make('Chromabits\TutumClient\Interfaces\ClientInterface');

        $this->assertInstanceOf(
            'Chromabits\TutumClient\Interfaces\ClientInterface',
            $client
        );

        $this->assertEquals('somekey', $client->getBearerKey());

        putenv('TUTUM_AUTH');
    }

    public function testProvides()
    {
        $provider = new CacheServiceProvider($this->app);

        $this->assertInternalType('array', $provider->provides());
    }

    protected function makeMockClient()
    {
        $client = new HttpClient();

        $responseBody = file_get_contents(base_path() . '/resources/testing/tutumContainerShowResponse.json');

        $responseStream = Stream::factory($responseBody);

        $mock = new Mock([
            new Response(
                200,
                [
                    'content-type' => 'application/json'
                ],
                $responseStream
            )
        ]);

        $client->getEmitter()->attach($mock);

        return $client;
    }

    public function testCacheExtension()
    {
        $provider = new CacheServiceProvider($this->app);

        $provider->register();

        $cache = $this->app->make('cache');

        $store = $cache->store('tutum');

        $this->assertInstanceOf(
            'Illuminate\Cache\Repository',
            $store
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->app['config']->set('tutum', [
            'username' => 'testing',

            'apikey' => 'keykey',

            'redis' => [
                'service' => 'redis',

                'password' => 'secret'
            ]
        ]);

        $this->app['config']->set('cache.stores.tutum', [
            'driver' => 'tutum_redis'
        ]);

        putenv('TUTUM_CONTAINER_API_URL=/api/v1/container/c1dd4e1e-1356-411c-8613-e15146633640');
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

        $this->app['cache']->store('tutumredisconfig')->forever('redis_pool', $pool);
    }
}
