<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CleanupUsers extends Command
{
    protected $signature = 'users:cleanup';
    protected $description = 'Verify user roles';

    protected $validRoles = ['admin', 'staff', 'kasubbag', 'sekretaris', 'ppk'];

    public function handle()
    {
        $this->info('Starting user cleanup...');

        // Verify remaining users
        $users = User::all();
        $this->info("\nRemaining users:");
        foreach ($users as $user) {
            $this->info("- {$user->name} ({$user->email}) - Role: {$user->role}");
        }

        $this->info("\nCleanup completed successfully!");
    }
}
