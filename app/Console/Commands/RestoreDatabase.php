<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class RestoreDatabase extends Command
{
    protected $signature = 'clinic:restore {file : Absolute path to an encrypted backup} {--force : Replace data; requires maintenance mode}';

    protected $description = 'Verify a backup, or restore it with --force while the application is down';

    public function handle(DatabaseBackupService $backups): int
    {
        try {
            $payload = $backups->inspect($this->argument('file'));
            $this->info('Backup verified: '.count($payload['tables']).' tables, created '.$payload['created_at']);
            if (! $this->option('force')) {
                $this->line('Verification only. To restore: php artisan down, then rerun with --force, then php artisan up. Keep the original APP_KEY to decrypt backups. Stop queue workers during restore.');

                return self::SUCCESS;
            }
            if (! app()->isDownForMaintenance()) {
                $this->error('Run php artisan down and stop queue workers before restoring.');

                return self::FAILURE;
            }
            $safety = $backups->create();
            $backups->restore($this->argument('file'));
            $this->info('Restore complete. Pre-restore safety backup: '.$safety);

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            report($exception);
            $this->error('Restore failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
