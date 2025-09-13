@php
    use ArPHP\I18N\Arabic;

    // Initialize Arabic processor once
    $arabicProcessor = new Arabic();

    // Helper function to detect RTL text (Arabic, Kurdish, Persian, etc.)
    function isRTLText($text) {
        return preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text);
    }

    // Helper function to detect Kurdish-specific characters
    function isKurdishText($text) {
        // Kurdish-specific characters: ڕ ڵ ێ ۆ ۊ ڤ ڕ گ ک چ ژ
        return preg_match('/[ڕڵێۆۊڤگکچژ]/u', $text);
    }

    // Helper function to get text direction class with Kurdish font support
    function getTextDirectionClass($text) {
        if (!isRTLText($text)) {
            return 'ltr';
        }

        // Use Kurdish font for Kurdish text, Arabic font for other RTL text
        return isKurdishText($text) ? 'rtl kurdish-text' : 'rtl arabic-text';
    }

    // Helper function to process Kurdish/Arabic text for better rendering
    function processRTLText($text) {
        if (!isRTLText($text)) {
            return $text;
        }

        // Use Arabic processor to shape the text properly
        try {
            // Create a new Arabic processor instance
            $processor = new Arabic();

            // Process the text for proper letter shaping
            $processedText = $processor->utf8Glyphs($text);
            return $processedText ?: $text; // Fallback to original if processing fails
        } catch (Exception $e) {
            return $text; // Fallback to original text if processing fails
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Nutrition Plan - {{ $dietPlan->plan_number }}</title>


    <style>
        body {
            font-family: "dejavu sans", sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        /* Kurdish-specific font styling - force Amiri font which we know works */
        .kurdish-text {
            font-family: "amiri-regular", "dejavu sans";
            direction: rtl;
            text-align: right;
            unicode-bidi: bidi-override;
            writing-mode: horizontal-tb;
            font-size: 14px;
            line-height: 1.8;
        }

        /* RTL text support with proper Unicode handling */
        .arabic-text {
            font-family: "amiri-regular", "dejavu sans";
            direction: rtl;
            text-align: right;
            unicode-bidi: bidi-override;
            writing-mode: horizontal-tb;
            font-size: 14px;
            line-height: 1.8;
        }

        /* Force all RTL text to use Amiri font */
        .rtl {
            font-family: "amiri-regular", "dejavu sans" !important;
            direction: rtl !important;
            text-align: right !important;
            unicode-bidi: bidi-override !important;
            font-size: 14px !important;
        }

        .rtl {
            direction: rtl !important;
            text-align: right !important;
            unicode-bidi: bidi-override !important;
            display: inline-block;
            width: 100%;
        }

        .ltr {
            direction: ltr;
            text-align: left;
            unicode-bidi: normal;
        }

        /* Specific RTL styling for table cells */
        .food-name.rtl {
            direction: rtl !important;
            text-align: right !important;
            unicode-bidi: bidi-override !important;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #008080;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #008080;
            margin: 0;
            font-size: 24px;
        }
        
        .header h2 {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 16px;
            font-weight: normal;
        }
        
        .plan-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .plan-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .plan-info td {
            padding: 5px 10px;
            border: none;
        }
        
        .plan-info .label {
            font-weight: bold;
            color: #008080;
            width: 30%;
        }
        
        .nutritional-targets {
            background-color: #e8f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .nutritional-targets h3 {
            color: #008080;
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        
        .targets-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .targets-grid td {
            width: 25%;
            text-align: center;
            background: white;
            padding: 12px 8px;
            border: 1px solid #ddd;
            vertical-align: middle;
        }

        .target-value {
            font-size: 16px;
            font-weight: bold;
            color: #008080;
            display: block;
        }

        .target-label {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
            display: block;
        }
        
        .meals-section h3 {
            color: #008080;
            border-bottom: 1px solid #008080;
            padding-bottom: 5px;
            margin: 20px 0 15px 0;
        }
        
        .meal {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .meal-header {
            background-color: #008080;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .meal-foods {
            border: 1px solid #ddd;
            border-top: none;
        }
        
        .food-item {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
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
        }

        .food-name.rtl {
            text-align: right;
            direction: rtl;
        }

        .food-name.ltr {
            text-align: left;
            direction: ltr;
        }

        .food-portion {
            color: #666;
            width: 15%;
            text-align: center;
        }

        .food-nutrition {
            font-size: 11px;
            color: #888;
            text-align: right;
            width: 45%;
        }
        
        .meal-totals {
            background-color: #f8f9fa;
            padding: 8px 12px;
            font-weight: bold;
            border-top: 1px solid #ddd;
        }

        .meal-totals table {
            width: 100%;
            border-collapse: collapse;
        }

        .meal-totals td {
            text-align: center;
            padding: 4px;
            font-size: 11px;
        }

        .meal-totals .total-label {
            font-weight: bold;
            color: #008080;
            text-align: left;
            width: 15%;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        
        .notes {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .notes h4 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        
        @media print {
            body { margin: 0; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Nutrition Plan</h1>
        <h2>{{ $dietPlan->plan_number }}</h2>
    </div>

    <!-- Plan Information -->
    <div class="plan-info">
        <table>
            <tr>
                <td class="label">Patient:</td>
                @php
                    $patientName = $dietPlan->patient->first_name . ' ' . $dietPlan->patient->last_name;
                    $planTitle = $dietPlan->title;
                @endphp
                <td class="{{ getTextDirectionClass($patientName) }}">
                    @if(isRTLText($patientName))
                        {!! $arabicProcessor->utf8Glyphs($patientName) !!}
                    @else
                        {{ $patientName }}
                    @endif
                </td>
                <td class="label">Plan Title:</td>
                <td class="{{ getTextDirectionClass($planTitle) }}">
                    @if(isRTLText($planTitle))
                        {!! $arabicProcessor->utf8Glyphs($planTitle) !!}
                    @else
                        {{ $planTitle }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Goal:</td>
                <td>{{ ucwords(str_replace('_', ' ', $dietPlan->goal)) }}</td>
                <td class="label">Duration:</td>
                <td>{{ $dietPlan->duration_days }} days</td>
            </tr>
            <tr>
                <td class="label">Start Date:</td>
                <td>{{ \Carbon\Carbon::parse($dietPlan->start_date)->format('M d, Y') }}</td>
                <td class="label">Created By:</td>
                <td>{{ $dietPlan->doctor->first_name }} {{ $dietPlan->doctor->last_name }}</td>
            </tr>
        </table>
    </div>

    <!-- Nutritional Targets -->
    <div class="nutritional-targets">
        <h3>Daily Nutritional Targets</h3>
        <table class="targets-grid">
            <tr>
                <td>
                    <span class="target-value">{{ number_format($dietPlan->target_calories) }}</span>
                    <span class="target-label">Calories</span>
                </td>
                <td>
                    <span class="target-value">{{ number_format($dietPlan->target_protein) }}g</span>
                    <span class="target-label">Protein</span>
                </td>
                <td>
                    <span class="target-value">{{ number_format($dietPlan->target_carbs) }}g</span>
                    <span class="target-label">Carbohydrates</span>
                </td>
                <td>
                    <span class="target-value">{{ number_format($dietPlan->target_fat) }}g</span>
                    <span class="target-label">Fat</span>
                </td>
            </tr>
        </table>
    </div>

    @if($dietPlan->description)
    <!-- Notes -->
    <div class="notes">
        <h4>Plan Description</h4>
        <p class="{{ getTextDirectionClass($dietPlan->description) }}">
            @if(isRTLText($dietPlan->description))
                {!! $arabicProcessor->utf8Glyphs($dietPlan->description) !!}
            @else
                {{ $dietPlan->description }}
            @endif
        </p>
    </div>
    @endif

    <!-- Meals Section -->
    <div class="meals-section">
        <h3>Daily Meal Plan</h3>
        
        @forelse($dietPlan->meals->groupBy('meal_type') as $mealType => $meals)
            <div class="meal">
                <div class="meal-header">
                    @php
                        $displayName = match($mealType) {
                            'snack_1', 'snack_2', 'snack_3', 'snack' => 'Snacks',
                            default => ucfirst($mealType)
                        };
                    @endphp
                    {{ $displayName }}
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
                                // Convert quantity to grams for calculation if needed
                                $quantityInGrams = $quantity; // Assuming quantity is already in grams for now
                                $calories = ($food->calories * $quantityInGrams) / 100;
                                $protein = ($food->protein * $quantityInGrams) / 100;
                                $carbs = ($food->carbohydrates * $quantityInGrams) / 100;
                                $fat = ($food->fat * $quantityInGrams) / 100;
                                
                                $mealCalories += $calories;
                                $mealProtein += $protein;
                                $mealCarbs += $carbs;
                                $mealFat += $fat;
                            @endphp
                            
                            @php
                                $foodName = $mealFood->food_name ?: $food->name;
                            @endphp
                            <div class="food-item">
                                <table>
                                    <tr>
                                        <td class="food-name {{ getTextDirectionClass($foodName) }}">
                                            {!! $foodName !!}
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
                    
                    <div class="meal-totals">
                        <table>
                            <tr>
                                <td class="total-label">Total:</td>
                                <td>{{ number_format($mealCalories, 0) }} cal</td>
                                <td>{{ number_format($mealProtein, 1) }}g protein</td>
                                <td>{{ number_format($mealCarbs, 1) }}g carbs</td>
                                <td>{{ number_format($mealFat, 1) }}g fat</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <p style="text-align: center; color: #666; font-style: italic;">No meals have been added to this nutrition plan yet.</p>
        @endforelse
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Generated on {{ now()->format('M d, Y \a\t g:i A') }} | ConCure Clinic Management System</p>
        <p>This nutrition plan is personalized for {{ $dietPlan->patient->first_name }} {{ $dietPlan->patient->last_name }} and should be followed under medical supervision.</p>
    </div>
</body>
</html>
