<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\SimplePrescription;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Clinic;

echo "=== Patient Records Debug ===" . PHP_EOL;

// Get first patient
$patient = Patient::first();
if (!$patient) {
    echo "❌ No patients found in database" . PHP_EOL;
    exit;
}

echo "✅ Found patient: " . $patient->first_name . " " . $patient->last_name . PHP_EOL;
echo "   Patient ID: " . $patient->id . PHP_EOL;
echo "   Clinic ID: " . $patient->clinic_id . PHP_EOL;

// Check prescriptions
$prescriptions = Prescription::where('patient_id', $patient->id)->get();
echo PHP_EOL . "=== Regular Prescriptions ===" . PHP_EOL;
echo "Count: " . $prescriptions->count() . PHP_EOL;
foreach ($prescriptions as $prescription) {
    echo "- " . $prescription->prescription_number . " (Date: " . $prescription->prescribed_date . ")" . PHP_EOL;
}

// Check simple prescriptions
$simplePrescriptions = SimplePrescription::where('patient_id', $patient->id)->get();
echo PHP_EOL . "=== Simple Prescriptions ===" . PHP_EOL;
echo "Count: " . $simplePrescriptions->count() . PHP_EOL;
foreach ($simplePrescriptions as $prescription) {
    echo "- " . $prescription->prescription_number . " (Date: " . $prescription->prescribed_date . ")" . PHP_EOL;
}

// Check appointments
$appointments = Appointment::where('patient_id', $patient->id)->get();
echo PHP_EOL . "=== Appointments ===" . PHP_EOL;
echo "Count: " . $appointments->count() . PHP_EOL;
foreach ($appointments as $appointment) {
    echo "- " . $appointment->appointment_number . " (Date: " . $appointment->appointment_datetime . ")" . PHP_EOL;
}

// Check if there are any records at all
echo PHP_EOL . "=== Total Records in Database ===" . PHP_EOL;
echo "Total Patients: " . Patient::count() . PHP_EOL;
echo "Total Prescriptions: " . Prescription::count() . PHP_EOL;
echo "Total Simple Prescriptions: " . SimplePrescription::count() . PHP_EOL;
echo "Total Appointments: " . Appointment::count() . PHP_EOL;
echo "Total Users: " . User::count() . PHP_EOL;
echo "Total Clinics: " . Clinic::count() . PHP_EOL;

// Create sample data if none exists
if ($simplePrescriptions->count() == 0 && $appointments->count() == 0) {
    echo PHP_EOL . "=== Creating Sample Data ===" . PHP_EOL;
    
    // Get or create a doctor
    $doctor = User::where('role', 'doctor')->where('clinic_id', $patient->clinic_id)->first();
    if (!$doctor) {
        $doctor = User::where('role', 'admin')->where('clinic_id', $patient->clinic_id)->first();
    }
    
    if ($doctor) {
        // Create sample prescription
        $prescription = SimplePrescription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $patient->clinic_id,
            'prescription_number' => SimplePrescription::generatePrescriptionNumber(),
            'diagnosis' => 'Sample diagnosis for testing',
            'notes' => 'This is a test prescription to verify the system is working',
            'prescribed_date' => now()->toDateString(),
            'status' => 'active'
        ]);
        echo "✅ Created sample prescription: " . $prescription->prescription_number . PHP_EOL;
        
        // Create sample appointment
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $patient->clinic_id,
            'appointment_datetime' => now()->addDays(1),
            'duration_minutes' => 30,
            'type' => 'consultation',
            'status' => 'scheduled',
            'reason' => 'Regular checkup',
            'created_by' => $doctor->id
        ]);
        echo "✅ Created sample appointment: " . $appointment->appointment_number . PHP_EOL;
        
        echo PHP_EOL . "Sample data created! Refresh the patient page to see the records." . PHP_EOL;
    } else {
        echo "❌ No doctor found to create sample data" . PHP_EOL;
    }
}

echo PHP_EOL . "Debug completed!" . PHP_EOL;
