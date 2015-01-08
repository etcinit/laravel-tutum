<?php

namespace Chromabits\TutumClient\Console\Commands;

use Chromabits\TutumClient\Cache\TutumRedisPoolFinder;
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

        $finder = $this->getLaravel()->make('Chromabits\TutumClient\Cache\TutumRedisPoolFinder');

        $finder->refresh();

        $this->line('Stored discovered links in cache');
    }
}
