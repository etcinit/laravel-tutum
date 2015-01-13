<?php

namespace Chromabits\Tests\TutumClient\Providers;

use Chromabits\Tests\Support\LaravelTestCase as TestCase;
use Chromabits\TutumClient\Client;
use Chromabits\TutumClient\Providers\TutumServiceProvider;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use Mockery as m;

/**
 * Class TutumServiceProviderTest
 *
 * @package Chromabits\Tests\TutumClient\Providers
 */
class TutumServiceProviderTest extends TestCase
{
    public function testRegister()
    {
        $provider = new TutumServiceProvider($this->app);

        $provider->register();

        $this->assertTrue($this->app->bound('Chromabits\TutumClient\Interfaces\ClientInterface'));

        $this->assertInstanceOf(
            'Chromabits\TutumClient\Interfaces\ClientInterface',
            $this->app->make('Chromabits\TutumClient\Interfaces\ClientInterface')
        );
    }

    public function testRegisterWith()
    {
        $provider = new TutumServiceProvider($this->app);

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

    public function testProvides()
    {
        $provider = new TutumServiceProvider($this->app);

        $this->assertInternalType('array', $provider->provides());
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
}
