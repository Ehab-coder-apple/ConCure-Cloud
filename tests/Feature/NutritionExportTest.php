<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\DietPlan;
use App\Models\DietPlanMeal;
use App\Models\DietPlanMealFood;
use App\Models\Food;
use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NutritionExportTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $clinic;
    private $patient;
    private $dietPlan;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test clinic
        $this->clinic = Clinic::factory()->create();

        // Create test user
        $this->user = User::factory()->create([
            'clinic_id' => $this->clinic->id,
            'role' => 'doctor',
        ]);

        // Create test patient
        $this->patient = Patient::factory()->create([
            'clinic_id' => $this->clinic->id,
        ]);

        // Create test diet plan
        $this->dietPlan = DietPlan::factory()->create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->user->id,
        ]);

        // Create test food
        $food = Food::factory()->create([
            'clinic_id' => $this->clinic->id,
        ]);

        // Create test meal
        $meal = DietPlanMeal::factory()->create([
            'diet_plan_id' => $this->dietPlan->id,
            'meal_type' => 'breakfast',
            'day_number' => 1,
        ]);

        // Create test meal food
        DietPlanMealFood::factory()->create([
            'diet_plan_meal_id' => $meal->id,
            'food_id' => $food->id,
            'food_name' => $food->name,
            'quantity' => 100,
            'unit' => 'g',
        ]);
    }

    /** @test */
    public function user_can_download_daily_pdf()
    {
        $response = $this->actingAs($this->user)
            ->get(route('nutrition.pdf', $this->dietPlan));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('nutrition-plan-', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function user_can_download_daily_word()
    {
        $response = $this->actingAs($this->user)
            ->get(route('nutrition.word', $this->dietPlan));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/msword');
        $this->assertStringContainsString('nutrition-plan-', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function user_can_download_weekly_pdf()
    {
        $response = $this->actingAs($this->user)
            ->get(route('nutrition.weekly-pdf', $this->dietPlan));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('weekly-nutrition-plan-', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function user_can_download_weekly_word()
    {
        $response = $this->actingAs($this->user)
            ->get(route('nutrition.weekly-word', $this->dietPlan));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/msword');
        $this->assertStringContainsString('weekly-nutrition-plan-', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function unauthorized_user_cannot_access_exports()
    {
        $otherClinic = Clinic::factory()->create();
        $otherUser = User::factory()->create([
            'clinic_id' => $otherClinic->id,
            'role' => 'doctor',
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('nutrition.weekly-pdf', $this->dietPlan));

        $response->assertStatus(403);
    }
}
