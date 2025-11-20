<?php

namespace App\Helpers;

trait HasActivityLink
{
    /**
     * Returns the correct route for a notification/activity based on the user type.
     *
     * @param  \Illuminate\Foundation\Auth\User  $notifiable
     * @param  string  $type  // e.g., 'student_added', 'document_uploaded', etc.
     * @param  mixed   $model // The model instance (Student, Document, Application)
     * @return string
     */
    public function getActivityLink($notifiable, string $type, $model): string
    {
        // Admin routes take priority if user is admin
        $isAdmin = $notifiable->is_admin ?? false;

        // Map of activity type => [agent route, admin route]
        $routes = [
            // STUDENTS
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

            // DOCUMENTS
            'document_uploaded' => [
                'agent' => fn($m) => route('agent.documents.index', ['student' => $m->id]),
                'admin' => fn($m) => route('admin.documents.index', ['student' => $m->id]),
            ],
            'document_deleted' => [
                'agent' => fn($m) => route('agent.documents.index', ['student' => $m->id]),
                'admin' => fn($m) => route('admin.documents.index', ['student' => $m->id]),
            ],

            // APPLICATIONS
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


            // USERS
            'user_registered' => [
                'agent' => fn($m) => '#', // not used for agents
                'admin' => fn($m) => route('admin.users.waiting'),
            ],
            'user_approved' => [
                'agent' => fn($m) => route('auth.login'),
                'admin' => fn($m) => route('auth.login'),
            ],
        ];

        if (!isset($routes[$type])) {
            // fallback route
            return '#';
        }

        return $isAdmin
            ? $routes[$type]['admin']($model)
            : $routes[$type]['agent']($model);
    }
}
