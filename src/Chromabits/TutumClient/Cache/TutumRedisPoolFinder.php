<?php

namespace Chromabits\TutumClient\Cache;

use Chromabits\TutumClient\Client;
use Chromabits\TutumClient\Support\EnvUtils;
use Exception;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\StoreInterface;
use Illuminate\Contracts\Foundation\Application;

/**
 * Class TutumRedisPoolFinder
 *
 * @package Chromabits\TutumClient\Cache
 */
class TutumRedisPoolFinder
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * Construct an instance of a TutumRedisPoolFinder
     *
     * @param Application $app
     * @param CacheManager $cacheManager
     */
    public function __construct(Application $app, CacheManager $cacheManager)
    {
        $this->app = $app;

        $this->cacheManager = $cacheManager;
    }

    /**
     * Refresh container Redis links
     *
     * @return \Chromabits\TutumClient\Entities\ContainerLink[]
     * @throws Exception
     */
    public function refresh()
    {
        $links = $this->fetch();

        /** @var StoreInterface $cache */
        $cache = $this->cacheManager->store('tutumredisconfig');

        $cache->forever('redis_pool', $links);

        return $links;
    }

    /**
     * Get container links
     *
     * @return \Chromabits\TutumClient\Entities\ContainerLink[]
     * @throws Exception
     */
    protected function fetch()
    {
        $app = $this->app;

        /** @var Client $client */
        $client = $app->make('Chromabits\TutumClient\Interfaces\ClientInterface');

        $envUtils = new EnvUtils();

        // Check that we have access to the container UUID
        if (!$envUtils->hasContainerUuid()) {
            throw new Exception('Unable to fetch container UUID. Unable to find Redis links');
        }

        // Check that the service name is setup
        if (!$app['config']->has('tutum.redis.service')) {
            throw new Exception('Tutum Redis service name is not set. Unable to find Redis links');
        }

        // Fetch current container information
        $container = $client->container->show($envUtils->getContainerUuid())->get();

        return $container->findLinks($app['config']['tutum.redis.service']);
    }
}
