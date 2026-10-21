<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    protected $signature = 'clinic:backup {--prune : Remove verified backups older than the retention period}';

    protected $description = 'Create and verify an encrypted database and uploaded-files backup';

    public function handle(DatabaseBackupService $backups): int
    {
        try {
            $path = $backups->create();
            $this->info('Verified encrypted backup saved: '.$path);
            if ($this->option('prune')) {
                foreach (File::glob(config('backups.path').'/*.backup') as $file) {
                    if (filemtime($file) < now()->subDays(config('backups.retention_days'))->timestamp) {
                        // Do not remove unknown or corrupt files automatically.
                        try {
                            $backups->inspect($file);
                            File::delete($file);
                        } catch (\Throwable) {
                        }
                    }
                }
            }

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            report($exception);
            $this->error('Backup failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
