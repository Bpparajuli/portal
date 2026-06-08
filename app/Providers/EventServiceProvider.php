<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\StudentCreated::class => [
            \App\Listeners\LogStudentActivity::class,
        ],
        \App\Events\ApplicationSubmitted::class => [
            \App\Listeners\LogApplicationActivity::class,
        ],
        \App\Events\DocumentUploaded::class => [
            \App\Listeners\LogDocumentActivity::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
