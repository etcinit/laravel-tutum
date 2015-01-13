<?php

namespace Chromabits\Tests\TutumClient\Cache;

use Chromabits\Tests\Support\LaravelTestCase as TestCase;
use Chromabits\TutumClient\Cache\TutumRedisPoolFinder;
use Chromabits\TutumClient\Client;
use Chromabits\TutumClient\Entities\ContainerLink;
use Chromabits\TutumClient\Providers\CacheServiceProvider;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;

/**
 * Class TutumRedisPoolFinderTest
 *
 * @package Chromabits\Tests\TutumClient\Cache
 */
class TutumRedisPoolFinderTest extends TestCase
{
    public function testRefresh()
    {
        // Inject mock HTTP client
        $this->app->bind('Chromabits\TutumClient\Interfaces\ClientInterface', function ($app) {
            $client = new Client('testing', 'testing');

            $client->setHttpClient($this->makeMockClient());

            return $client;
        });

        $finder = new TutumRedisPoolFinder($this->app, $this->app['cache']);

        $finder->refresh();

        $this->assertTrue($this->app['cache']->store('tutumredisconfig')->has('redis_pool'));

        $pool = $this->app['cache']->store('tutumredisconfig')->get('redis_pool');

        $this->assertEquals(2, count($pool));

        $this->assertTrue($pool[0] instanceof ContainerLink);
        $this->assertTrue($pool[1] instanceof ContainerLink);
    }

    /**
     * @expectedException \Exception
     */
    public function testRefreshWithoutConfig()
    {
        // Inject mock HTTP client
        $this->app->bind('Chromabits\TutumClient\Interfaces\ClientInterface', function ($app) {
            $client = new Client('testing', 'testing');

            $client->setHttpClient($this->makeMockClient());

            return $client;
        });

        $this->app['config']->set('tutum.redis', []);

        $finder = new TutumRedisPoolFinder($this->app, $this->app['cache']);

        $finder->refresh();
    }

    /**
     * @expectedException \Exception
     */
    public function testRefreshWithoutEnv()
    {
        // Inject mock HTTP client
        $this->app->bind('Chromabits\TutumClient\Interfaces\ClientInterface', function ($app) {
            $client = new Client('testing', 'testing');

            $client->setHttpClient($this->makeMockClient());

            return $client;
        });

        putenv('TUTUM_CONTAINER_API_URL');

        $finder = new TutumRedisPoolFinder($this->app, $this->app['cache']);

        $finder->refresh();
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

        $this->app['config']->set('cache.stores.tutumredisconfig', [
            'driver' => 'array'
        ]);

        $this->app['config']->set('cache.stores.tutum', [
            'driver' => 'tutum_redis'
        ]);

        putenv('TUTUM_CONTAINER_API_URL=/api/v1/container/c1dd4e1e-1356-411c-8613-e15146633640');

        $cacheProvider = new CacheServiceProvider($this->app);

        $cacheProvider->register();
    }
}
