<?php

namespace App\Services;

use App\Helpers\ClinicHelper;

class WordDocumentService
{
    /**
     * Generate Word document for nutrition plan
     */
    public function generateNutritionPlan($dietPlan, $nutritionalTotals)
    {
        // Create Word document content as HTML that can be opened by Word
        $html = $this->generateWordHtml($dietPlan, $nutritionalTotals);

        return $html;
    }



    /**
     * Generate HTML content optimized for Word
     */
    private function generateWordHtml($dietPlan, $nutritionalTotals)
    {
        $html = '<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <meta name="ProgId" content="Word.Document">
    <meta name="Generator" content="Microsoft Word">
    <meta name="Originator" content="Microsoft Word">
    <title>Daily Meal Plan</title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>90</w:Zoom>
            <w:DoNotPromptForConvert/>
            <w:DoNotShowRevisions/>
            <w:DoNotPrintRevisions/>
            <w:DisplayHorizontalDrawingGridEvery>0</w:DisplayHorizontalDrawingGridEvery>
            <w:DisplayVerticalDrawingGridEvery>2</w:DisplayVerticalDrawingGridEvery>
            <w:UseMarginsForDrawingGridOrigin/>
            <w:ValidateAgainstSchemas/>
            <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
            <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
            <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
            <w:Compatibility>
                <w:BreakWrappedTables/>
                <w:SnapToGridInCell/>
                <w:WrapTextWithPunct/>
                <w:UseAsianBreakRules/>
            </w:Compatibility>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page {
            size: A4;
            margin: 0.65in;
        }

        body {
            font-family: "Segoe UI", Calibri, Arial, "DejaVu Sans", "Amiri", "Arabic Typesetting", "Traditional Arabic", sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            direction: ltr;
        }

        .kurdish {
            font-family: "Amiri", "Segoe UI", Calibri, Arial, "Arabic Typesetting", "Traditional Arabic", sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 10.5pt;
            line-height: 1.4;
            unicode-bidi: plaintext;
        }

        .header {
            text-align: center;
            margin-bottom: 20pt;
            border-bottom: 2pt solid #20B2AA;
            padding-bottom: 10pt;
        }

        .header h1 {
            color: #20B2AA;
            font-size: 16pt;
            font-weight: bold;
            margin: 0;
        }

        .patient-info {
            margin-bottom: 12pt;
            padding: 8pt;
            border: 1pt solid #20B2AA;
            background-color: #f7fbfb;
            border-radius: 6pt;
        }

        .meal-section {
            margin-bottom: 12pt;
            border: 1pt solid #e4f3f2;
            page-break-inside: avoid;
        }

        .meal-header {
            background-color: #20B2AA;
            color: white;
            padding: 6pt 10pt;
            font-size: 11pt;
            font-weight: bold;
        }

        .food-item {
            padding: 8pt 10pt;
            border-bottom: 1pt dotted #e2e8f0;
            background-color: #fcfcfc;
            margin-bottom: 5pt;
            border-left: 3pt solid #79d0c9;
            border-radius: 3pt;
        }

        .food-item:last-child {
            border-bottom: none;
        }

        .food-name {
            font-weight: bold;
            margin-bottom: 4pt;
            color: #2c3e50;
            font-size: 11pt;
        }

        .food-details {
            color: #7f8c8d;
            font-size: 10pt;
            margin-bottom: 2pt;
        }

        .nutritional-info {
            color: #27ae60;
            font-size: 10pt;
            font-weight: 500;
        }

        .meal-total {
            background-color: #f0f8ff;
            padding: 8pt 12pt;
            font-weight: bold;
            color: #20B2AA;
            border-top: 1pt solid #20B2AA;
        }

        .summary {
            margin-top: 20pt;
            padding: 15pt;
            border: 2pt solid #20B2AA;
            background-color: #f0f8ff;
        }

        .summary h3 {
            color: #20B2AA;
            margin-top: 0;
            text-align: center;
            font-size: 14pt;
        }

        .summary-item {
            margin: 8pt 0;
            padding: 4pt 0;
            border-bottom: 1pt dotted #ccc;
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 3pt;
            vertical-align: top;
        }
    </style>
</head>
<body>';

        // Brand header with clinic logo and title
        $clinicId = $dietPlan->patient->clinic_id ?? null;
        $clinicInfo = \App\Helpers\ClinicHelper::getClinicInfo($clinicId);
        $clinicName = htmlspecialchars($clinicInfo['name'] ?? 'ConCure Clinic');
        $logoUrl = $clinicInfo['logo'] ?? null;
        $html .= '<table class="brand-header" role="presentation" cellspacing="0" cellpadding="0" style="width:100%; border-bottom:2pt solid #20B2AA; margin-bottom:10pt;">'
            . '<tr>'
            . '<td style="width:22%; vertical-align:middle; text-align:left;">' . ($logoUrl ? '<img src="' . htmlspecialchars($logoUrl) . '" style="height:36pt;">' : '') . '</td>'
            . '<td style="width:56%; text-align:center; color:#20B2AA;">'
                . '<div style="font-weight:700; font-size:16pt;">' . $clinicName . '</div>'
                . '<div style="font-size:12pt; color:#2c3e50; margin-top:2pt;">Daily Meal Plan</div>'
              . '</td>'
            . '<td style="width:22%;"></td>'
            . '</tr>'
          . '</table>';

        // Patient Info
        $html .= '<div class="patient-info">
            <strong>Name:</strong> ' . htmlspecialchars(($dietPlan->patient->full_name ?? ($dietPlan->patient->name ?? 'Unknown'))) . '<br>
            <strong>Plan Number:</strong> ' . htmlspecialchars($dietPlan->plan_number ?? 'N/A') . '<br>
            <strong>Gender:</strong> ' . htmlspecialchars(ucfirst(strtolower($dietPlan->patient->gender ?? ''))) . '<br>
            <strong>Age:</strong> ' . (int)($dietPlan->patient->age ?? 0) . '<br>
            <strong>Date:</strong> ' . ($dietPlan->created_at ? $dietPlan->created_at->format('Y-m-d') : date('Y-m-d')) . '<br>
            <strong>Doctor:</strong> ' . htmlspecialchars($dietPlan->doctor->name ?? 'Not Assigned') . '
        </div>';

        // Check if this is a flexible meal plan (option-based)
        $isFlexiblePlan = $dietPlan->meals()->where('is_option_based', true)->exists();

        if ($isFlexiblePlan) {
            // Handle flexible meal plan with options
            $html .= $this->generateFlexibleMealPlanHtml($dietPlan);
        } else {
            // Group meals by day and organize properly
            $mealsByDay = $dietPlan->meals->groupBy('day_number')->sortKeys();

            // Check if we have valid day numbers (greater than 0)
            $hasValidDays = $mealsByDay->keys()->filter(function($day) { return $day > 0; })->count() > 0;

            if ($hasValidDays && $mealsByDay->count() > 0) {
            foreach ($mealsByDay as $dayNumber => $dayMeals) {
                // Skip day 0 or invalid days
                if ($dayNumber < 1) {
                    continue;
                }

                $html .= '<div class="day-section">
                    <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5pt; margin-top: 20pt;">Day ' . $dayNumber . '</h3>';

                // Group meals by type for this day
                $mealTypeGroups = [
                    'breakfast' => ['breakfast'],
                    'lunch' => ['lunch'],
                    'dinner' => ['dinner'],
                    'snacks' => ['snack', 'snack_1', 'snack_2', 'snack_3']
                ];

                $dayTotalCalories = 0;

                foreach ($mealTypeGroups as $displayName => $mealTypes) {
                    $meals = $dayMeals->whereIn('meal_type', $mealTypes);

                    if ($meals->count() > 0) {
                        $label = ucfirst($displayName);
                        $outputLang = $dietPlan->language ?? (request()->get('lang') ?? app()->getLocale());
                        $isKurdish = in_array($outputLang, ['ku_bahdini','ku_sorani','ku'], true);
                        if ($outputLang === 'ar') {
                            if ($displayName === 'breakfast') { $label = 'الفطور'; }
                            elseif ($displayName === 'lunch') { $label = 'الغداء'; }
                            elseif ($displayName === 'dinner') { $label = 'العشاء'; }
                            elseif ($displayName === 'snacks') { $label = 'وجبة خفيفة'; }
                        } elseif ($isKurdish) {
                            if ($displayName === 'breakfast') { $label = 'بەیانی'; }
                            elseif ($displayName === 'lunch') { $label = 'نانی نیوەڕۆ'; }
                            elseif ($displayName === 'dinner') { $label = 'شێوان'; }
                            elseif ($displayName === 'snacks') { $label = 'خواردنی سووک'; }
                        }
                        $align = ($outputLang === 'ar' || $isKurdish) ? 'right' : 'left';
                        $html .= '<div class="meal-section"><div class="meal-header" style="text-align:' . $align . ';">' . $label . '</div><ul class="food-list" style="margin:4pt 10pt; padding:0 0 0 12pt;">';

                        foreach ($meals as $meal) {
                            foreach ($meal->foods as $mealFood) {
                                $food = $mealFood->food;
                                $foodName = $mealFood->food_name ?? $mealFood->food_name_display ?? ($food ? $food->name : 'Unknown Food');
                                $html .= '<li class="kurdish" style="margin:1.5pt 0; font-size:10.5pt;">' . htmlspecialchars($foodName) . ' — <span style="color:#666; font-weight:normal; font-size:9.8pt;">' . htmlspecialchars($mealFood->quantity_with_equivalent) . '</span></li>';
                            }
                        }

                        $html .= '</ul></div>';
                    }
                }

                // Add day total
                $html .= '</div>';
            }
        }
        }

        // Add message if no meals found
        $totalMeals = $dietPlan->meals->count();
        if ($totalMeals === 0) {
            $html .= '<div class="no-meals">
                <p><strong>No meals have been added to this nutrition plan yet.</strong></p>
                <p>Please add meals to the plan to see them in the exported document.</p>
            </div>';
        }

        // Summary
        $html .= '<div class="summary">
            <h3>Daily Nutritional Summary</h3>
            <div class="summary-item">
                <span class="summary-label">Total Calories:</span>
                <span class="summary-value">' . number_format($nutritionalTotals['calories'], 0) . ' cal</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Protein:</span>
                <span class="summary-value">' . number_format($nutritionalTotals['protein'], 1) . 'g</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Carbohydrates:</span>
                <span class="summary-value">' . number_format($nutritionalTotals['carbs'], 1) . 'g</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Fat:</span>
                <span class="summary-value">' . number_format($nutritionalTotals['fat'], 1) . 'g</span>
            </div>
        </div>';

        $html .= '<div style="margin-top: 20pt; text-align: center; font-size: 8pt; color: #666; border-top: 1pt solid #ddd; padding-top: 10pt;">
            Generated on ' . now()->format('Y-m-d H:i:s') . ' | ConCure Clinic Management System
        </div>';

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Generate HTML for flexible meal plan with options
     */
    private function generateFlexibleMealPlanHtml($dietPlan)
    {
        try {
            $html = '<div class="flexible-plan">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #20B2AA; padding-bottom: 5pt; margin-top: 20pt;">Flexible Meal Plan - Choose One Option from Each Meal</h3>';

            // Group meals by meal type and option
            $mealsByType = [];
            $mealTypes = ['breakfast', 'lunch', 'dinner', 'snack_1'];
            $outputLang = $dietPlan->language ?? (request()->get('lang') ?? app()->getLocale());
            $isKurdish = in_array($outputLang, ['ku_bahdini','ku_sorani','ku'], true);
            if ($outputLang === 'ar') {
                $mealTypeNames = [
                    'breakfast' => 'الفطور',
                    'lunch' => 'الغداء',
                    'dinner' => 'العشاء',
                    'snack_1' => 'وجبة خفيفة',
                ];
            } elseif ($isKurdish) {
                $mealTypeNames = [
                    'breakfast' => 'بەیانی',
                    'lunch' => 'نانی نیوەڕۆ',
                    'dinner' => 'شێوان',
                    'snack_1' => 'خواردنی سووک',
                ];
            } else {
                $mealTypeNames = [
                    'breakfast' => 'Breakfast',
                    'lunch' => 'Lunch',
                    'dinner' => 'Dinner',
                    'snack_1' => 'Snacks',
                ];
            }

            // Initialize structure
            foreach ($mealTypes as $mealType) {
                $mealsByType[$mealType] = [];
            }

            // Group existing meals by type and option
            if ($dietPlan && $dietPlan->meals) {
                foreach ($dietPlan->meals->where('is_option_based', true) as $meal) {
                    $mealType = $meal->meal_type ?? '';
                    if (in_array($mealType, $mealTypes)) {
                        $mealsByType[$mealType][] = $meal;
                    }
                }
            }

        // Render each meal type with its options
        foreach ($mealTypes as $mealType) {
            if (count($mealsByType[$mealType]) > 0) {
                $align = ($outputLang === 'ar' || $isKurdish) ? 'right' : 'left';
                $html .= '<div class="meal-type-section" style="margin-bottom: 16pt; page-break-inside: avoid;">
                    <h4 style="color: #20B2AA; font-size: 12pt; margin: 0 0 8pt; border-bottom: 1pt solid #20B2AA; padding: 0 0 3pt; text-align:' . $align . ';">' .
                    $mealTypeNames[$mealType] . ' Options</h4>';

                foreach ($mealsByType[$mealType] as $meal) {
                    $html .= '<div class="option-box" style="border:1pt solid #e4f3f2; margin-bottom: 8pt; padding: 8pt; background-color: #fff; border-radius: 6pt;">'
                        . '<div class="option-header" style="font-weight:600; font-size:11pt; margin-bottom:6pt; text-align:center; color:#20B2AA; background-color:#f8f9fa; padding:3pt; border-radius:2pt;">'
                        . 'Option ' . ($meal->option_number ?? 1) . '</div>'
                        . '<ul class="food-list" style="margin:4pt 0 0 0; padding:0 0 0 14pt;">';

                    if ($meal->foods) {
                        foreach ($meal->foods as $mealFood) {
                            $food = $mealFood->food;
                            $foodName = $mealFood->food_name ?? $mealFood->food_name_display ?? ($food ? $food->name : 'Unknown Food');
                            $html .= '<li class="kurdish" style="margin:2pt 0; font-size:11pt;">' . htmlspecialchars($foodName) . ' — <span style="color:#666; font-weight:normal; font-size:10pt;">' . htmlspecialchars($mealFood->quantity_with_equivalent) . '</span></li>';
                        }
                    }

                    $html .= '</ul></div>'; // Close option-box
                }

                $html .= '</div>'; // Close meal-type-section
            }
        }

        // Instructions & Restrictions (typed by user)
        $inst = trim((string)($dietPlan->instructions ?? ''));
        $rest = trim((string)($dietPlan->restrictions ?? ''));
        if ($inst !== '' || $rest !== '') {
            $html .= '<div class="instructions-section" style="margin-top: 15pt; padding: 10pt; border: 1pt solid #20B2AA; background-color: #f0f8ff; border-radius: 6pt;">';
            if ($inst !== '') {
                $html .= '<div class="instructions-title" style="color: #20B2AA; font-weight: bold; font-size: 11pt; margin-bottom: 6pt; text-align: center;">Instructions</div>';
                $html .= '<div class="instructions-content" style="font-size: 9pt; line-height: 1.4; white-space: pre-line;">' . htmlspecialchars($inst) . '</div>';
            }
            if ($rest !== '') {
                $html .= '<div class="instructions-title" style="color: #20B2AA; font-weight: bold; font-size: 11pt; margin: 10pt 0 6pt; text-align: center;">Dietary Restrictions</div>';
                $html .= '<div class="instructions-content" style="font-size: 9pt; line-height: 1.4; white-space: pre-line;">' . htmlspecialchars($rest) . '</div>';
            }
            $html .= '</div>';
        } else {
            // Fallback generic instructions when nothing provided
            $html .= '<div class="instructions-section" style="margin-top: 15pt; padding: 10pt; border: 1pt solid #20B2AA; background-color: #f0f8ff; border-radius: 6pt;">'
                . '<div class="instructions-title" style="color: #20B2AA; font-weight: bold; font-size: 11pt; margin-bottom: 6pt; text-align: center;">Instructions</div>'
                . '<div class="instructions-content" style="font-size: 9pt; line-height: 1.4;">Choose one option from each meal type for each day. You can mix and match different options throughout the week for variety!</div>'
                . '</div>';
        }

        $html .= '</div>'; // Close flexible-plan

        return $html;

        } catch (\Exception $e) {
            // Log the error and return a fallback message
            \Log::error('Error generating flexible meal plan HTML: ' . $e->getMessage(), [
                'diet_plan_id' => $dietPlan->id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return '<div class="error-message" style="padding: 20pt; border: 2pt solid #e74c3c; background-color: #fdf2f2; color: #e74c3c; text-align: center;">
                <h3>Error Generating Flexible Meal Plan</h3>
                <p>There was an issue generating the flexible meal plan. Please contact support if this issue persists.</p>
                <p><small>Error: ' . htmlspecialchars($e->getMessage()) . '</small></p>
            </div>';
        }
    }
}
