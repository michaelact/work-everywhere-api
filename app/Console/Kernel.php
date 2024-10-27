<?php

namespace App\Console;

use App\Models\Task;
use App\Notifications\TaskDeadlineNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;

protected $commands = [
    Commands\NotifyTaskDeadlines::class,
];

protected function schedule(Schedule $schedule)
{
    $schedule->command('notify:task-deadlines')->daily();
}
