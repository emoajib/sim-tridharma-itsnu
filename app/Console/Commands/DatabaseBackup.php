<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup
        {--disk=s3 : Storage disk to upload backup}
        {--keep=7 : Number of daily backups to keep}
        {--no-upload : Skip upload, keep local only}';

    protected $description = 'Backup PostgreSQL database via pg_dump';

    public function handle(): int
    {
        $disk = $this->option('disk');
        $keep = (int) $this->option('keep');
        $noUpload = (bool) $this->option('no-upload');

        $dbName = config('database.connections.pgsql.database');
        $dbUser = config('database.connections.pgsql.username');
        $dbHost = config('database.connections.pgsql.host');
        $dbPort = config('database.connections.pgsql.port');

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$dbName}_{$timestamp}.sql.gz";
        $localPath = storage_path("app/backups/{$filename}");

        $this->info("Starting database backup: {$dbName}");
        $this->line("Host: {$dbHost}:{$dbPort}, User: {$dbUser}");

        $this->line('Running pg_dump...');
        $start = microtime(true);
        
        $password = config('database.connections.pgsql.password');
        $tempPasswordFile = temp_path('pgpass_' . md5($password) . '.txt');
        file_put_contents($tempPasswordFile, "*:*:*:*:{$password}");
        chmod($tempPasswordFile, 0600);
        
        $command = sprintf(
            'export PGPASSWORD="%s" && pg_dump -h %s -p %s -U %s -d %s --no-owner --no-acl | gzip > %s',
            escapeshellarg($dbHost),
            escapeshellarg((string) $dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbName),
            escapeshellarg($localPath)
        );
        
        $envCommand = sprintf('cat %s | %s', escapeshellarg($tempPasswordFile), $command);
        exec($envCommand, $output, $exitCode);
        
        unlink($tempPasswordFile);
        
        $elapsed = round(microtime(true) - $start, 2);

        if ($exitCode !== 0) {
            $this->error("Backup failed (exit code: {$exitCode})");
            return Command::FAILURE;
        }

        $size = round(filesize($localPath) / 1024 / 1024, 2);
        $this->info("Backup completed: {$size}MB in {$elapsed}s");
        $this->line("Local: {$localPath}");

        if (! $noUpload && $disk !== 'local') {
            try {
                $remotePath = "backups/{$filename}";
                Storage::disk($disk)->put($remotePath, file_get_contents($localPath));
                $this->info("Uploaded to {$disk}://{$remotePath}");
            } catch (\Exception $e) {
                $this->warn("Upload to {$disk} failed: {$e->getMessage()}. Backup kept locally.");
            }
        }

        if ($keep > 0) {
            $this->cleanOldBackups($keep, $disk, $noUpload);
        }

        return Command::SUCCESS;
    }

    protected function cleanOldBackups(int $keep, string $disk, bool $noUpload): void
    {
        $files = collect(Storage::disk('local')->files('backups'))
            ->filter(fn ($f) => Str::startsWith($f, 'backup_'))
            ->sort();

        $toDelete = $files->slice(0, max(0, $files->count() - $keep));
        foreach ($toDelete as $file) {
            Storage::disk('local')->delete($file);
            $this->line("Cleaned local: {$file}");
        }

        if (! $noUpload && $disk !== 'local') {
            try {
                $remoteFiles = collect(Storage::disk($disk)->files('backups'))
                    ->filter(fn ($f) => Str::startsWith($f, 'backup_'))
                    ->sort();

                $toDeleteRemote = $remoteFiles->slice(0, max(0, $remoteFiles->count() - $keep));
                foreach ($toDeleteRemote as $file) {
                    Storage::disk($disk)->delete($file);
                    $this->line("Cleaned remote: {$file}");
                }
            } catch (\Exception $e) {
                $this->warn("Remote cleanup failed: {$e->getMessage()}");
            }
        }
    }
}
