<?php

namespace Klytron\LaravelScheduleTelegramOutput;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Container\Container;

class TelegramSchedule extends Schedule
{
    /**
     * Create a new schedule instance.
     */
    public function __construct(Container $container = null)
    {
        parent::__construct($container);
    }

    /**
     * Add a new command event to the schedule.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return \Klytron\LaravelScheduleTelegramOutput\TelegramEvent
     */
    public function exec($command, array $parameters = [])
    {
        if (count($parameters)) {
            $command .= ' '.$this->compileParameters($parameters);
        }

        $this->events[] = $event = new TelegramEvent($this->eventMutex, $command, $this->timezone);

        return $event;
    }

    /**
     * Add a new command event to the schedule.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return \Klytron\LaravelScheduleTelegramOutput\TelegramEvent
     */
    public function command($command, array $parameters = [])
    {
        if (count($parameters)) {
            $command .= ' '.$this->compileParameters($parameters);
        }

        $this->events[] = $event = new TelegramEvent($this->eventMutex, $command, $this->timezone);

        return $event;
    }
}
