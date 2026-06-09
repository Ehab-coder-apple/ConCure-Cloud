<?php

namespace App\Http\Controllers;

use App\Models\ClinicContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContractController extends Controller
{
    /**
     * Show the contract acceptance page.
     */
    public function show()
    {
        $user = auth()->user();
        $clinic = $user->clinic;

        if (!$clinic) {
            abort(403, 'You are not associated with any clinic.');
        }

        $contract = $clinic->activeContract()
            ->where('status', 'pending')
            ->firstOrFail();

        // Replace placeholders in contract content with actual clinic data
        $contractContent = $this->replacePlaceholders($contract->contract_content, $clinic);

        return view('contract.show', compact('contract', 'clinic', 'contractContent'));
    }

    /**
     * Accept the contract.
     */
    public function accept(Request $request)
    {
        $validated = $request->validate([
            'signature_name' => 'required|string|max:255',
            'agree' => 'required',
        ], [
            'signature_name.required' => 'Please type your full name to sign the contract.',
            'agree.required' => 'You must agree to the terms and conditions.',
        ]);

        $user = auth()->user();
        $clinic = $user->clinic;

        if (!$clinic) {
            return back()->withErrors(['error' => 'You are not associated with any clinic.']);
        }

        try {
            $contract = $clinic->activeContract()
                ->where('status', 'pending')
                ->firstOrFail();
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'No pending contract found for your clinic.']);
        }

        try {
            DB::transaction(function () use ($contract, $user, $validated, $request) {
                $contract->accept(
                    $user,
                    $validated['signature_name'],
                    $request->ip()
                );
            });

            $this->storeAcceptanceNotification($contract, $clinic, $user);

            return redirect()->route('dashboard')
                ->with('success', 'Contract accepted successfully! Welcome to ConCure Cloud.');

        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Failed to accept contract: ' . $e->getMessage()]);
        }
    }

    /**
     * Store a database notification for the contract creator.
     */
    private function storeAcceptanceNotification(ClinicContract $contract, $clinic, $user): void
    {
        if (!$contract->created_by) {
            return;
        }

        try {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\ContractAccepted',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $contract->created_by,
                'data' => json_encode([
                    'clinic_id' => $clinic->id,
                    'clinic_name' => $clinic->name,
                    'contract_id' => $contract->id,
                    'accepted_by' => $user->full_name,
                    'message' => $clinic->name . ' has accepted the service contract.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Failed to store contract acceptance notification.', [
                'contract_id' => $contract->id,
                'created_by' => $contract->created_by,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * View accepted contract (for clinic users).
     */
    public function view()
    {
        $user = auth()->user();
        $clinic = $user->clinic;

        if (!$clinic) {
            abort(403, 'You are not associated with any clinic.');
        }

        $contract = $clinic->activeContract()
            ->where('status', 'accepted')
            ->firstOrFail();

        // Replace placeholders in contract content with actual clinic data
        $contractContent = $this->replacePlaceholders($contract->contract_content, $clinic);

        return view('contract.view', compact('contract', 'clinic', 'contractContent'));
    }

    /**
     * Replace placeholders in contract content with actual clinic data.
     */
    private function replacePlaceholders(string $content, $clinic): string
    {
        $replacements = [
            '[Clinic Name]' => $clinic->name,
            '[CLINIC_NAME]' => $clinic->name,
            '[Clinic Email]' => $clinic->email ?? 'N/A',
            '[CLINIC_EMAIL]' => $clinic->email ?? 'N/A',
            '[Clinic Phone]' => $clinic->phone ?? 'N/A',
            '[CLINIC_PHONE]' => $clinic->phone ?? 'N/A',
            '[Clinic Address]' => $clinic->address ?? 'N/A',
            '[CLINIC_ADDRESS]' => $clinic->address ?? 'N/A',
            '[Date]' => now()->format('Y-m-d'),
            '[DATE]' => now()->format('Y-m-d'),
            '[Year]' => now()->format('Y'),
            '[YEAR]' => now()->format('Y'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
}
