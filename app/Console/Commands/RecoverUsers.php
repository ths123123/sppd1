<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecoverUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:recover 
                          {--create-admin : Create default admin user}
                          {--create-sekretaris : Create default sekretaris user}
                          {--check : Check database integrity}
                          {--fix-passwords : Fix users with missing passwords}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recover missing users and fix user-related issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Starting User Recovery Process...');
        
        if ($this->option('check')) {
            $this->checkDatabaseIntegrity();
        }
        
        if ($this->option('fix-passwords')) {
            $this->fixMissingPasswords();
        }
        
        if ($this->option('create-admin')) {
            $this->createDefaultAdmin();
        }
        
        if ($this->option('create-sekretaris')) {
            $this->createDefaultSekretaris();
        }
        
        // Default action if no specific option
        if (!$this->option('check') && !$this->option('fix-passwords') && 
            !$this->option('create-admin') && !$this->option('create-sekretaris')) {
            $this->performFullRecovery();
        }
        
        $this->info('✅ User Recovery Process Completed!');
    }
    
    private function checkDatabaseIntegrity()
    {
        $this->info('🔍 Checking Database Integrity...');
        
        $userCount = User::count();
        $this->info("Total users in database: {$userCount}");
        
        // Check for users with missing passwords
        $usersWithoutPassword = User::whereNull('password')->count();
        if ($usersWithoutPassword > 0) {
            $this->error("❌ Found {$usersWithoutPassword} users without passwords!");
        } else {
            $this->info("✅ All users have passwords");
        }
        
        // Check for users with missing email
        $usersWithoutEmail = User::whereNull('email')->count();
        if ($usersWithoutEmail > 0) {
            $this->error("❌ Found {$usersWithoutEmail} users without email!");
        } else {
            $this->info("✅ All users have email addresses");
        }
        
        // Check for duplicate emails
        $duplicateEmails = User::select('email')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('email');
            
        if ($duplicateEmails->count() > 0) {
            $this->error("❌ Found duplicate emails: " . $duplicateEmails->implode(', '));
        } else {
            $this->info("✅ No duplicate emails found");
        }
        
        // Check for admin users
        $adminCount = User::where('role', 'admin')->count();
        if ($adminCount === 0) {
            $this->warn("⚠️ No admin users found!");
        } else {
            $this->info("✅ Found {$adminCount} admin user(s)");
        }
    }
    
    private function fixMissingPasswords()
    {
        $this->info('🔧 Fixing users with missing passwords...');
        
        $usersWithoutPassword = User::whereNull('password')->get();
        
        if ($usersWithoutPassword->count() === 0) {
            $this->info("✅ No users with missing passwords found");
            return;
        }
        
        $this->warn("Found {$usersWithoutPassword->count()} users without passwords");
        
        foreach ($usersWithoutPassword as $user) {
            $defaultPassword = 'password123'; // Default password
            $user->password = Hash::make($defaultPassword);
            $user->save();
            
            $this->info("✅ Fixed password for user: {$user->email}");
            
            Log::info('Password fixed for user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'fixed_at' => now(),
            ]);
        }
    }
    
    private function createDefaultAdmin()
    {
        $this->info('👑 Creating default admin user...');
        
        $adminEmail = 'admin@kpu-cirebon.go.id';
        
        // Check if admin already exists
        $existingAdmin = User::where('email', $adminEmail)->first();
        if ($existingAdmin) {
            $this->warn("⚠️ Admin user already exists: {$adminEmail}");
            return;
        }
        
        $admin = User::create([
            'name' => 'Administrator',
            'email' => $adminEmail,
            'password' => 'admin123', // Will be auto-hashed by mutator
            'nip' => '1234567890',
            'jabatan' => 'Administrator',
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        
        $this->info("✅ Admin user created: {$admin->email}");
        $this->warn("🔐 Default password: admin123 (Please change immediately!)");
    }
    
    private function createDefaultSekretaris()
    {
        $this->info('👤 Creating default sekretaris user...');
        
        $sekretarisEmail = 'sekretaris@kpu-cirebon.go.id';
        
        // Check if sekretaris already exists
        $existingSekretaris = User::where('email', $sekretarisEmail)->first();
        if ($existingSekretaris) {
            $this->warn("⚠️ Sekretaris user already exists: {$sekretarisEmail}");
            return;
        }
        
        $sekretaris = User::create([
            'name' => 'Sekretaris KPU',
            'email' => $sekretarisEmail,
            'password' => 'sekretaris123', // Will be auto-hashed by mutator
            'nip' => '0987654321',
            'jabatan' => 'Sekretaris',
            'role' => 'sekretaris',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        
        $this->info("✅ Sekretaris user created: {$sekretaris->email}");
        $this->warn("🔐 Default password: sekretaris123 (Please change immediately!)");
    }
    
    private function performFullRecovery()
    {
        $this->info('🚀 Performing full user recovery...');
        
        // Run all checks and fixes
        $this->checkDatabaseIntegrity();
        $this->fixMissingPasswords();
        
        // Create default users if needed
        if (User::where('role', 'admin')->count() === 0) {
            $this->createDefaultAdmin();
        }
        
        if (User::where('role', 'sekretaris')->count() === 0) {
            $this->createDefaultSekretaris();
        }
        
        // Final verification
        $this->info('📊 Final Status:');
        $this->info('- Total users: ' . User::count());
        $this->info('- Admin users: ' . User::where('role', 'admin')->count());
        $this->info('- Sekretaris users: ' . User::where('role', 'sekretaris')->count());
        $this->info('- Active users: ' . User::where('is_active', true)->count());
    }
}
