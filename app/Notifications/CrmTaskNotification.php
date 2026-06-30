<?php

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
        if (!$this->shouldSendNotification($notifiable)) {
            return [];
        }
        return ['database'];
    }

    /**
     * Laravel 12 calls this from NotificationSender — must be public.
     */
    public function shouldSend($notifiable, $channel): bool
    {
        return $this->shouldSendNotification($notifiable);
    }

    private function shouldSendNotification($notifiable)
    {
        $preferences = $notifiable->crm_notification_preferences ?? [];

        switch ($this->type) {
            case 'assigned':
                if (!($preferences['task_assigned'] ?? true)) return false;
                break;
            case 'due_today':
                if (!($preferences['task_due_today'] ?? true)) return false;
                break;
            case 'overdue':
                if (!($preferences['task_overdue'] ?? true)) return false;
                break;
        }

        // assigned: always send (only dispatched once when task is created)
        if ($this->type === 'assigned') return true;

        // due_today: once per day
        if ($this->type === 'due_today') {
            $exists = $notifiable->notifications()
                ->where('type', self::class)
                ->where('data->task_id', $this->task->id)
                ->where('data->subtype', 'due_today')
                ->where('created_at', '>', now()->subDay())
                ->exists();
            if ($exists) return false;
        }

        // overdue: once ever (never repeat)
        if ($this->type === 'overdue') {
            $exists = $notifiable->notifications()
                ->where('type', self::class)
                ->where('data->task_id', $this->task->id)
                ->where('data->subtype', 'overdue')
                ->exists();
            if ($exists) return false;
        }

        return true;
    }

    public function toMail($notifiable)
    {
        $messages = [
            'assigned' => 'New Task Assigned',
            'due_today' => 'Task Due Today',
            'overdue' => 'Task Overdue',
        ];

        $descriptions = [
            'assigned' => 'You have been assigned a new task.',
            'due_today' => 'Your task is due today.',
            'overdue' => 'Your task is overdue.',
        ];

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
            ->line('**Due Date:** ' . ($this->task->scheduled_for ? $this->task->scheduled_for->format('F j, Y') : 'Not set'))
            ->action('View Task', route('crm.student.show', $this->task->student_id) . "#task-{$this->task->id}")
            ->line('Please take necessary action on this task.');
    }

    public function toArray($notifiable)
    {
        $studentName = '';
        if ($this->task->student) {
            $studentName = $this->task->student->full_name ?? ($this->task->student->first_name . ' ' . $this->task->student->last_name);
        }
        $studentLabel = $studentName ? " ({$studentName})" : '';

        $messages = [
            'assigned' => "New task assigned{$studentLabel}: {$this->task->subject}",
            'due_today' => "Task due today{$studentLabel}: {$this->task->subject}",
            'overdue' => "Task OVERDUE{$studentLabel}: {$this->task->subject}",
        ];

        $url = route('crm.student.show', $this->task->student_id) . "#task-{$this->task->id}";

        return [
            'type' => 'crm_task',
            'subtype' => $this->type,
            'task_id' => $this->task->id,
            'task_title' => $this->task->subject,
            'student_id' => $this->task->student_id,
            'student_name' => $studentName,
            'due_date' => $this->task->scheduled_for ? $this->task->scheduled_for->format('Y-m-d') : null,
            'message' => $messages[$this->type] ?? "Task update{$studentLabel}: {$this->task->subject}",
            'link' => $url,
            'url' => $url,
            'triggered_by' => $this->triggeredBy ? $this->triggeredBy->name : null,
        ];
    }
}
