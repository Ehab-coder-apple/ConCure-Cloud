<?php
/**
 * Simulate real patient form submission for Clinic 45
 * Run: php test_real_patient_form.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\PatientController;

echo "=== Simulating Real Patient Form Submission for Clinic 45 ===\n\n";

// Get a user from clinic 45
$user = \App\Models\User::where('clinic_id', 45)->first();
if (!$user) {
    die("❌ No users in clinic 45\n");
}

echo "✅ Logged in as: {$user->first_name} {$user->last_name}\n";
echo "   Clinic: {$user->clinic->name} (ID: {$user->clinic_id})\n\n";

// Authenticate the user
auth()->login($user);

// Create a realistic form request (minimal data like the user might submit)
$formData = [
    'first_name' => 'Test',
    'last_name' => 'Patient_' . time(),
    'phone' => '07701234567',
    // Intentionally leaving out date_of_birth and gender to test nullable fields
];

echo "Form data being submitted:\n";
print_r($formData);
echo "\n";

try {
    // Create request
    $request = Request::create('/patients', 'POST', $formData);
    $request->setUserResolver(function () use ($user) {
        return $user;
    });

    // Simulate the controller
    $controller = new PatientController();
    
    echo "Calling PatientController::store()...\n";
    $response = $controller->store($request);
    
    echo "✅ SUCCESS!\n";
    echo "Response status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 302) {
        echo "Redirect to: " . $response->headers->get('Location') . "\n";
    }
    
    // Check if patient was created
    $lastPatient = \App\Models\Patient::where('clinic_id', 45)
        ->orderBy('id', 'desc')
        ->first();
    
    if ($lastPatient) {
        echo "\n✅ Last patient in clinic 45:\n";
        echo "   ID: {$lastPatient->id}\n";
        echo "   Patient ID: {$lastPatient->patient_id}\n";
        echo "   Name: {$lastPatient->first_name} {$lastPatient->last_name}\n";
        echo "   Phone: {$lastPatient->phone}\n";
        echo "   DOB: " . ($lastPatient->date_of_birth ? $lastPatient->date_of_birth->format('Y-m-d') : 'NULL') . "\n";
        echo "   Gender: " . ($lastPatient->gender ?? 'NULL') . "\n";
    }
    
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "❌ VALIDATION ERROR:\n";
    foreach ($e->errors() as $field => $errors) {
        echo "   {$field}: " . implode(', ', $errors) . "\n";
    }
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n   Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
