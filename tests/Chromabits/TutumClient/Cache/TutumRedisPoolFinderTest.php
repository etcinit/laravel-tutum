<?php

namespace Tests\Chromabits\TutumClient\Cache;

use Chromabits\TutumClient\Cache\TutumRedisPoolFinder;
use Chromabits\TutumClient\Client;
use Chromabits\TutumClient\Entities\ContainerLink;
use Chromabits\TutumClient\Providers\CacheServiceProvider;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use Tests\Chromabits\Support\LaravelTestCase as TestCase;

/**
 * Class TutumRedisPoolFinderTest
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Tests\TutumClient\Cache
 */
class TutumRedisPoolFinderTest extends TestCase
{
    public function testRefresh()
    {
        // Inject mock HTTP client
        $this->bindMockClient();

        $finder = new TutumRedisPoolFinder($this->app, $this->app['cache']);

        $finder->refresh();

        $this->assertTrue(
            $this->app['cache']->store('tutumredisconfig')->has('redis_pool')
        );

        $pool = $this->app['cache']
            ->store('tutumredisconfig')
            ->get('redis_pool');

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
        $this->bindMockClient();

        $this->app['config']->set('tutum.redis', []);

        $finder = new TutumRedisPoolFinder($this->app, $this->app['cache']);

        $finder->refresh();
    }

    /**
     * Inject mock HTTP client
     */
    protected function bindMockClient()
    {
        $this->app->bind(
            'Chromabits\TutumClient\Interfaces\ClientInterface',
            function () {
                $client = new Client('testing', 'testing');

                $client->setHttpClient($this->makeMockClient());

                return $client;
            }
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testRefreshWithoutEnv()
    {
        // Inject mock HTTP client
        $this->bindMockClient();

        putenv('TUTUM_CONTAINER_API_URL');

        $finder = new TutumRedisPoolFinder($this->app, $this->app['cache']);

        $finder->refresh();
    }

    /**
     * Make a mock HTTP client
     *
     * @return \GuzzleHttp\Client
     */
    protected function makeMockClient()
    {
        $client = new HttpClient();

        $responseBody = file_get_contents(
            base_path() . '/resources/testing/tutumContainerShowResponse.json'
        );

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

        putenv(
            'TUTUM_CONTAINER_API_URL='
            . '/api/v1/container/c1dd4e1e-1356-411c-8613-e15146633640'
        );

        $cacheProvider = new CacheServiceProvider($this->app);

        $cacheProvider->register();
    }
}
