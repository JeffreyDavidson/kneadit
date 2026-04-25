<?php

namespace App\Http\Controllers\Central;

use App\Filament\Central\Pages\Backups;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class BackupDownloadController extends Controller
{
    #[Authorize('platform-admin')]
    public function __invoke(string $name): BinaryFileResponse
    {
        abort_unless(Backups::isSafeBackupName($name), 404);

        $folder = Backups::backupDirectory() . '/' . $name;
        abort_unless(is_dir($folder), 404, 'Backup not found.');

        $zipPath = sys_get_temp_dir() . '/kneadit-backup-' . $name . '.zip';

        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE);

        foreach (File::files($folder) as $file) {
            $zip->addFile($file->getPathname(), $file->getFilename());
        }

        $zip->close();

        return response()->download($zipPath, "kneadit-backup-{$name}.zip")->deleteFileAfterSend();
    }
}
