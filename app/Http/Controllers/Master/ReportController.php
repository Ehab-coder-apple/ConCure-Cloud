<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Clinic;
use App\Models\User;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Master Reports index with filters and summary data
     */
    public function index(Request $request)
    {
        $from = $this->parseDate($request->query('from'));
        $to   = $this->parseDate($request->query('to'));

        // Clinics summary
        $clinicsBase = Clinic::query();
        if ($from) { $clinicsBase->whereDate('created_at', '>=', $from->toDateString()); }
        if ($to)   { $clinicsBase->whereDate('created_at', '<=', $to->toDateString()); }

        $clinicsTotal    = (clone $clinicsBase)->count();
        $clinicsActive   = (clone $clinicsBase)->where('is_active', true)->count();
        $clinicsInactive = (clone $clinicsBase)->where('is_active', false)->count();

        // Users by role (exclude super_admin)
        $usersBase = User::where('role', '!=', 'super_admin');
        if ($from) { $usersBase->whereDate('created_at', '>=', $from->toDateString()); }
        if ($to)   { $usersBase->whereDate('created_at', '<=', $to->toDateString()); }

        $usersByRole = $usersBase
            ->select('role', DB::raw('COUNT(*) as count'))
            ->groupBy('role')
            ->orderBy('role')
            ->pluck('count', 'role')
            ->toArray();

        // Financials (master subscriptions)
        $currencySymbol = config('concure.currency_symbol', '$');
        $activeSubscribers = Clinic::where('is_active', true)->count();
        $monthlyFee = (float) config('concure.subscription.monthly_fee', 29);
        $expectedMonthlyFees = $activeSubscribers * $monthlyFee;
        // Collected amount placeholder: no subscription payment records yet
        $collectedAmount = 0.0;

        $filters = [
            'from' => $from?->toDateString(),
            'to'   => $to?->toDateString(),
        ];

        return view('master.reports.index', compact(
            'filters',
            'clinicsTotal', 'clinicsActive', 'clinicsInactive',
            'usersByRole',
            'currencySymbol', 'activeSubscribers', 'monthlyFee', 'expectedMonthlyFees', 'collectedAmount'
        ));
    }

    private function parseDate($value): ?Carbon
    {
        if (!$value) return null;
        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}

