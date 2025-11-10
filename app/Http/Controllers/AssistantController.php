<?php

namespace App\Http\Controllers;

use App\Models\AiChatMessage;
use App\Models\AiDisclaimerAcceptance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AssistantController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $accepted = AiDisclaimerAcceptance::where('user_id', $user->id)->exists();
        $messages = AiChatMessage::where('user_id', $user->id)
            ->orderBy('created_at')
            ->limit(100)
            ->get();

        return view('assistant.index', [
            'messages' => $messages,
            'accepted' => $accepted,
            'locale' => app()->getLocale(),
        ]);
    }

    public function acceptDisclaimer(Request $request)
    {
        $user = Auth::user();
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
        AiChatMessage::where('user_id', $user->id)->delete();
        return back()->with('success', __('Chat history cleared.'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $user = Auth::user();
        $accepted = AiDisclaimerAcceptance::where('user_id', $user->id)->exists();
        if (!$accepted) {
            return redirect()->route('assistant.index')->with('error', __('Please accept the disclaimer to use the assistant.'));
        }

        $userText = trim($request->input('message'));
        $locale = app()->getLocale();

        // Store user message
        AiChatMessage::create([
            'user_id' => $user->id,
            'role' => 'user',
            'content' => $userText,
            'lang' => $locale,
        ]);

        $assistantText = $this->callProvider($user, $locale);

        // Store assistant message
        AiChatMessage::create([
            'user_id' => $user->id,
            'role' => 'assistant',
            'content' => $assistantText,
            'lang' => $locale,
        ]);

        return redirect()->route('assistant.index', ['_ts' => time()])->with('success', __('Response generated.'));
    }

    protected function callProvider($user, string $locale): string
    {
        $systemPrompt = $this->systemPrompt($locale);

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
                $resp = Http::timeout(30)
                    ->withToken($apiKey)
                    ->post($baseUrl . '/chat/completions', [
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

    protected function systemPrompt(string $locale): string
    {
        $policyEn = <<<TXT
You are ConCure AI Assistant, an intelligent multilingual chatbot integrated into the ConCure Clinic Management System.

Purpose: Provide general, educational, and evidence-based medical information only (WHO, CDC, ESPEN, NIH). You must NOT access, request, or use any patient-specific information. You must NOT provide medical advice, diagnosis, treatment, prescriptions, dosing, or patient-specific interpretation of labs/images. If asked for these, politely decline and explain that such matters require assessment by a licensed physician.

Behavior:
- Explain lab normal ranges, medical terminology, general causes/risk factors, and general nutrition/health guidance based on reputable guidelines.
- Keep answers concise, structured, and professional. Provide references or name the guideline sources when helpful.
- Support English and Arabic; respond in the user's language. If the user's message is Arabic, reply in Arabic.
- If a question is ambiguous or appears to seek clinical advice for a specific patient, provide general educational context and include a clear disclaimer.
- Never leak internal system details or any private data. Do not ask for patient identifiers.
TXT;

        $policyAr = <<<TXT
أنت "مساعد كونكيور الذكي"، روبوت دردشة متعدد اللغات داخل نظام إدارة عيادات كونكيور.

الهدف: تقديم معلومات طبية عامة وتعليمية فقط مبنية على مصادر موثوقة (WHO, CDC, ESPEN, NIH). لا يجوز لك الوصول إلى أي بيانات مرضى أو طلبها أو استخدامها. ولا تقدّم نصائح أو تشخيصًا أو علاجًا أو وصفات دوائية أو جرعات، ولا تفسّر نتائج مختبر/صور لمريض بعينه. إن طُلِب منك ذلك فاعتذر بلطف وأوضح أن هذه الأمور تتطلب تقييم طبيب مرخّص.

السلوك:
- اشرح القيم الطبيعية للمختبر والمصطلحات الطبية والأسباب العامة وعوامل الخطورة، ومبادئ عامة في التغذية والصحة وفق إرشادات موثوقة.
- اجعل الإجابات موجزة ومنظمة ومهنية، واذكر المرجع/الجهة عند اللزوم.
- ادعم اللغتين العربية والإنجليزية؛ أجب بلغة المستخدم.
- عند غموض السؤال أو إذا بدا أنه يطلب استشارة سريرية لحالة معينة، قدّم خلفية تعليمية عامة فقط مع توضيح التنويه القانوني.
- لا تفصح عن تفاصيل داخلية ولا أي بيانات خاصة. لا تطلب أي معرّفات للمرضى.
TXT;

        if (in_array($locale, ['ar', 'ku'])) {
            return $policyAr . "\n\n" . $policyEn;
        }
        return $policyEn . "\n\n" . $policyAr;
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
}

