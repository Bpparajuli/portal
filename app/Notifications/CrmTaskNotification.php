<?php
// app/Notifications/CrmTaskNotification.php

namespace App\Notifications;

use App\Models\CrmTasks;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Auth;

class CrmTaskNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $type;
    protected $triggeredBy;

    public function __construct(CrmTasks $task, $type, User $triggeredBy = null)
    {
        $this->task = $task;
        $this->type = $type;
        $this->triggeredBy = $triggeredBy ?? (Auth::check() ? Auth::user() : null);
    }

    public function via($notifiable)
    {
        // Check user preferences
        $preferences = $notifiable->crm_notification_preferences ?? [];
        $shouldSend = true;

        switch ($this->type) {
            case 'assigned':
                $shouldSend = $preferences['task_assigned'] ?? true;
                break;
            case 'due_today':
                $shouldSend = $preferences['task_due_today'] ?? true;
                break;
            case 'upcoming':
                $shouldSend = $preferences['task_upcoming'] ?? true;
                break;
            case 'overdue':
                $shouldSend = $preferences['task_overdue'] ?? true;
                break;
        }

        if (!$shouldSend) {
            return [];
        }

        return ['database'];
    }

    public function toMail($notifiable)
    {
        $messages = [
            'assigned' => "New Task Assigned",
            'due_today' => "Task Due Today",
            'upcoming' => "Upcoming Task Tomorrow",
            'overdue' => "Task Overdue",
        ];

        $descriptions = [
            'assigned' => "You have been assigned a new task.",
            'due_today' => "Your task is due today.",
            'upcoming' => "Your task is due tomorrow.",
            'overdue' => "Your task is overdue. Please take action.",
        ];

        // Get student name safely
        $studentName = '';
        if ($this->task->student) {
            $studentName = $this->task->student->full_name ?? ($this->task->student->first_name . ' ' . $this->task->student->last_name);
        }

        return (new MailMessage)
            ->subject($messages[$this->type] ?? 'Task Notification')
            ->greeting("Hello {$notifiable->name},")
            ->line($descriptions[$this->type] ?? 'Task update')
            ->line("**Task:** {$this->task->subject}")
            ->when($studentName, fn($mail) => $mail->line("**Student:** {$studentName}"))
            ->line("**Due Date:** " . ($this->task->scheduled_for ? $this->task->scheduled_for->format('F j, Y') : 'Not set'))
            ->action('View Task', route('crm.student.show', $this->task->student_id) . "#task-{$this->task->id}")
            ->line('Please take necessary action on this task.');
    }

    public function toArray($notifiable)
    {
        // Check if a similar notification was sent in the last hour
        $existingNotification = $notifiable->notifications()
            ->where('type', self::class)
            ->where('data->task_id', $this->task->id)
            ->where('data->subtype', $this->type)
            ->where('created_at', '>', now()->subHour())
            ->exists();

        // Prevent duplicate notifications within 1 hour
        if ($existingNotification && in_array($this->type, ['due_today', 'upcoming'])) {
            return [];
        }

        $messages = [
            'assigned' => "📋 New task assigned: {$this->task->subject}",
            'due_today' => "⚠️ Task due today: {$this->task->subject}",
            'upcoming' => "🔔 Upcoming task tomorrow: {$this->task->subject}",
            'overdue' => "❌ Task is OVERDUE: {$this->task->subject}",
        ];

        // Build the correct URL
        $url = route('crm.student.show', $this->task->student_id) . "#task-{$this->task->id}";

        // Get student name safely
        $studentName = '';
        if ($this->task->student) {
            $studentName = $this->task->student->full_name ?? ($this->task->student->first_name . ' ' . $this->task->student->last_name);
        }

        return [
            'type' => 'crm_task',
            'subtype' => $this->type,
            'task_id' => $this->task->id,
            'task_title' => $this->task->subject,
            'student_id' => $this->task->student_id,
            'student_name' => $studentName,
            'due_date' => $this->task->scheduled_for ? $this->task->scheduled_for->format('Y-m-d') : null,
            'message' => $messages[$this->type] ?? "Task update: {$this->task->subject}",
            'link' => $url,  // Important: Use 'link' key
            'url' => $url,   // Also keep 'url' for compatibility
            'triggered_by' => $this->triggeredBy ? $this->triggeredBy->name : null,
        ];
    }
}
