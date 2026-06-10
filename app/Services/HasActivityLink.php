<?php

namespace App\Services;

trait HasActivityLink
{
    public function getActivityLink($notifiable, string $type, $model): string
    {
        $isAdmin = $notifiable->is_admin ?? false;

        $routes = [
            'student_added' => [
                'agent' => fn($m) => route('agent.students.show', $m->id),
                'admin' => fn($m) => route('admin.students.show', $m->id),
            ],
            'student_deleted' => [
                'agent' => fn($m) => route('agent.students.index'),
                'admin' => fn($m) => route('admin.users.show', $m->id),
            ],
            'student_status_updated' => [
                'agent' => fn($m) => route('agent.students.show', $m->id),
                'admin' => fn($m) => route('admin.students.show', $m->id),
            ],
            'document_uploaded' => [
                'agent' => fn($m) => route('agent.documents.index', ['student' => $m->id]),
                'admin' => fn($m) => route('admin.documents.index', ['student' => $m->id]),
            ],
            'document_deleted' => [
                'agent' => fn($m) => route('agent.documents.index', ['student' => $m->id]),
                'admin' => fn($m) => route('admin.documents.index', ['student' => $m->id]),
            ],
            'application_submitted' => [
                'agent' => fn($m) => route('agent.applications.show', $m->id),
                'admin' => fn($m) => route('admin.applications.show', $m->id),
            ],
            'application_status_updated' => [
                'agent' => fn($m) => route('agent.applications.show', $m->id),
                'admin' => fn($m) => route('admin.applications.show', $m->id),
            ],
            'application_withdrawn' => [
                'agent' => fn($m) => route('agent.applications.index'),
                'admin' => fn($m) => route('admin.users.show', $m->id) . '/applications',
            ],
            'application_message_added' => [
                'agent' => fn($m) => route('agent.applications.show', $m->application_id) . '#comment-' . $m->id,
                'admin' => fn($m) => route('admin.applications.show', $m->application_id) . '#comment-' . $m->id,
            ],
            'user_registered' => [
                'agent' => fn($m) => '#',
                'admin' => fn($m) => route('admin.users.waiting'),
            ],
            'user_approved' => [
                'agent' => fn($m) => route('auth.login'),
                'admin' => fn($m) => route('auth.login'),
            ],
        ];

        if (!isset($routes[$type])) {
            return '#';
        }

        return $isAdmin
            ? $routes[$type]['admin']($model)
            : $routes[$type]['agent']($model);
    }
}
