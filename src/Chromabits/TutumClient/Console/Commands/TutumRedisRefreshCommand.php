<?php

namespace Chromabits\TutumClient\Console\Commands;

use Exception;
use Illuminate\Console\Command;

/**
 * Class TutumRedisRefreshCommand
 *
 * @package Chromabits\TutumClient\Console\Commands
 */
class TutumRedisRefreshCommand extends Command
{
    /**
     * Name of the command
     *
     * @var string
     */
    protected $name = 'tutum:redis:refresh';

    /**
     * Description of the command
     *
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
