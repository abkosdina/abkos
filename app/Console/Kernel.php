<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Modules\Workflow\Jobs\CheckExpiredStepsJob;
use Modules\Workflow\Jobs\SendWorkflowRemindersJob;
use Modules\Workflow\Jobs\ExecuteTimeoutActionsJob;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new CheckExpiredStepsJob())->everyFiveMinutes();
        $schedule->job(new SendWorkflowRemindersJob())->hourly();
        $schedule->job(new ExecuteTimeoutActionsJob())->everyTenMinutes();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
