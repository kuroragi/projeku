<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class MonitorStorageCommand extends Command
{
    protected $signature = 'kds:monitor-storage';
    protected $description = 'Pantau penggunaan ruang disk dan jumlah berkas di private storage';

    public function handle(): int
    {
        $privatePath = storage_path('app/private');
        $filesCount = 0;
        $totalBytes = 0;

        if (File::isDirectory($privatePath)) {
            $allFiles = File::allFiles($privatePath);
            $filesCount = count($allFiles);
            foreach ($allFiles as $file) {
                $totalBytes += $file->getSize();
            }
        }

        $freeBytes = disk_free_space(base_path());
        $totalDiskBytes = disk_total_space(base_path());

        $mbUsed = round($totalBytes / (1024 * 1024), 2);
        $gbFree = round($freeBytes / (1024 * 1024 * 1024), 2);
        $gbTotal = round($totalDiskBytes / (1024 * 1024 * 1024), 2);

        $this->info("Jumlah berkas: {$filesCount} berkas ({$mbUsed} MB)");
        $this->info("Kapasitas disk: Tersisa {$gbFree} GB dari total {$gbTotal} GB");

        // Peringatan jika sisa kapasitas di bawah 1 GB atau jumlah berkas > 50.000 (batas inode)
        if ($freeBytes < 1024 * 1024 * 1024) {
            $warn = "Peringatan: Ruang disk kritis! Tersisa hanya {$gbFree} GB.";
            $this->warn($warn);
            Log::warning($warn);
        }

        if ($filesCount > 50000) {
            $warn = "Peringatan: Jumlah berkas di storage mencapai {$filesCount}. Pertimbangkan migrasi ke Cloudflare R2.";
            $this->warn($warn);
            Log::warning($warn);
        }

        return self::SUCCESS;
    }
}

