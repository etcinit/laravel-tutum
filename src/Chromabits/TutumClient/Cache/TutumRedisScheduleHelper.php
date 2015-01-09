<?php

namespace Chromabits\TutumClient\Cache;

use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Config\Repository;

/**
 * Class TutumRedisScheduleHelper
 *
 * Helper class for defining the schedule for refreshing the redis server pool
 * from Tutum's API
 *
 * @package Chromabits\TutumClient\Cache
 */
class TutumRedisScheduleHelper
{
    /**
     * @var Schedule
     */
    protected $schedule;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * Construct an instance of a TutumRedisScheduleHelper
     *
     * @param Schedule $schedule
     * @param Repository $config
     */
    public function __construct(Schedule $schedule, Repository $config)
    {
        $this->schedule = $schedule;

        $this->config = $config;
    }

    /**
     * Setup the schedule for refreshing redis servers
     *
     * @throws Exception
     */
    public function setup()
    {
        $frequency = $this->config->get('tutum.redis.frequency', 'ten');

        switch($frequency) {
            case 'five':
                $this->schedule->command('tutum:redis:refresh')->everyFiveMinutes();
                break;
            case 'ten':
                $this->schedule->command('tutum:redis:refresh')->everyTenMinutes();
                break;
            case 'thirty':
                $this->schedule->command('tutum:redis:refresh')->everyThirtyMinutes();
                break;
            default:
                throw new Exception('Unknown frequency');
        }
    }
}
