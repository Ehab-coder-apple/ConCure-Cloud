<?php

namespace App\Http\Controllers;

use App\Models\ClinicContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return view('contract.show', compact('contract', 'clinic'));
    }

    /**
     * Accept the contract.
     */
    public function accept(Request $request)
    {
        $request->validate([
            'signature_name' => 'required|string|max:255',
            'agree' => 'required|accepted',
        ], [
            'signature_name.required' => 'Please type your full name to sign the contract.',
            'agree.required' => 'You must agree to the terms and conditions.',
            'agree.accepted' => 'You must agree to the terms and conditions.',
        ]);

        $user = auth()->user();
        $clinic = $user->clinic;

        if (!$clinic) {
            return back()->withErrors(['error' => 'You are not associated with any clinic.']);
        }

        $contract = $clinic->activeContract()
            ->where('status', 'pending')
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Accept the contract
            $contract->accept(
                $user,
                $request->signature_name,
                $request->ip()
            );

            // Create a system notification for master admin
            DB::table('notifications')->insert([
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

            DB::commit();

            return redirect()->route('dashboard')
                ->with('success', 'Contract accepted successfully! Welcome to ConCure Cloud.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to accept contract: ' . $e->getMessage()]);
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

        return view('contract.view', compact('contract', 'clinic'));
    }
}
