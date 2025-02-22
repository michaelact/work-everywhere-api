<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Task;

class TaskUpdated extends Notification
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
                    ->subject('✨ Task Update Notification')
                    ->greeting('Hello!')
                    ->line('We wanted to let you know that a task assigned to you has been updated with the following details:')
                    ->line('📌 **Task Title:** ' . $this->task->title)
                    ->line('📈 **Current Status:** ' . ucfirst($this->task->status))
                    ->line('📅 **Due Date:** ' . ($this->task->due_date ? $this->task->due_date->format('F j, Y') : 'No due date available'))
                    ->action('View Updated Task', $frontendUrl) // Use the constructed URL
                    ->line("We're here to help if you have any questions or need further support. Thank you for your dedication and effort!")
                    ->salutation('Warm regards,')
                    ->salutation('Working Everywhere Team');
    }
}
