<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class SessionActivityControllerTest extends TestCase
{
    public function test_keep_alive_updates_last_activity_and_returns_alive_json(): void
    {
        $user = new User([
            'first_name' => 'Session',
            'last_name' => 'User',
            'email' => 'session@example.com',
            'role' => 'doctor',
        ]);
        $user->id = 123;

        $response = $this->actingAs($user)->postJson('/session/keep-alive');

        $response->assertOk()->assertJson([
            'status' => 'alive',
            'message' => 'Session kept alive',
            'user' => [
                'id' => $user->id,
            ],
        ]);

        $this->assertIsInt(session('last_activity'));
    }

    public function test_status_returns_remaining_time_for_authenticated_user(): void
    {
        $user = new User([
            'first_name' => 'Status',
            'last_name' => 'User',
            'email' => 'status@example.com',
            'role' => 'doctor',
        ]);
        $user->id = 456;

        $lastActivity = now()->subSeconds(30)->timestamp;

        $response = $this->actingAs($user)
            ->withSession(['last_activity' => $lastActivity])
            ->getJson('/session/status');

        $response->assertOk()->assertJson([
            'status' => 'active',
            'last_activity' => date('Y-m-d H:i:s', $lastActivity),
        ]);

        $this->assertIsInt($response->json('remaining_seconds'));
        $this->assertGreaterThanOrEqual(0, $response->json('remaining_seconds'));
    }
}