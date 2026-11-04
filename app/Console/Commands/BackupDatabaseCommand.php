<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'kds:backup-db';
    protected $description = 'Cadangkan basis data ke storage/backups/ dan bersihkan cadangan lama';

    public function handle(): int
    {
        $backupDir = storage_path('backups');
        if (! File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $stamp = date('Y-m-d-Hi');
        $filename = "db-{$stamp}.sql.gz";
        $targetPath = "{$backupDir}/{$filename}";

        $connection = config('database.default');

        if ($connection === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            if ($dbPath === ':memory:') {
                $content = "-- SQLite in-memory dump\n";
                file_put_contents($targetPath, gzencode($content, 9));
                $this->info("Cadangan SQLite in-memory berhasil dibuat: {$targetPath}");
            } elseif (file_exists($dbPath)) {
                $content = file_get_contents($dbPath);
                file_put_contents($targetPath, gzencode($content, 9));
                $this->info("Cadangan SQLite berhasil dibuat: {$targetPath}");
            } else {
                $this->warn("Database SQLite tidak ditemukan di {$dbPath}");
            }
        } elseif ($connection === 'mysql') {
            $dbName = config('database.connections.mysql.database');
            $user = config('database.connections.mysql.username');
            $pass = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);

            $cmd = sprintf(
                'mysqldump --single-transaction --no-tablespaces -h %s -P %s -u %s %s %s | gzip > %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($user),
                $pass ? '-p' . escapeshellarg($pass) : '',
                escapeshellarg($dbName),
                escapeshellarg($targetPath)
            );

            $result = Process::run($cmd);
            if (! $result->successful() && ! file_exists($targetPath)) {
                $this->error("Gagal mencadangkan MySQL: " . $result->errorOutput());
                return self::FAILURE;
            }

            $this->info("Cadangan MySQL berhasil dibuat: {$targetPath}");
        }

        // Rotasi: Simpan 14 cadangan terbaru, hapus sisanya
        $backups = File::glob("{$backupDir}/db-*.sql.gz");
        if (count($backups) > 14) {
            // Urutkan berdasarkan waktu modifikasi DESC
            usort($backups, fn ($a, $b) => filemtime($b) <=> filemtime($a));
            $toDelete = array_slice($backups, 14);
            foreach ($toDelete as $oldFile) {
                File::delete($oldFile);
                $this->line("Menghapus cadangan kedaluwarsa: " . basename($oldFile));
            }
        }

        return self::SUCCESS;
    }
}
