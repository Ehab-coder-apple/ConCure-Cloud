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

        $query = Clinic::query()
            // Use withoutGlobalScopes() so master reports always see full system data,
            // regardless of the logged-in user's clinic visibility.
            ->withCount([
                'patients as total_patients' => function ($q) {
                    $q->withoutGlobalScopes();
                },
                'prescriptions as total_prescriptions' => function ($q) {
                    $q->withoutGlobalScopes();
                },
            ])
            ->withSum([
                'aestheticInvoices as total_revenue' => function ($q) {
                    $q->withoutGlobalScopes()
                        ->whereIn('status', ['paid', 'partial']);
                },
            ], 'total_amount')
            ->withMax([
                'users as last_login_at' => function ($q) {
                    $q->withoutGlobalScopes()->whereNotNull('last_login_at');
                },
            ], 'last_login_at')
            ->select('clinics.*');

        // Total images via subquery to avoid global scopes on SessionImage / AestheticSession
        $query->selectSub(function ($sub) {
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
