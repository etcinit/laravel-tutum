<?php

namespace Chromabits\TutumClient\Cache;

use Chromabits\TutumClient\Entities\ContainerLink;
use Exception;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\RedisStore;
use Illuminate\Cache\Repository;
use Illuminate\Cache\StoreInterface;
use Illuminate\Redis\Database;

/**
 * Class TutumRedisPool
 *
 * A Redis cache driver for Laravel that uses Tutum to find connections
 *
 * @package Chromabits\TutumClient\Cache
 */
class TutumRedisPool
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Connections pool
     *
     * @var Array
     */
    protected $connections;

    /**
     * @var CacheManager
     */
    protected $cache;

    /**
     * @var StoreInterface
     */
    protected $store;

    /**
     * Construct an instance of a TutumRedisPool
     *
     * @param $app
     */
    public function __construct($app)
    {
        $this->app = $app;

        $this->cache = $app['cache'];

        $this->store = $this->cache->store('tutumredisconfig');
    }

    /**
     * Create an instance of the Redis cache driver.
     *
     * @param array $config
     * @return RedisStore
     * @throws Exception
     */
    public function createRedisDriver(array $config)
    {
        $redis = new Database($this->getRedisConnections());

        return $this->repository(new RedisStore($redis, $this->getPrefix($config), 'default'));
    }

    /**
     * Builds a cluster configuration for a Redis client using links discovered on Tutum's API
     *
     * @return array
     * @throws Exception
     */
    protected function getRedisConnections()
    {
        $connections = [];

        $connections['cluster'] = true;

        // Check that there is an array of ContainerLinks in the local Tutum cache
        // If there is, we will use it to build a cluster connection
        if ($this->store->get('redis_pool')) {
            $pool = $this->store->get('redis_pool');

            /** @var ContainerLink $link */
            foreach ($pool as $link) {
                $endpoints = array_values($link->getEndpointsAsUrls());

                $endpoint = $endpoints[0];

                $connection = [
                    'host' => $endpoint->getHost(),
                    'port' => $endpoint->getPort()
                ];

                if ($this->app['config']->has('tutum.redis.password')) {
                    $connection['password'] = $this->app['config']['tutum.redis.password'];
                }

                $connections[$link->getName()] = $connection;
            }

            return $connections;
        }

        // If there is not an array, then we have to bail out since the web server usually
        // does not have access to the environment variables to hit Tutum's API and fetch
        // linked services
        throw new Exception('Unable to fetch pool configuration from file store.');
    }

    /**
     * Create a new cache repository with the given implementation.
     *
     * From: Illuminate\Cache\CacheManager
     *
     * @param  \Illuminate\Cache\StoreInterface $store
     * @return \Illuminate\Cache\Repository
     */
    protected function repository(StoreInterface $store)
    {
        $repository = new Repository($store);

        if ($this->app->bound('Illuminate\Contracts\Events\Dispatcher')) {
            $repository->setEventDispatcher(
                $this->app['Illuminate\Contracts\Events\Dispatcher']
            );
        }

        return $repository;
    }

    /**
     * Get the cache prefix.
     *
     * From: Illuminate\Cache\CacheManager
     *
     * @param  array $config
     * @return string
     */
    protected function getPrefix(array $config)
    {
        return array_get($config, 'prefix') ?: $this->app['config']['cache.prefix'];
    }
}
