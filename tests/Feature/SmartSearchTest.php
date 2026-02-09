<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Patient;
use App\Models\Medicine;
use App\Models\Food;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SmartSearchTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user with clinic
        $this->user = User::factory()->create([
            'role' => 'doctor',
            'clinic_id' => 1,
        ]);
    }

    /** @test */
    public function it_validates_minimum_search_length_for_patients()
    {
        $this->actingAs($this->user);

        // Empty search should return all patients (no filter applied)
        $response = $this->get('/patients?search=');
        $response->assertStatus(200);

        // Single character search should work
        $response = $this->get('/patients?search=J');
        $response->assertStatus(200);
    }

    /** @test */
    public function it_searches_patients_by_multiple_fields()
    {
        $this->actingAs($this->user);

        // Create test patients
        Patient::factory()->create([
            'clinic_id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'patient_id' => 'P001',
            'phone' => '1234567890',
            'email' => 'john@example.com',
        ]);

        Patient::factory()->create([
            'clinic_id' => 1,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'patient_id' => 'P002',
            'phone' => '0987654321',
            'email' => 'jane@example.com',
        ]);

        // Search by first name
        $response = $this->get('/patients?search=John');
        $response->assertStatus(200);
        $response->assertSee('John');

        // Search by patient ID
        $response = $this->get('/patients?search=P001');
        $response->assertStatus(200);
        $response->assertSee('P001');

        // Search by phone
        $response = $this->get('/patients?search=1234567890');
        $response->assertStatus(200);
        $response->assertSee('1234567890');

        // Search by email
        $response = $this->get('/patients?search=john@example.com');
        $response->assertStatus(200);
        $response->assertSee('john@example.com');
    }

    /** @test */
    public function it_validates_minimum_search_length_for_medicines()
    {
        $this->actingAs($this->user);

        // Empty search should return all medicines
        $response = $this->get('/medicines?search=');
        $response->assertStatus(200);

        // Single character search should work
        $response = $this->get('/medicines?search=A');
        $response->assertStatus(200);
    }

    /** @test */
    public function it_searches_medicines_by_multiple_fields()
    {
        $this->actingAs($this->user);

        // Create test medicines
        Medicine::factory()->create([
            'clinic_id' => 1,
            'name' => 'Aspirin',
            'generic_name' => 'Acetylsalicylic Acid',
            'brand_name' => 'Bayer',
        ]);

        Medicine::factory()->create([
            'clinic_id' => 1,
            'name' => 'Paracetamol',
            'generic_name' => 'Acetaminophen',
            'brand_name' => 'Tylenol',
        ]);

        // Search by name
        $response = $this->get('/medicines?search=Aspirin');
        $response->assertStatus(200);
        $response->assertSee('Aspirin');

        // Search by generic name
        $response = $this->get('/medicines?search=Acetylsalicylic');
        $response->assertStatus(200);
        $response->assertSee('Acetylsalicylic');

        // Search by brand name
        $response = $this->get('/medicines?search=Bayer');
        $response->assertStatus(200);
        $response->assertSee('Bayer');
    }

    /** @test */
    public function it_returns_json_response_for_ajax_searches()
    {
        $this->actingAs($this->user);

        // Create test patient
        Patient::factory()->create([
            'clinic_id' => 1,
            'first_name' => 'Test',
            'last_name' => 'Patient',
        ]);

        // AJAX search should return JSON
        $response = $this->getJson('/patients/api?search=Test');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'first_name', 'last_name']
            ]
        ]);
    }

    /** @test */
    public function it_handles_empty_search_results()
    {
        $this->actingAs($this->user);

        // Search for non-existent patient
        $response = $this->getJson('/patients/api?search=NonExistentPatient123');
        $response->assertStatus(200);
        $response->assertJson([
            'data' => []
        ]);
    }
}

