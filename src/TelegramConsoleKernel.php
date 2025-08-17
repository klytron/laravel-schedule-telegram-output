<?php

namespace Klytron\LaravelScheduleTelegramOutput;

trait TelegramConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function defineConsoleSchedule($schedule)
    {
        // The schedule is already injected, so we can use it directly
        // This trait is mainly for documentation purposes now
        // The actual functionality is handled by the TelegramSchedule class
    }

    /**
     * Get the schedule instance.
     *
     * @return \Klytron\LaravelScheduleTelegramOutput\TelegramSchedule
     */
    protected function schedule($schedule)
    {
        // This method can be overridden in the Console Kernel
        // to add custom scheduling logic
    }
}