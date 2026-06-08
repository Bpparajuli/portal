<?php

namespace App\Providers;

use App\Contracts\FileUploadServiceInterface;
use App\Contracts\StudentServiceInterface;
use App\Services\FileUploadService;
use App\Services\StudentService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(StudentServiceInterface::class, StudentService::class);
        $this->app->bind(FileUploadServiceInterface::class, FileUploadService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive(); // or useBootstrap() for Bootstrap 4
    }
}
