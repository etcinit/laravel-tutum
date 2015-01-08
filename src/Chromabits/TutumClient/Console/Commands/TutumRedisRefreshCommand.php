<?php

namespace Chromabits\TutumClient\Console\Commands;

use Chromabits\TutumClient\Client;
use Chromabits\TutumClient\Support\EnvUtils;
use Exception;
use Illuminate\Cache\StoreInterface;
use Illuminate\Console\Command;

/**
 * Class TutumRedisRefreshCommand
 *
 * @package Chromabits\TutumClient\Console\Commands
 */
class TutumRedisRefreshCommand extends Command
{
    /**
     * @var string
     */
    protected $name = 'tutum:redis:refresh';

    /**
     * @var string
     */
    protected $description = 'Refresh available Redis connections from the Tutum API';

    /**
     * Execute the command
     *
     * @throws Exception
     */
    public function handle()
    {
        $this->line('Discovering Redis links from Tutum API...');

        $links = $this->fetchLinks();

        /** @var StoreInterface $cache */
        $cache = $this->getLaravel()['cache']->store('tutumredisconfig');

        $cache->forever('redis_pool', $links);

        $this->line('Stored discovered links in cache');
    }

    /**
     * Get
     * @return \Chromabits\TutumClient\Entities\ContainerLink[]
     * @throws Exception
     */
    protected function fetchLinks()
    {
        $app = $this->getLaravel();

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
