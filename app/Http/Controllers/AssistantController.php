<?php

namespace App\Http\Controllers;

use App\Models\AiChatMessage;
use App\Models\AiDisclaimerAcceptance;
use App\Services\AiDataService;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AssistantController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check permission for AI Assistant access
        if (!(config('app.debug') || env('DISABLE_PERMISSIONS', false))) {
            if (!$user->isSuperAdmin() && !$user->isClinicAdmin() && !$user->hasPermission('ai_assistant_access')) {
                abort(403, 'You do not have permission to access the AI Medical Assistant.');
            }
        }

        $accepted = AiDisclaimerAcceptance::where('user_id', $user->id)->exists();
        $messages = AiChatMessage::where('user_id', $user->id)
            ->orderBy('created_at')
            ->limit(100)
            ->get();

        // Get patients for dropdown
        $patients = Patient::where('clinic_id', $user->clinic_id)
            ->orderBy('first_name')
            ->select('id', 'patient_id', 'first_name', 'last_name')
            ->get();

        return view('assistant.index', [
            'messages' => $messages,
            'accepted' => $accepted,
            'locale' => app()->getLocale(),
            'patients' => $patients,
        ]);
    }

    public function acceptDisclaimer(Request $request)
    {
        $user = Auth::user();

        // Check permission for AI Assistant access
        if (!(config('app.debug') || env('DISABLE_PERMISSIONS', false))) {
            if (!$user->isSuperAdmin() && !$user->isClinicAdmin() && !$user->hasPermission('ai_assistant_access')) {
                abort(403, 'You do not have permission to access the AI Medical Assistant.');
            }
        }

        AiDisclaimerAcceptance::updateOrCreate(
            ['user_id' => $user->id],
            [
                'version' => 'v1',
                'locale' => app()->getLocale(),
                'accepted_at' => now(),
            ]
        );
        return redirect()->route('assistant.index')->with('success', __('Disclaimer accepted.'));
    }

    public function clearHistory(Request $request)
    {
        $user = Auth::user();

        // Check permission for AI Assistant access
        if (!(config('app.debug') || env('DISABLE_PERMISSIONS', false))) {
            if (!$user->isSuperAdmin() && !$user->isClinicAdmin() && !$user->hasPermission('ai_assistant_access')) {
                abort(403, 'You do not have permission to access the AI Medical Assistant.');
            }
        }

        AiChatMessage::where('user_id', $user->id)->delete();
        return back()->with('success', __('Chat history cleared.'));
    }

    public function send(Request $request)
    {
        Log::info('ASSISTANT_SEND_START', ['user' => Auth::id()]);

        $request->validate([
            'message' => 'required|string|max:2000',
            'patient_id' => 'nullable|integer|exists:patients,id',
        ]);

        $user = Auth::user();
        Log::info('ASSISTANT_USER_AUTH', ['user_id' => $user->id, 'email' => $user->email]);

        // Check permission for AI Assistant access
        if (!(config('app.debug') || env('DISABLE_PERMISSIONS', false))) {
            if (!$user->isSuperAdmin() && !$user->isClinicAdmin() && !$user->hasPermission('ai_assistant_access')) {
                abort(403, 'You do not have permission to access the AI Medical Assistant.');
            }
        }

        $accepted = AiDisclaimerAcceptance::where('user_id', $user->id)->exists();
        if (!$accepted) {
            return redirect()->route('assistant.index')->with('error', __('Please accept the disclaimer to use the assistant.'));
        }

        $userText = trim($request->input('message'));
        $locale = app()->getLocale();
        $patientId = $request->input('patient_id');

        // Detect question language (Arabic or English)
        $questionLang = $this->detectQuestionLanguage($userText);

        // Log the detection
        Log::info('Question language detected', [
            'user_id' => $user->id,
            'detected_lang' => $questionLang,
            'ui_locale' => $locale,
            'text_preview' => substr($userText, 0, 100)
        ]);

        // Store user message
        AiChatMessage::create([
            'user_id' => $user->id,
            'role' => 'user',
            'content' => $userText,
            'lang' => $questionLang,
            'patient_id' => $patientId,
        ]);

        // Use question language for AI response, fallback to user's locale
        $responseLang = $questionLang ?: $locale;

        // Pass the detected question language to the system prompt
        $assistantText = $this->callProvider($user, $responseLang, $patientId, $responseLang);

        // Store assistant message
        AiChatMessage::create([
            'user_id' => $user->id,
            'role' => 'assistant',
            'content' => $assistantText,
            'lang' => $locale,
            'patient_id' => $patientId,
        ]);

        return redirect()->route('assistant.index', ['_ts' => time()])->with('success', __('Response generated.'));
    }

    protected function callProvider($user, string $locale, ?int $patientId = null, ?string $detectedLanguage = null): string
    {
        $systemPrompt = $this->systemPrompt($locale, $patientId, $detectedLanguage);

        // Get last 12 message pairs (24 messages) for context
        $history = AiChatMessage::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(24)
            ->get()
            ->reverse()
            ->values();

        $messages = [];
        $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        foreach ($history as $m) {
            $messages[] = [
                'role' => $m->role === 'assistant' ? 'assistant' : 'user',
                'content' => $m->content,
            ];
        }

        $provider = config('ai.provider', 'openai');
        if ($provider === 'openai') {
            $apiKey = config('ai.openai.api_key') ?: env('OPENAI_API_KEY');
            $model = config('ai.openai.model', 'gpt-4o-mini') ?: env('OPENAI_MODEL', 'gpt-4o-mini');
            $baseUrl = config('ai.openai.base_url', 'https://api.openai.com/v1');
            if (!$apiKey) {
                return $this->noKeyFallback($locale);
            }
            try {
                $project = config('ai.openai.project') ?: env('OPENAI_PROJECT');
                $org = config('ai.openai.organization') ?: env('OPENAI_ORG');
                $headers = [];
                if ($project) { $headers['OpenAI-Project'] = $project; }
                if ($org) { $headers['OpenAI-Organization'] = $org; }

                $http = Http::timeout(30)->withToken($apiKey);
                if (!empty($headers)) {
                    $http = $http->withHeaders($headers);
                }

                $resp = $http->post($baseUrl . '/chat/completions', [
                    'model' => $model,
                    'temperature' => 0.2,
                    'max_tokens' => 700,
                    'messages' => $messages,
                ]);
                if ($resp->successful()) {
                    return (string) data_get($resp->json(), 'choices.0.message.content', $this->fallback($locale));
                }
                Log::warning('OpenAI chat/completions failed', [
                    'status' => $resp->status(),
                    'body' => $resp->body(),
                ]);
                return $this->fallback($locale);
            } catch (\Throwable $e) {
                Log::error('OpenAI request exception', [
                    'error' => $e->getMessage(),
                ]);
                return $this->fallback($locale);
            }
        }

        // Default fallback if provider not supported
        return $this->fallback($locale);
    }

    protected function systemPrompt(string $locale, ?int $patientId = null, ?string $detectedLanguage = null): string
    {
        // Determine the response language based on detected question language
        // If no language detected, use the UI locale
        $responseLanguage = $detectedLanguage ?: $locale;

        $responseLanguageInstruction = '';
        if ($responseLanguage === 'ar' || $responseLanguage === 'ku') {
            $responseLanguageInstruction = "\n**LANGUAGE INSTRUCTION: You MUST respond entirely in ARABIC (العربية), even if clinic data is in English. Use proper Arabic grammar and terminology.**";
        } else {
            $responseLanguageInstruction = "\n**LANGUAGE INSTRUCTION: You MUST respond entirely in ENGLISH. Use clear, professional medical English.**";
        }

        // Add clinic context with error handling
        $contextData = '';
        try {
            $contextData = AiDataService::prepareContextData(['include_stats' => true, 'include_diagnoses' => true]);
        } catch (\Exception $e) {
            Log::warning('Failed to load clinic context for AI: ' . $e->getMessage());
            $contextData = "### Clinic Context Data\nClinical context temporarily unavailable.\n\n";
        }

        // Add patient data if provided
        $patientContext = '';
        if ($patientId) {
            try {
                $patientData = AiDataService::getPatientSummary($patientId);
                $patientContext = "\n### Selected Patient Data\n";
                $patientContext .= "Name: {$patientData['name']}\n";
                $patientContext .= "Age: {$patientData['age']}, Gender: {$patientData['gender']}\n";
                $patientContext .= "Medical History: {$patientData['medical_history']}\n";
                $patientContext .= "Chronic Diseases: {$patientData['chronic_diseases']}\n";
                $patientContext .= "Allergies: {$patientData['allergies']}\n";
                $patientContext .= "Current Medications: {$patientData['current_medications']}\n";
            } catch (\Exception $e) {
                Log::warning('Failed to load patient data for AI: ' . $e->getMessage());
            }
        }

        $policyEn = <<<TXT
You are ConCure AI Assistant, an intelligent multilingual medical advisor integrated into the ConCure Clinic Management System.

PURPOSE & CAPABILITIES:
- Analyze patient medical histories and provide clinical insights
- Answer medical questions with evidence-based information
- Provide clinic statistics and analytics
- Support clinic operations (inventory, scheduling, finances)
- Deliver analysis in the doctor's preferred language (English/Arabic)
- Handle multilingual input (doctor may ask in Arabic while data is in English)$responseLanguageInstruction

RESPONSE LANGUAGE (CRITICAL):
- ALWAYS respond in the SAME LANGUAGE as the doctor's question
- If doctor asks in Arabic, respond ENTIRELY in Arabic (not English)
- If doctor asks in English, respond ENTIRELY in English (not Arabic)
- Do NOT mix languages in response unless the question itself is mixed
- EVEN IF the clinic data is in English, match the doctor's language

IMPORTANT GUIDELINES:
- You CAN analyze patient data that is securely provided in this context
- You MUST NOT provide definitive medical advice or diagnoses - recommend that patients consult licensed physicians
- Explain findings based on evidence (WHO, CDC, ESPEN, NIH guidelines)
- Keep answers professional, structured, and concise
- For patient analysis: summarize history, identify patterns, suggest follow-up areas
- NEVER request additional patient identifiers or private data
- ALWAYS include appropriate disclaimers for clinical recommendations
- Handle code-switching gracefully (mixed Arabic/English only if question is mixed)

PATIENT ANALYSIS EXAMPLES:
- "What conditions should we screen for given this patient's history?"
- "Are there medication interactions to be aware of?"
- "What's the follow-up plan based on this patient's conditions?"
- "Summarize this patient's medication allergies and interactions"

CLINIC ANALYTICS EXAMPLES:
- "What are our top diagnoses this month?"
- "How many appointments are pending?"
- "Which medicines need reordering?"
- "Show me appointment trends this quarter"

$contextData
$patientContext
TXT;

        if (in_array($locale, ['ar', 'ku'])) {
            $policyAr = <<<TXT
أنت "مساعد كونكيور الذكي"، مستشار طبي متعدد اللغات متكامل في نظام إدارة عيادات كونكيور.

الأهداف والقدرات:
- تحليل السجلات الطبية للمرضى وتقديم رؤى سريرية
- الإجابة على الأسئلة الطبية بمعلومات مستندة إلى أدلة علمية
- تقديم إحصائيات تحليلية للعيادة
- دعم العمليات السريرية (المخزون والجدولة والمالية)
- تقديم التحليلات باللغة المفضلة للمستخدم (الإنجليزية/العربية)

المبادئ التوجيهية المهمة:
- يمكنك تحليل بيانات المرضى التي يتم توفيرها بأمان في هذا السياق
- يجب عدم تقديم نصائح طبية نهائية - يوصي باستشارة الأطباء المرخصين
- شرح النتائج على أساس الأدلة (إرشادات WHO و CDC و ESPEN و NIH)
- اجعل الإجابات احترافية ومنظمة وموجزة
- لتحليل المريض: ملخص السجل الطبي وتحديد الأنماط واقتراح مجالات المتابعة
- لا تطلب أبدًا معرّفات إضافية أو بيانات خاصة أخرى
- قدم دائمًا إخلاءات مسؤولية مناسبة للتوصيات السريرية

$contextData
$patientContext
TXT;
            return $policyAr . "\n\n" . $policyEn;
        }

        return $policyEn;
    }

    protected function fallback(string $locale): string
    {
        return in_array($locale, ['ar', 'ku'])
            ? 'حدث خطأ مؤقت. الرجاء المحاولة لاحقًا. تذكير: هذه الأداة تعليمية ولا تُقدّم نصيحة طبية.'
            : 'Temporary issue. Please try again later. Reminder: this tool is educational and does not provide medical advice.';
    }

    protected function noKeyFallback(string $locale): string
    {
        return in_array($locale, ['ar', 'ku'])
            ? 'لم يتم ضبط مفتاح واجهة الذكاء الاصطناعي. الرجاء تزويد المفتاح في ملف البيئة (OPENAI_API_KEY).'
            : 'AI API key is not configured. Please set OPENAI_API_KEY in the environment.';
    }

    /**
     * Detect if the question is in Arabic or English
     * Returns 'ar' for Arabic, 'en' for English, null if mixed/unclear
     * Uses character counting with lenient threshold
     */
    protected function detectQuestionLanguage(string $text): ?string
    {
        if (empty($text)) {
            return null;
        }

        // Count Arabic and English characters
        $arabicCount = preg_match_all('/[\x{0600}-\x{06FF}]/u', $text);
        $englishCount = preg_match_all('/[a-zA-Z]/u', $text);

        Log::info('LANG_DETECT_START', [
            'text_sample' => substr($text, 0, 100),
            'arabic_count' => $arabicCount,
            'english_count' => $englishCount
        ]);

        // If both languages present, use weighted calculation
        if ($arabicCount > 0 && $englishCount > 0) {
            $total = $arabicCount + $englishCount;
            $arabicPercent = ($arabicCount / $total) * 100;

            // If one language is clearly dominant (>50%), use it
            if ($arabicPercent > 50) {
                Log::info('LANG_DETECT_RESULT: Arabic detected', ['percent' => $arabicPercent]);
                return 'ar';
            } else {
                Log::info('LANG_DETECT_RESULT: English detected', ['percent' => (100 - $arabicPercent)]);
                return 'en';
            }
        } elseif ($arabicCount > 0) {
            // Pure Arabic
            Log::info('LANG_DETECT_RESULT: Pure Arabic');
            return 'ar';
        } elseif ($englishCount > 0) {
            // Pure English
            Log::info('LANG_DETECT_RESULT: Pure English');
            return 'en';
        }

        // No recognized characters - return null to use locale
        Log::info('LANG_DETECT_RESULT: Could not detect, using null', ['text' => substr($text, 0, 50)]);
        return null;
    }
}

