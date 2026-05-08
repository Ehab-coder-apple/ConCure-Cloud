<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserSession;
use App\Services\SessionManagementService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class TestSessionCreation extends Command
{
    protected $signature = 'test:sessions';
    protected $description = 'Test session creation for the admin user';

    public function handle()
    {
        $this->info('=== Testing Session Creation ===');
        
        // Get admin user
        $user = User::where('role', 'admin')->first();
        if (!$user) {
            $this->error('No admin user found');
            return 1;
        }
        
        $this->info("Found admin user: {$user->email}");
        
        // Check existing sessions
        $existingSessions = UserSession::where('user_id', $user->id)->get();
        $this->info("Existing sessions for this user: {$existingSessions->count()}");
        
        foreach ($existingSessions as $session) {
            $this->line("  - Session ID: {$session->session_id}, Active: " . ($session->isActive() ? 'YES' : 'NO'));
        }
        
        // Create a fake request
        $request = new Request();
        $request->headers->set('User-Agent', 'Test CLI Command');
        
        // Try to create a session
        $this->info("\nAttempting to create new session...");
        try {
            $session = SessionManagementService::createSession($user, $user->email, $request);
            
            if ($session) {
                $this->info("✅ Session created successfully!");
                $this->line("  - Session ID: {$session->session_id}");
                $this->line("  - IP: {$session->ip_address}");
                $this->line("  - Device: {$session->browser} on {$session->os}");
                return 0;
            } else {
                $this->error("❌ Session creation returned null");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error creating session: " . $e->getMessage());
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}
