<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use ZipArchive;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $backups = collect();
        $backupDir = storage_path('app/backups');
        if (is_dir($backupDir)) {
            $files = glob($backupDir . '/*.sql');
            foreach ($files as $file) {
                $backups->push([
                    'filename' => basename($file),
                    'size' => round(filesize($file) / 1024 / 1024, 2),
                    'date' => date('Y-m-d H:i:s', filemtime($file)),
                    'path' => $file,
                ]);
            }
            $backups = $backups->sortByDesc('date')->values();
        }
        return view('admin.backups', compact('backups'));
    }

    public function create()
    {
        $dbName = env('DB_DATABASE', 'bpparaju_portaldb');
        $dbUser = env('DB_USERNAME', 'root');
        $dbPass = env('DB_PASSWORD', '');
        $dbHost = env('DB_HOST', '127.0.0.1');
        // Try to find mysqldump in common Laragon path
        $mysqlPath = 'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe';
        if (!file_exists($mysqlPath)) {
            // Fallback: try alternative Laragon mysql versions
            $laragonMysqlBase = 'C:\\laragon\\bin\\mysql\\';
            if (is_dir($laragonMysqlBase)) {
                $versions = scandir($laragonMysqlBase);
                foreach ($versions as $version) {
                    if ($version === '.' || $version === '..') continue;
                    $candidate = $laragonMysqlBase . $version . '\\bin\\mysqldump.exe';
                    if (file_exists($candidate)) {
                        $mysqlPath = $candidate;
                        break;
                    }
                }
            }
        }
        if (!file_exists($mysqlPath)) {
            $mysqlPath = 'mysqldump';
        }

        $filename = 'backup-' . $dbName . '-' . date('Y-m-d_His') . '.sql';
        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        $filepath = $backupDir . '/' . $filename;

        $command = "\"{$mysqlPath}\" --host={$dbHost} --user={$dbUser} --password={$dbPass} {$dbName} > \"{$filepath}\" 2>&1";
        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($filepath) || filesize($filepath) === 0) {
            // Fallback: manual PHP-based backup
            try {
                $tables = DB::select('SHOW TABLES');
                $tableKey = 'Tables_in_' . $dbName;
                $sql = "-- Backup created: " . date('Y-m-d H:i:s') . "\n\n";
                foreach ($tables as $table) {
                    $tableName = $table->$tableKey;
                    $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                    $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
                    $rows = DB::table($tableName)->get();
                    if ($rows->count()) {
                        $columns = implode('`, `', array_keys((array)$rows[0]));
                        foreach ($rows->chunk(100) as $chunk) {
                            $values = [];
                            foreach ($chunk as $row) {
                                $vals = [];
                                foreach ((array)$row as $val) {
                                    $vals[] = is_null($val) ? 'NULL' : "'" . str_replace("'", "\\'", $val) . "'";
                                }
                                $sql .= "INSERT INTO `{$tableName}` (`{$columns}`) VALUES (" . implode(', ', $vals) . ");\n";
                            }
                        }
                    }
                    $sql .= "\n";
                }
                file_put_contents($filepath, $sql);
            } catch (\Exception $e) {
                return redirect()->route('admin.backup.index')->with('error', 'Backup failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.backup.index')->with('success', 'Backup created successfully: ' . $filename);
    }

    public function downloadFile($encodedFilename)
    {
        $filename = base64_decode($encodedFilename);
        $filepath = storage_path('app/backups/' . basename($filename));
        if (!file_exists($filepath)) {
            return back()->with('error', 'Backup file not found.');
        }
        return response()->download($filepath);
    }

    public function downloadSql()
    {
        $dbName = env('DB_DATABASE', 'bpparaju_portaldb');
        $dbUser = env('DB_USERNAME', 'root');
        $dbPass = env('DB_PASSWORD', '');
        $dbHost = env('DB_HOST', '127.0.0.1');
        // Try to find mysqldump in common Laragon path
        $mysqlPath = 'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe';
        if (!file_exists($mysqlPath)) {
            $mysqlPath = 'mysqldump';
        }

        $filename = 'backup-' . $dbName . '-' . date('Y-m-d_His') . '.sql';
        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        $filepath = $backupDir . '/' . $filename;

        $command = "\"{$mysqlPath}\" --host={$dbHost} --user={$dbUser} --password={$dbPass} {$dbName} > \"{$filepath}\" 2>&1";
        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($filepath) || filesize($filepath) === 0) {
            try {
                $tables = DB::select('SHOW TABLES');
                $tableKey = 'Tables_in_' . $dbName;
                $sql = "-- Backup created: " . date('Y-m-d H:i:s') . "\n\n";
                foreach ($tables as $table) {
                    $tableName = $table->$tableKey;
                    $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                    $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
                    $rows = DB::table($tableName)->get();
                    if ($rows->count()) {
                        $columns = implode('`, `', array_keys((array)$rows[0]));
                        foreach ($rows->chunk(100) as $chunk) {
                            $values = [];
                            foreach ($chunk as $row) {
                                $vals = [];
                                foreach ((array)$row as $val) {
                                    $vals[] = is_null($val) ? 'NULL' : "'" . str_replace("'", "\\'", $val) . "'";
                                }
                                $sql .= "INSERT INTO `{$tableName}` (`{$columns}`) VALUES (" . implode(', ', $vals) . ");\n";
                            }
                        }
                    }
                    $sql .= "\n";
                }
                file_put_contents($filepath, $sql);
            } catch (\Exception $e) {
                return back()->with('error', 'Backup failed: ' . $e->getMessage());
            }
        }

        return response()->download($filepath)->deleteFileAfterSend(false);
    }

    public function downloadZip()
    {
        $excludeDirs = ['vendor', 'node_modules', '.git', 'storage', 'bootstrap/cache'];
        $zip = new ZipArchive;
        $zipFile = storage_path('app/backups/project_backup_' . date('Y-m-d_His') . '.zip');

        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $this->zipFolder(base_path(), $zip, '', $excludeDirs);
            $zip->close();
        }

        return response()->download($zipFile)->deleteFileAfterSend(false);
    }

    public function delete($filename)
    {
        $filepath = storage_path('app/backups/' . basename($filename));
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        return back()->with('success', 'Backup deleted.');
    }

    private function zipFolder($folder, &$zip, $zipPath = '', $excludeDirs = [])
    {
        foreach (scandir($folder) as $file) {
            if ($file == '.' || $file == '..') continue;
            if (in_array($file, $excludeDirs)) continue;
            $path = "$folder/$file";
            $localPath = $zipPath . $file;
            if (is_dir($path)) {
                $zip->addEmptyDir($localPath);
                $this->zipFolder($path, $zip, $localPath . '/', $excludeDirs);
            } else {
                $zip->addFile($path, $localPath);
            }
        }
    }
}
