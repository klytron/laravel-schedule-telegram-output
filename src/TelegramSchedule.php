<?php

namespace Klytron\LaravelScheduleTelegramOutput;

use Illuminate\Console\Scheduling\Schedule;

/**
 * @deprecated Since v1.3.0, to be removed in v2.0.0. Use the native `->sendOutputToTelegram()` macro on Laravel's Schedule/Event instead.
 */
class TelegramSchedule extends Schedule
{
    /**
     * Create a new schedule instance.
     *
     * @param \DateTimeZone|string|null $timezone
     */
    public function __construct($timezone = null)
    {
        parent::__construct($timezone);
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
