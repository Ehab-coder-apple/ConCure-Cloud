<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClinicReportController extends Controller
{
    /**
     * Display aggregated clinic activity/performance metrics.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort', 'name');
        $direction = strtolower($request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Use raw subqueries against the underlying tables so the report always
        // reflects the true system-wide data, completely independent of any
        // model-level global scopes or auth context.
        $query = Clinic::query()
            ->select('clinics.*')
            // Total patients per clinic
            ->selectSub(function ($sub) {
                $sub->from('patients')
                    ->whereColumn('patients.clinic_id', 'clinics.id')
                    ->selectRaw('COUNT(*)');
            }, 'total_patients')
            // Total prescriptions per clinic
            ->selectSub(function ($sub) {
                $sub->from('prescriptions')
                    ->whereColumn('prescriptions.clinic_id', 'clinics.id')
                    ->selectRaw('COUNT(*)');
            }, 'total_prescriptions')
            // Total aesthetic revenue per clinic (paid or partial invoices)
            ->selectSub(function ($sub) {
                $sub->from('aesthetic_invoices')
                    ->whereIn('status', ['paid', 'partial'])
                    ->whereColumn('aesthetic_invoices.clinic_id', 'clinics.id')
                    ->selectRaw('COALESCE(SUM(total_amount), 0)');
            }, 'total_revenue')
            // Last login timestamp among clinic users
            ->selectSub(function ($sub) {
                $sub->from('users')
                    ->whereColumn('users.clinic_id', 'clinics.id')
                    ->selectRaw('MAX(last_login_at)');
            }, 'last_login_at')
            // Total images from aesthetic sessions mapped by tenant ID
            ->selectSub(function ($sub) {
                $sub->from('session_images')
                    ->join('aesthetic_sessions', 'session_images.session_id', '=', 'aesthetic_sessions.id')
                    ->whereNull('aesthetic_sessions.deleted_at')
                    ->whereColumn('aesthetic_sessions.tenant_id', 'clinics.tenant_id')
                    ->selectRaw('COUNT(*)');
            }, 'total_images');

        if ($search !== '') {
            $query->where('clinics.name', 'like', '%' . $search . '%');
        }

        $sortable = [
            'name' => 'clinics.name',
            'patients' => 'total_patients',
            'images' => 'total_images',
            'prescriptions' => 'total_prescriptions',
            'revenue' => 'total_revenue',
            'last_login' => 'last_login_at',
        ];

        if (!array_key_exists($sort, $sortable)) {
            $sort = 'name';
        }

        $query->orderBy($sortable[$sort], $direction)
              ->orderBy('clinics.name');

        $clinics = $query->paginate(25)->withQueryString();

        return view('master.reports.clinics', [
            'clinics' => $clinics,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }
}
