<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Task;

class TaskAssigned extends Notification
{
    use Queueable;

    protected $task;
    protected $projectId;

    public function __construct(Task $task, $projectId)
    {
        $this->task = $task;
        $this->projectId = $projectId;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $frontendUrl = env('FRONTEND_URL') . "/projects/{$this->projectId}";

        return (new MailMessage)
                    ->subject('✨ Task Assigned Notification')
                    ->greeting('Hello!')
                    ->line("Good news! You've been assigned a new task. Here are the details:")
                    ->line('📌 **Task Title:** ' . $this->task->title)
                    ->line('📅 **Due Date:** ' . ($this->task->due_date ? $this->task->due_date->format('F j, Y') : 'No due date set'))
                    ->action('View Task', $frontendUrl) // Updated URL
                    ->line("We're excited to see what you'll accomplish with this task. Please reach out if you have any questions or need assistance.")
                    ->salutation('Warm regards,')
                    ->salutation('Working Everywhere Team');
    }
}
