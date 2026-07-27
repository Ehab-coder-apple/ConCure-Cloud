<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubscriptionPayment;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;

class PaymentsController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'amount' => 'required|numeric|min:0.01',
            'paid_at' => 'nullable|date',
            'method' => 'nullable|string|max:100',
            'reference' => 'nullable|string|max:191',
            'notes' => 'nullable|string',
        ]);

        $data['paid_at'] = $data['paid_at'] ?? now()->toDateString();
        SubscriptionPayment::create($data);

        return back()->with('success', 'Subscription payment recorded.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('error', 'Unable to open CSV file.');
        }

        $header = null;
        $imported = 0; $skipped = 0; $rowNum = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $rowNum++;
                if ($rowNum === 1) { $header = array_map('trim', $row); continue; }
                if (!$header) { continue; }
                $data = array_combine($header, $row);
                if (!$data) { $skipped++; continue; }

                // Expected headers: clinic_id, paid_at, amount, method, reference, notes
                if (empty($data['clinic_id']) || empty($data['amount'])) { $skipped++; continue; }
                if (!Clinic::where('id', $data['clinic_id'])->exists()) { $skipped++; continue; }

                SubscriptionPayment::create([
                    'clinic_id' => (int) $data['clinic_id'],
                    'amount' => (float) $data['amount'],
                    'paid_at' => !empty($data['paid_at']) ? $data['paid_at'] : now()->toDateString(),
                    'method' => $data['method'] ?? null,
                    'reference' => $data['reference'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);
                $imported++;
            }
            fclose($handle);
            DB::commit();
        } catch (\Throwable $e) {
            if (is_resource($handle)) fclose($handle);
            DB::rollBack();
            return back()->with('error', 'Import failed: '.$e->getMessage());
        }

        return back()->with('success', "Imported {$imported} rows, skipped {$skipped}.");
    }
}

