<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use ZipArchive;

class BackupController extends Controller
{
    public function __construct()
    {
        // Only authenticated users can access
        $this->middleware('auth');
    }

    /**
     * Backup Laravel files if changed
     * Only admin with ID = 2 can trigger
     */
    public function backupFilesIfChanged()
    {
        $user = Auth::user();

        // Restrict access to admin ID = 2
        if ($user->id != 2) {
            abort(403, 'Unauthorized action.');
        }

        $hashFile = storage_path('app/backup_files_hash.txt');

        // Calculate current folder hash
        $currentHash = $this->folderHash(base_path());

        // Check if previous hash exists
        if (file_exists($hashFile)) {
            $oldHash = file_get_contents($hashFile);
            if ($oldHash === $currentHash) {
                return response()->json(['message' => 'No changes detected, backup not needed.']);
            }
        }

        // Save new hash
        file_put_contents($hashFile, $currentHash);

        // Create ZIP backup
        $zip = new ZipArchive;
        $zipFile = storage_path('app/project_backup_' . date('Y-m-d_His') . '.zip');

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $this->zipFolder(base_path(), $zip);
            $zip->close();
        }

        return response()->download($zipFile);
    }

    /**
     * Recursively calculate hash of all files in folder
     */
    private function folderHash($folder)
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder));
        $hash = '';

        foreach ($files as $file) {
            if ($file->isFile()) {
                $hash .= md5_file($file->getRealPath());
            }
        }

        return md5($hash);
    }

    /**
     * Recursively add folder contents to ZipArchive
     */
    private function zipFolder($folder, &$zip, $zipPath = '')
    {
        foreach (scandir($folder) as $file) {
            if ($file == '.' || $file == '..') continue;

            $path = "$folder/$file";
            $localPath = $zipPath . $file;

            if (is_dir($path)) {
                $zip->addEmptyDir($localPath);
                $this->zipFolder($path, $zip, $localPath . '/');
            } else {
                $zip->addFile($path, $localPath);
            }
        }
    }
}
