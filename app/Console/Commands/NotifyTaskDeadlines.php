<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Notifications\TaskDeadlineNotification;
use Illuminate\Support\Facades\Notification;

class NotifyTaskDeadlines extends Command
{
    protected $signature = 'notify:task-deadlines';
    protected $description = 'Send notifications to users about tasks approaching their deadlines';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info("Starting notification process for tasks approaching deadlines...");

        $projects = Project::with(['tasks' => function ($query) {
            $query->where('due_date', '<=', now()->addDays(3))
                  ->where('status', '!=', 'completed');
        }, 'users'])->get();

        $notificationSent = false;
        foreach ($projects as $project) {
            foreach ($project->tasks as $task) {
                Notification::sendNow($project->users, new TaskDeadlineNotification($task));
                $notificationSent = true;
            }
        }

        if ($notificationSent) {
            $this->info("Notifications sent for tasks approaching deadlines.");
        } else {
            $this->info("No tasks are approaching deadlines in the specified time frame.");
        }
    }
}
