<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\User;
use App\Services\ProjectAccess;
use Illuminate\Console\Command;

class AssignOwner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kds:assign-owner {project : ID atau nama projek} {email : Alamat email pengguna}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menetapkan seorang pengguna sebagai Owner pada sebuah projek (darurat)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $projectIdentifier = $this->argument('project');
        $email = $this->argument('email');

        $project = is_numeric($projectIdentifier)
            ? Project::find($projectIdentifier)
            : Project::where('name', $projectIdentifier)->first();

        if (! $project) {
            $this->error("Projek [{$projectIdentifier}] tidak ditemukan.");
            return Command::FAILURE;
        }

        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error("Pengguna dengan email [{$email}] tidak ditemukan.");
            return Command::FAILURE;
        }

        if (! $user->isActive()) {
            $this->error("Pengguna [{$email}] dalam status nonaktif.");
            return Command::FAILURE;
        }

        ProjectAccess::assignRole($project, $user, 'owner');

        $this->info("Pengguna [{$user->name} ({$email})] berhasil ditetapkan sebagai Owner pada projek [{$project->name}].");

        return Command::SUCCESS;
    }
}

