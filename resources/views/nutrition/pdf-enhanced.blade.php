<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Meal Plan</title>
    <style>
        body {
            font-family: "dejavu sans", sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        {!! $pdfService->getKurdishCss() !!}

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #20B2AA;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #20B2AA;
            font-size: 28px;
            margin: 0;
            font-weight: bold;
        }

        .patient-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            background-color: #f9f9f9;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 30%;
            padding: 5px 10px;
            color: #20B2AA;
        }

        .info-value {
            display: table-cell;
            padding: 5px 10px;
            width: 70%;
        }

        .meal-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .meal-header {
            background-color: #20B2AA;
            color: white;
            padding: 12px 15px;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 0;
        }

        .meal-foods {
            border: 1px solid #ddd;
            border-top: none;
        }

        .food-item {
            border-bottom: 1px solid #eee;
            padding: 0;
        }

        .food-item:last-child {
            border-bottom: none;
        }

        .food-item table {
            width: 100%;
            border-collapse: collapse;
        }

        .food-name {
            font-weight: bold;
            width: 40%;
            padding: 10px;
            vertical-align: top;
        }

        .food-portion {
            width: 20%;
            padding: 10px;
            text-align: center;
            color: #666;
            vertical-align: top;
        }

        .food-nutrition {
            width: 40%;
            padding: 10px;
            font-size: 11px;
            color: #555;
            vertical-align: top;
        }

        .meal-total {
            background-color: #f0f8ff;
            padding: 10px 15px;
            font-weight: bold;
            color: #20B2AA;
            border-top: 2px solid #20B2AA;
        }

        .daily-summary {
            margin-top: 30px;
            border: 2px solid #20B2AA;
            border-radius: 5px;
            padding: 20px;
            background-color: #f0f8ff;
        }

        .daily-summary h3 {
            color: #20B2AA;
            margin-top: 0;
            text-align: center;
            font-size: 20px;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-row {
            display: table-row;
        }

        .summary-label {
            display: table-cell;
            font-weight: bold;
            width: 50%;
            padding: 8px;
            color: #20B2AA;
        }

        .summary-value {
            display: table-cell;
            padding: 8px;
            text-align: right;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }

        /* Page break control */
        .page-break {
            page-break-before: always;
        }

        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Meal Plan</h1>
    </div>

    <div class="patient-info">
        <div class="info-row">
            <div class="info-label">Patient:</div>
            <div class="info-value">{{ $dietPlan->patient->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Plan Number:</div>
            <div class="info-value">{{ $dietPlan->plan_number }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Created Date:</div>
            <div class="info-value">{{ $dietPlan->created_at->format('Y-m-d') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Doctor:</div>
            <div class="info-value">{{ $dietPlan->doctor->name }}</div>
        </div>
    </div>

    @php
        $mealTypes = ['breakfast', 'lunch', 'dinner', 'snacks'];
        $mealTypeMapping = [
            'breakfast' => 'Breakfast',
            'lunch' => 'Lunch',
            'dinner' => 'Dinner',
            'snacks' => 'Snacks'
        ];
    @endphp

    @foreach($mealTypes as $mealType)
        @php
            // Handle different snack variations
            if ($mealType === 'snacks') {
                $meals = $dietPlan->meals->filter(function($meal) {
                    return in_array($meal->meal_type, ['snack', 'snack_1', 'snack_2', 'snack_3']);
                });
            } else {
                $meals = $dietPlan->meals->where('meal_type', $mealType);
            }
        @endphp

        @if($meals->count() > 0)
            <div class="meal-section no-break">
                <div class="meal-header">
                    {{ $mealTypeMapping[$mealType] ?? ucfirst($mealType) }}
                </div>
                <div class="meal-foods">
                    @php
                        $mealCalories = 0;
                        $mealProtein = 0;
                        $mealCarbs = 0;
                        $mealFat = 0;
                    @endphp
                    
                    @foreach($meals as $meal)
                        @foreach($meal->foods as $mealFood)
                            @php
                                $food = $mealFood->food;
                                $quantity = $mealFood->quantity;
                                $quantityInGrams = $quantity;
                                $calories = ($food->calories * $quantityInGrams) / 100;
                                $protein = ($food->protein * $quantityInGrams) / 100;
                                $carbs = ($food->carbohydrates * $quantityInGrams) / 100;
                                $fat = ($food->fat * $quantityInGrams) / 100;
                                
                                $mealCalories += $calories;
                                $mealProtein += $protein;
                                $mealCarbs += $carbs;
                                $mealFat += $fat;
                            @endphp
                            
                            <div class="food-item">
                                <table>
                                    <tr>
                                        <td class="food-name rtl">
                                            {!! $mealFood->food_name !!}
                                        </td>
                                        <td class="food-portion">{{ $mealFood->quantity }} {{ $mealFood->unit }}</td>
                                        <td class="food-nutrition">
                                            {{ number_format($calories, 0) }} cal |
                                            {{ number_format($protein, 1) }}g protein |
                                            {{ number_format($carbs, 1) }}g carbs |
                                            {{ number_format($fat, 1) }}g fat
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        @endforeach
                    @endforeach
                    
                    <div class="meal-total">
                        Total: {{ number_format($mealCalories, 0) }} cal
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <div class="daily-summary">
        <h3>Daily Nutritional Summary</h3>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-label">Total Calories:</div>
                <div class="summary-value">{{ number_format($nutritionalTotals['calories'], 0) }} cal</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Total Protein:</div>
                <div class="summary-value">{{ number_format($nutritionalTotals['protein'], 1) }}g</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Total Carbohydrates:</div>
                <div class="summary-value">{{ number_format($nutritionalTotals['carbs'], 1) }}g</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Total Fat:</div>
                <div class="summary-value">{{ number_format($nutritionalTotals['fat'], 1) }}g</div>
            </div>
        </div>
    </div>

    <div class="footer">
        Generated on {{ now()->format('Y-m-d H:i:s') }} | ConCure Clinic Management System
    </div>
</body>
</html>
