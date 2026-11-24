<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * One generic database notification used for the unified notification feed.
 * `data.category` powers the bell tabs (Students / Applications / Users / ...).
 */
class PortalActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $event,
        public string $category,
        public string $title,
        public string $message,
        public ?string $link = null,
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => $this->event,
            'event' => $this->event,
            'category' => $this->category,
            'title' => $this->title,
            'message' => $this->message,
            'link' => $this->link,
        ];
    }
}
