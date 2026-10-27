<?php

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Local\LocalFilesystemAdapter as FlysystemLocalAdapter;
use League\Flysystem\PathPrefixing\PathPrefixedAdapter;
use League\Flysystem\ReadOnly\ReadOnlyFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;
use Illuminate\Filesystem\LocalFilesystemAdapter as LaravelLocalAdapter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Route all outgoing mail through the DB-configured SMTP server.
        try {
            \App\Services\MailConfigService::configure();
        } catch (\Throwable $e) {
            // Never let mail misconfiguration break the app.
        }

        // Record who soft-deleted a record (for the Recycle Bin).
        // Guarded so it is a no-op until the deleted_by column is migrated.
        \Illuminate\Support\Facades\Event::listen('eloquent.deleting: *', function ($eventName, array $payload) {
            $model = $payload[0] ?? null;
            if (! is_object($model)) {
                return;
            }

            if (! in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model), true)) {
                return;
            }

            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                return;
            }

            if (! \Illuminate\Support\Facades\Auth::check()) {
                return;
            }

            try {
                static $columnCache = [];
                $table = $model->getTable();

                if (! array_key_exists($table, $columnCache)) {
                    $columnCache[$table] = \Illuminate\Support\Facades\Schema::hasColumn($table, 'deleted_by');
                }

                if (! $columnCache[$table]) {
                    return;
                }

                $model->deleted_by = \Illuminate\Support\Facades\Auth::id();
                $model->saveQuietly();
            } catch (\Throwable $e) {
                // Never block a delete because of tracking.
            }
        });

        Storage::extend('local', function ($app, $config) {
            $visibility = PortableVisibilityConverter::fromArray(
                $config['permissions'] ?? [],
                $config['directory_visibility'] ?? $config['visibility'] ?? Visibility::PRIVATE,
            );

            $links = ($config['links'] ?? null) === 'skip'
                ? FlysystemLocalAdapter::SKIP_LINKS
                : FlysystemLocalAdapter::DISALLOW_LINKS;

            $adapter = new FlysystemLocalAdapter(
                $config['root'],
                $visibility,
                $config['lock'] ?? LOCK_EX,
                $links,
                new ExtensionMimeTypeDetector,
            );

            if (($config['read-only'] ?? false) === true) {
                $adapter = new ReadOnlyFilesystemAdapter($adapter);
            }

            if (! empty($config['prefix'])) {
                $adapter = new PathPrefixedAdapter($adapter, $config['prefix']);
            }

            if (str_contains($config['endpoint'] ?? '', 'r2.cloudflarestorage.com')) {
                $config['retain_visibility'] = false;
            }

            $flysystem = new Flysystem($adapter, Arr::only($config, [
                'directory_visibility',
                'disable_asserts',
                'retain_visibility',
                'temporary_url',
                'url',
                'visibility',
            ]));

            return (new LaravelLocalAdapter($flysystem, $adapter, $config));
        });
    }
}
