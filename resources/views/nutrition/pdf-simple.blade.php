<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daily Meal Plan</title>
    <style>
        body {
            font-family: "dejavu sans", sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            margin: 20px;
        }

        /* Kurdish/Arabic text styling - optimized for letter connection */
        .kurdish {
            font-family: "Amiri-Regular", "amiri-regular", "Amiri", "Noto Sans Arabic", "Arabic Typesetting", "Traditional Arabic", "dejavu sans", serif;
            direction: rtl;
            text-align: right;
            unicode-bidi: embed;
            writing-mode: horizontal-tb;
            font-size: 20px;
            line-height: 2.5;
            font-weight: normal;
            letter-spacing: -0.5px;
            word-spacing: 2px;
            text-rendering: optimizeLegibility;
            font-feature-settings: "liga" 1, "calt" 1, "ccmp" 1, "curs" 1;
            -webkit-font-feature-settings: "liga" 1, "calt" 1, "ccmp" 1, "curs" 1;
        }

        .header {
            margin-bottom: 30px;
        }

        .clinic-header-table {
            width: 100%;
            margin-bottom: 15px;
        }

        .clinic-logo {
            max-height: 85px;
            max-width: 85px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            padding: 1px;
        }

        .header h1 {
            color: #20B2AA;
            font-size: 24px;
            margin: 0;
        }

        .clinic-name {
            color: #20B2AA;
            font-size: 22px;
            font-weight: bold;
            margin: 0 0 5px 0;
        }

        .clinic-info {
            font-size: 12px;
            color: #666;
            margin: 0;
        }

        .header-divider {
            border-bottom: 2px solid #20B2AA;
            margin-bottom: 20px;
        }

        .patient-info {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }

        .meal-section {
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        .meal-header {
            background-color: #20B2AA;
            color: white;
            padding: 10px 15px;
            font-size: 16px;
            font-weight: bold;
        }

        .food-item {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }

        .food-item:last-child {
            border-bottom: none;
        }

        .food-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .food-details {
            color: #666;
            font-size: 12px;
        }

        .meal-total {
            background-color: #f0f8ff;
            padding: 10px 15px;
            font-weight: bold;
            color: #20B2AA;
            border-top: 1px solid #20B2AA;
        }

        .summary {
            margin-top: 30px;
            padding: 20px;
            border: 2px solid #20B2AA;
            background-color: #f0f8ff;
        }

        .summary h3 {
            color: #20B2AA;
            margin-top: 0;
            text-align: center;
        }

        .summary-item {
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }

        .summary-label {
            font-weight: bold;
            display: inline-block;
            width: 60%;
        }

        .summary-value {
            display: inline-block;
            width: 35%;
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        @php
            $clinicLogo = \App\Http\Controllers\SettingsController::getClinicLogo($dietPlan->patient->clinic_id);
            $clinicName = $dietPlan->patient->clinic->name ?? 'ConCure Clinic';
        @endphp

        <table class="clinic-header-table">
            <tr>
                @if($clinicLogo)
                    <td style="width: 90px; vertical-align: top; text-align: center;">
                        <img src="{{ public_path('storage/' . str_replace('storage/', '', $clinicLogo)) }}"
                             alt="Clinic Logo"
                             class="clinic-logo">
                    </td>
                @endif
                <td style="vertical-align: top; text-align: {{ $clinicLogo ? 'left' : 'center' }}; {{ $clinicLogo ? 'padding-left: 15px;' : '' }}">
                    <div class="clinic-name">{{ $clinicName }}</div>
                    <h1>Daily Meal Plan</h1>
                </td>
            </tr>
        </table>
        <div class="header-divider"></div>
    </div>

    <div class="patient-info">
        <strong>Patient:</strong> {{ $dietPlan->patient->name }}<br>
        <strong>Plan Number:</strong> {{ $dietPlan->plan_number }}<br>
        <strong>Date:</strong> {{ $dietPlan->created_at->format('Y-m-d') }}<br>
        <strong>Doctor:</strong> {{ $dietPlan->doctor->name }}
    </div>

    @php
        $mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'];
    @endphp

    @foreach($mealTypes as $mealType)
        @php
            $meals = $dietPlan->meals->where('meal_type', $mealType);
        @endphp
        
        @if($meals->count() > 0)
            <div class="meal-section">
                <div class="meal-header">
                    {{ ucfirst($mealType) }}
                </div>
                
                @php
                    $mealCalories = 0;
                @endphp
                
                @foreach($meals as $meal)
                    @foreach($meal->foods as $mealFood)
                        @php
                            $food = $mealFood->food;
                            $quantity = $mealFood->quantity;
                            $calories = ($food->calories * $quantity) / 100;
                            $protein = ($food->protein * $quantity) / 100;
                            $carbs = ($food->carbohydrates * $quantity) / 100;
                            $fat = ($food->fat * $quantity) / 100;
                            
                            $mealCalories += $calories;
                        @endphp
                        
                        <div class="food-item">
                            <div class="food-name kurdish">
                                {!! $mealFood->food_name !!}
                            </div>
                            <div class="food-details">
                                {{ $mealFood->quantity }} {{ $mealFood->unit }} | 
                                {{ number_format($calories, 0) }} cal | 
                                {{ number_format($protein, 1) }}g protein | 
                                {{ number_format($carbs, 1) }}g carbs | 
                                {{ number_format($fat, 1) }}g fat
                            </div>
                        </div>
                    @endforeach
                @endforeach
                
                <div class="meal-total">
                    Total: {{ number_format($mealCalories, 0) }} calories
                </div>
            </div>
        @endif
    @endforeach

    <div class="summary">
        <h3>Daily Nutritional Summary</h3>
        
        <div class="summary-item">
            <span class="summary-label">Total Calories:</span>
            <span class="summary-value">{{ number_format($nutritionalTotals['calories'], 0) }} cal</span>
        </div>
        
        <div class="summary-item">
            <span class="summary-label">Total Protein:</span>
            <span class="summary-value">{{ number_format($nutritionalTotals['protein'], 1) }}g</span>
        </div>
        
        <div class="summary-item">
            <span class="summary-label">Total Carbohydrates:</span>
            <span class="summary-value">{{ number_format($nutritionalTotals['carbs'], 1) }}g</span>
        </div>
        
        <div class="summary-item">
            <span class="summary-label">Total Fat:</span>
            <span class="summary-value">{{ number_format($nutritionalTotals['fat'], 1) }}g</span>
        </div>
    </div>

    <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 15px;">
        Generated on {{ now()->format('Y-m-d H:i:s') }} | ConCure Clinic Management System
    </div>
</body>
</html>
