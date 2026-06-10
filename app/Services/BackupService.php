<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    private string $disk = 'public';

    /**
     * List existing backup files on the public disk.
     *
     * @return array  Array of ['filename' => ..., 'size' => ..., 'last_modified' => ...]
     */
    public function listBackups(): array
    {
        $files = Storage::disk($this->disk)->files('backups');
        $backups = [];
        foreach ($files as $file) {
            $backups[] = [
                'filename'      => basename($file),
                'size'          => Storage::disk($this->disk)->size($file),
                'last_modified' => Storage::disk($this->disk)->lastModified($file),
            ];
        }
        return $backups;
    }

    /**
     * Create a SQL dump backup file.
     *
     * Attempts to use mysqldump via shell; falls back to a PHP-based export.
     *
     * @return string  The filename of the created backup.
     */
    public function createBackup(): string
    {
        $filename = 'backups/backup-' . date('Y-m-d_H-i-s') . '.sql';
        $fullPath = Storage::disk($this->disk)->path($filename);

        try {
            $db = config('database.connections.mysql');
            $cmd = sprintf(
                '"%s" --host=%s --user=%s --password=%s %s > %s',
                env('MYSQLDUMP_PATH', 'mysqldump'),
                $db['host'],
                $db['username'],
                $db['password'],
                $db['database'],
                $fullPath
            );
            exec($cmd, $output, $exitCode);

            if ($exitCode !== 0) {
                throw new \RuntimeException('mysqldump failed with exit code ' . $exitCode);
            }
        } catch (\Exception $e) {
            Log::warning('mysqldump failed, falling back to PHP export: ' . $e->getMessage());
            $this->phpExport($fullPath);
        }

        return basename($filename);
    }

    /**
     * PHP-based database export fallback.
     */
    private function phpExport(string $path): void
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        $key = 'Tables_in_' . $dbName;
        $output = '';

        foreach ($tables as $table) {
            $tableName = $table->$key;
            $output .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $create = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $output .= $create[0]->{'Create Table'} . ";\n\n";

            $rows = DB::table($tableName)->get();
            foreach ($rows as $row) {
                $columns = array_map(fn($col) => "`{$col}`", array_keys((array) $row));
                $values = array_map(fn($val) => is_null($val) ? 'NULL' : "'" . addslashes($val) . "'", (array) $row);
                $output .= "INSERT INTO `{$tableName}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
            $output .= "\n";
        }

        file_put_contents($path, $output);
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup(string $filename): void
    {
        $path = 'backups/' . $filename;
        if (Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
    }

    /**
     * Get the full path to a backup file for download.
     */
    public function getBackupPath(string $filename): string
    {
        return Storage::disk($this->disk)->path('backups/' . $filename);
    }
}
