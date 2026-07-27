<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Conversation;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalMessagingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        // Run the full database seeder so we have an admin and a doctor in the same clinic
        $this->seed();
    }

    public function test_text_message_flow_unread_and_mark_read(): void
    {
        $admin = User::where('username', 'admin')->first() ?? User::where('email','admin@demo.clinic')->firstOrFail();
        $doctor = User::where('username', 'doctor')->first() ?? User::where('email','doctor@demo.clinic')->firstOrFail();
        $this->assertNotNull($admin->clinic_id);
        $this->assertEquals($admin->clinic_id, $doctor->clinic_id, 'Users must be in same clinic');

        // Create a conversation (admin -> doctor)
        $this->actingAs($admin);
        $resp = $this->post('/messages/conversations', [
            'participant_ids' => [$doctor->id],
            'title' => 'Test Conversation',
        ]);
        $resp->assertStatus(200)->assertJson(['success' => true]);
        $conversationId = $resp->json('conversation_id');
        $this->assertNotEmpty($conversationId);

        // Send a text message from admin
        $send = $this->post('/messages/send', [
            'conversation_id' => $conversationId,
            'body' => 'Hello Doctor',
        ]);
        $send->assertStatus(200)->assertJson(['success' => true]);

        // Doctor sees 1 unread
        $this->actingAs($doctor);
        $unread = $this->get('/messages/unread-count');
        $unread->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals(1, $unread->json('unread'));

        // Doctor marks the conversation as read
        $mark = $this->post("/messages/{$conversationId}/read");
        $mark->assertStatus(200)->assertJson(['success' => true]);

        // Unread count becomes 0
        $unread2 = $this->get('/messages/unread-count');
        $unread2->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals(0, $unread2->json('unread'));
    }

    public function test_transfer_accept_flow(): void
    {
        $admin = User::where('username', 'admin')->first() ?? User::where('email','admin@demo.clinic')->firstOrFail();
        $doctor = User::where('username', 'doctor')->first() ?? User::where('email','doctor@demo.clinic')->firstOrFail();
        $clinic = Clinic::findOrFail($admin->clinic_id);

        // Create a minimal patient record
        $patient = Patient::create([
            'patient_id' => 'P-' . uniqid(),
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'clinic_id' => $clinic->id,
            'created_by' => $admin->id,
        ]);

        // Create a conversation
        $this->actingAs($admin);
        $conv = $this->post('/messages/conversations', [
            'participant_ids' => [$doctor->id],
        ]);
        $conv->assertStatus(200);
        $conversationId = $conv->json('conversation_id');

        // Send a transfer message (use a simple fake source model reference)
        $send = $this->post('/messages/send', [
            'conversation_id' => $conversationId,
            'patient_id' => $patient->id,
            'is_transfer' => true,
            'transfer_type' => 'patient_file',
            'source_type' => 'App\\Models\\Patient',
            'source_id' => $patient->id,
            'metadata' => ['note' => 'Please review file'],
        ]);
        $send->assertStatus(200)->assertJson(['success' => true]);

        // Find the created transfer
        /** @var Conversation $convModel */
        $convModel = Conversation::findOrFail($conversationId);
        $message = $convModel->messages()->latest('id')->first();
        $transfer = $message->transfer; // hasOne
        $this->assertNotNull($transfer);

        // Doctor accepts the transfer
        $this->actingAs($doctor);
        $action = $this->post("/messages/transfers/{$transfer->id}/action", [
            'action' => 'accept',
        ]);
        $action->assertStatus(200)->assertJson(['success' => true, 'status' => 'accepted']);

        $transfer->refresh();
        $this->assertEquals('accepted', $transfer->status);
        $this->assertEquals($doctor->id, $transfer->acted_by);
    }
}

