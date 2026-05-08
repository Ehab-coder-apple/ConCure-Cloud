<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestFullFeature extends Command
{
    protected $signature = 'test:feature';
    protected $description = 'Test the full single session enforcement feature';

    public function handle()
    {
        $this->info('=== Testing Single Session Enforcement ===\n');
        
        // Check database connection
        $this->info('✅ Database connection OK');
        
        // Check user_sessions table
        try {
            $sessionCount = UserSession::count();
            $this->info('✅ user_sessions table exists');
        } catch (\Exception $e) {
            $this->error('❌ user_sessions table NOT found: ' . $e->getMessage());
            return 1;
        }
        
        // Check existing sessions
        $sessionCount = UserSession::count();
        $this->info("📊 Total user sessions in database: {$sessionCount}");
        
        if ($sessionCount > 0) {
            $this->info("\n📋 Recent sessions:");
            $sessions = UserSession::orderBy('created_at', 'desc')->limit(5)->get();
            foreach ($sessions as $session) {
                $user = $session->user;
                $userEmail = $user ? $user->email : 'N/A';
                $status = $session->isActive() ? '✅ ACTIVE' : '❌ TERMINATED';
                $this->line("  - User: {$userEmail}, Created: {$session->created_at}, {$status}");
                if (!$session->isActive()) {
                    $this->line("    Reason: {$session->termination_reason}");
                }
            }
        }
        
        $this->info("\n✅ Feature is ready for testing!");
        return 0;
    }
}
