<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUsers extends Command
{
    protected $signature = 'users:check';
    protected $description = 'Check users with sekretaris role';

    public function handle()
    {
        $users = User::where('role', 'sekretaris')->get(['id', 'name', 'email']);
        
        if ($users->isEmpty()) {
            $this->info('No users found with sekretaris role.');
            return;
        }
        
        $this->info('Users with sekretaris role:');
        foreach ($users as $user) {
            $this->line("ID: {$user->id}, Name: {$user->name}, Email: {$user->email}");
        }
    }
}
