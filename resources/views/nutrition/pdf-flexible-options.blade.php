<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Flexible Meal Plan Options</title>
    <style>
        @page {
            margin: 15mm 10mm;
            size: A4;
        }
        
        body {
            font-family: "dejavu sans", sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Kurdish/Arabic text styling */
        .kurdish {
            font-family: "Amiri-Regular", "amiri-regular", "Amiri", "Noto Sans Arabic", "Arabic Typesetting", "Traditional Arabic", "dejavu sans", serif;
            direction: rtl;
            text-align: right;
            unicode-bidi: embed;
            font-size: 12px;
            line-height: 1.4;
            font-weight: normal;
            letter-spacing: -0.3px;
            word-spacing: 1px;
        }

        /* Header */
        .header {
            margin-bottom: 15px;
            border-bottom: 2px solid #20B2AA;
            padding-bottom: 8px;
        }

        .clinic-header-table {
            width: 100%;
            margin-bottom: 8px;
        }

        .clinic-logo {
            max-height: 60px;
            max-width: 60px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
            padding: 2px;
            background: white;
        }

        .header h1 {
            color: #20B2AA;
            font-size: 16px;
            margin: 0;
            text-align: center;
        }

        .clinic-name {
            color: #20B2AA;
            font-size: 14px;
            font-weight: bold;
            margin: 0 0 3px 0;
        }

        /* Patient Info */
        .patient-info {
            margin-bottom: 12px;
            padding: 8px 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            font-size: 9px;
        }

        /* Flexible Options Layout - 3 Options Grid */
        .meal-type-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .meal-type-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
            padding-bottom: 3px;
            border-bottom: 1px solid #333;
        }

        .options-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .option-row {
            display: table-row;
        }

        .option-cell {
            display: table-cell;
            vertical-align: top;
            padding: 3px;
        }

        .option-box {
            border: 1.5px solid #20B2AA;
            min-height: 80px;
            padding: 10px;
            background-color: #fff;
            border-radius: 3px;
            margin-bottom: 5px;
        }

        .option-header {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 8px;
            text-align: center;
            color: #20B2AA;
            background-color: #f8f9fa;
            padding: 4px;
            border-radius: 2px;
        }

        .food-item {
            margin-bottom: 4px;
            font-size: 10px;
            line-height: 1.3;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .food-name {
            font-weight: 500;
            color: #333;
            flex: 1;
        }

        .food-details {
            color: #666;
            font-size: 9px;
            font-weight: normal;
            white-space: nowrap;
        }

        .option-summary {
            margin-top: 8px;
            padding: 4px 6px;
            font-size: 9px;
            color: #20B2AA;
            text-align: center;
            border-top: 1px solid #e0e0e0;
            padding-top: 6px;
            font-weight: 600;
            background-color: #f8f9fa;
            border-radius: 2px;
        }

        /* Instructions Section */
        .instructions-section {
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #20B2AA;
            background-color: #f0f8ff;
            border-radius: 6px;
        }

        .instructions-title {
            color: #20B2AA;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 6px;
            text-align: center;
        }

        .instructions-content {
            font-size: 9px;
            line-height: 1.4;
        }

        .choice-note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 8px;
            margin: 10px 0;
            border-radius: 4px;
            font-size: 9px;
            text-align: center;
        }

        .choice-note strong {
            color: #856404;
        }

        /* Footer */
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 7px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }

        /* Print optimizations */
        @media print {
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        @php
            $clinicInfo = \App\Helpers\ClinicHelper::getClinicInfo($dietPlan->patient->clinic_id);
            $clinicLogo = $clinicInfo['logo_pdf_path'];
            $clinicName = $clinicInfo['name'];
        @endphp

        <table class="clinic-header-table">
            <tr>
                <td style="vertical-align: top; text-align: left; width: {{ ($clinicLogo && file_exists($clinicLogo)) ? 'calc(100% - 80px)' : '100%' }};">
                    <div class="clinic-name">{{ $clinicName }}</div>
                    <h1>Flexible Meal Plan Options</h1>
                </td>
                @if($clinicLogo && file_exists($clinicLogo))
                    <td style="width: 70px; vertical-align: top; text-align: right; padding-left: 12px;">
                        <img src="{{ $clinicLogo }}" alt="Clinic Logo" class="clinic-logo">
                    </td>
                @endif
            </tr>
        </table>
    </div>

    <!-- Patient Info -->
    <div class="patient-info">
        <strong>Patient:</strong> {{ $dietPlan->patient->first_name }} {{ $dietPlan->patient->last_name }} &nbsp;&nbsp;&nbsp;
        <strong>Plan #:</strong> {{ $dietPlan->plan_number }} &nbsp;&nbsp;&nbsp;
        <strong>Date:</strong> {{ $dietPlan->created_at->format('M d, Y') }} &nbsp;&nbsp;&nbsp;
        <strong>Created by:</strong> {{ $dietPlan->doctor->full_name_with_title }}
    </div>

    <!-- Choice Instructions -->
    <div class="choice-note">
        <strong>How to Use This Plan:</strong> Choose one option from each meal type for each day. 
        You can mix and match different options throughout the week for variety!
    </div>

    @php
        // Group meals by meal type and option
        $mealsByType = [];
        $mealTypes = ['breakfast', 'lunch', 'dinner', 'snack_1'];
        $mealTypeNames = [
            'breakfast' => 'Breakfast Options',
            'lunch' => 'Lunch Options', 
            'dinner' => 'Dinner Options',
            'snack_1' => 'Snack Options'
        ];
        
        // Initialize structure
        foreach ($mealTypes as $mealType) {
            $mealsByType[$mealType] = [];
        }
        
        // Group existing meals by type and option
        foreach ($dietPlan->meals->where('is_option_based', true) as $meal) {
            $mealType = $meal->meal_type;
            if (in_array($mealType, $mealTypes)) {
                $mealsByType[$mealType][] = $meal;
            }
        }
    @endphp

    <!-- Meal Options by Type -->
    @foreach ($mealTypes as $mealType)
        @if(count($mealsByType[$mealType]) > 0)
        <div class="meal-type-section">
            <div class="meal-type-title">
                {{ str_replace(' Options', '', $mealTypeNames[$mealType]) }}
            </div>

            <div class="options-grid">
                <div class="option-row">
                    @foreach($mealsByType[$mealType] as $index => $meal)
                        <div class="option-cell">
                            <div class="option-box">
                                <div class="option-header">
                                    Option {{ $meal->option_number }}
                                </div>

                                @php
                                    $optionCalories = 0;
                                    $optionProtein = 0;
                                    $optionCarbs = 0;
                                    $optionFat = 0;
                                @endphp

                                @foreach ($meal->foods as $mealFood)
                                    @php
                                        $food = $mealFood->food;
                                        $quantity = $mealFood->quantity;
                                        if ($food) {
                                            $calories = ($food->calories * $quantity) / 100;
                                            $protein = ($food->protein * $quantity) / 100;
                                            $carbs = ($food->carbohydrates * $quantity) / 100;
                                            $fat = ($food->fat * $quantity) / 100;

                                            $optionCalories += $calories;
                                            $optionProtein += $protein;
                                            $optionCarbs += $carbs;
                                            $optionFat += $fat;
                                        }
                                    @endphp

                                    <div class="food-item">
                                        <span class="food-name kurdish">{!! $mealFood->food_name !!}</span>
                                        <span class="food-details">
                                            ({{ $mealFood->quantity }}{{ $mealFood->unit }})
                                        </span>
                                    </div>
                                @endforeach

                                @if ($optionCalories > 0)
                                    <div class="option-summary">
                                        {{ number_format($optionCalories, 0) }} cal
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    @endforeach

    <!-- Instructions -->
    @if ($dietPlan->instructions || $dietPlan->restrictions)
        <div class="instructions-section">
            <div class="instructions-title">Important Instructions</div>
            <div class="instructions-content">
                @if ($dietPlan->instructions)
                    <strong>General Instructions:</strong><br>
                    {{ $dietPlan->instructions }}<br><br>
                @endif
                
                @if ($dietPlan->restrictions)
                    <strong>Dietary Restrictions:</strong><br>
                    {{ $dietPlan->restrictions }}
                @endif
            </div>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Generated on {{ now()->format('M d, Y H:i') }} | ConCure Clinic Management System<br>
        <strong>Remember:</strong> Choose one option from each meal type daily. Consult your doctor for any questions.
    </div>
</body>
</html>
