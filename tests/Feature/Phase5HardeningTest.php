<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class Phase5HardeningTest extends TestCase
{
    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_heartbeat_channel_configuration(): void
    {
        $channelConfig = config('logging.channels.heartbeat');
        $this->assertNotNull($channelConfig);
        $this->assertEquals('single', $channelConfig['driver']);
        $this->assertStringContainsString('heartbeat.log', $channelConfig['path']);

        // Pastikan bisa menulis ke channel
        Log::channel('heartbeat')->info('test heartbeat scheduler');
        $this->assertFileExists(storage_path('logs/heartbeat.log'));
    }

    public function test_backup_database_command(): void
    {
        $this->artisan('kds:backup-db')
            ->assertSuccessful();

        $backups = File::glob(storage_path('backups/db-*.sql.gz'));
        $this->assertNotEmpty($backups);

        // Verifikasi berkas tidak kosong
        $latest = end($backups);
        $this->assertGreaterThan(0, filesize($latest));
    }

    public function test_monitor_storage_command(): void
    {
        $this->artisan('kds:monitor-storage')
            ->assertSuccessful();
    }

    public function test_deployment_artifacts_exist_and_valid(): void
    {
        $this->assertFileExists(base_path('deploy/deploy.sh'));
        $this->assertFileExists(base_path('deploy/index.hostinger.php'));

        $deploySh = file_get_contents(base_path('deploy/deploy.sh'));
        $this->assertStringContainsString('git pull --ff-only', $deploySh);
        $this->assertStringContainsString('artisan migrate --force', $deploySh);
        $this->assertStringContainsString('mysqldump', $deploySh);

        $indexHostinger = file_get_contents(base_path('deploy/index.hostinger.php'));
        $this->assertStringContainsString('usePublicPath', $indexHostinger);
    }
}

