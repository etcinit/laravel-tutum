<?php

namespace Chromabits\TutumClient\Console\Commands;

use Chromabits\TutumClient\Entities\ContainerLink;
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

        $links = $finder->refresh();

        if (is_array($links)) {
            /** @var ContainerLink $link */
            foreach ($links as $link) {
                $urls = $link->getEndpointsAsUrls();

                foreach ($urls as $url)
                {
                    $this->line('Found link: ' . $url->getHost() . ':' . $url->getPort());
                }
            }
        }

        $this->line('Stored discovered links in cache');
    }
}
