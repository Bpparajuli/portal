<?php

namespace App\Providers;

use App\Contracts\FileUploadServiceInterface;
use App\Contracts\StudentServiceInterface;
use App\Models\Application;
use App\Observers\ApplicationObserver;
use App\Services\FileUploadService;
use App\Services\StudentService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(StudentServiceInterface::class, StudentService::class);
        $this->app->bind(FileUploadServiceInterface::class, FileUploadService::class);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Application::observe(ApplicationObserver::class);
    }
}
