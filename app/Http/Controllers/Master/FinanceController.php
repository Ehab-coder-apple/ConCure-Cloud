<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceController extends Controller
{
    /**
     * Display financial dashboard with time period filters.
     */
    public function index(Request $request)
    {
        // Get period filter (default: month)
        $period = $request->get('period', 'month');
        $customFrom = $request->get('from');
        $customTo = $request->get('to');

        // Calculate date range based on period
        $dateRange = $this->getDateRange($period, $customFrom, $customTo);
        $from = $dateRange['from'];
        $to = $dateRange['to'];

        // Get financial statistics
        $stats = $this->getFinancialStats($from, $to);

        // Get tenant and user statistics
        $tenantStats = $this->getTenantStats($from, $to);

        // Get recent invoices and receipts
        $recentInvoices = $this->getRecentInvoices();
        $recentReceipts = $this->getRecentReceipts();

        // Chart data for revenue trends
        $revenueChart = $this->getRevenueChartData($period, $from, $to);

        $currencySymbol = config('concure.currency_symbol', '$');

        return view('master.finance.index', compact(
            'period',
            'from',
            'to',
            'stats',
            'tenantStats',
            'recentInvoices',
            'recentReceipts',
            'revenueChart',
            'currencySymbol'
        ));
    }

    /**
     * Get date range based on period selection.
     */
    private function getDateRange(string $period, $customFrom = null, $customTo = null): array
    {
        $now = Carbon::now();

        switch ($period) {
            case 'today':
                return ['from' => $now->copy()->startOfDay(), 'to' => $now->copy()->endOfDay()];
            
            case 'week':
                return ['from' => $now->copy()->startOfWeek(), 'to' => $now->copy()->endOfWeek()];
            
            case 'month':
                return ['from' => $now->copy()->startOfMonth(), 'to' => $now->copy()->endOfMonth()];
            
            case 'quarter':
                return ['from' => $now->copy()->startOfQuarter(), 'to' => $now->copy()->endOfQuarter()];
            
            case 'semester':
                // First semester: Jan-Jun, Second semester: Jul-Dec
                $month = $now->month;
                if ($month <= 6) {
                    return [
                        'from' => Carbon::create($now->year, 1, 1)->startOfDay(),
                        'to' => Carbon::create($now->year, 6, 30)->endOfDay()
                    ];
                } else {
                    return [
                        'from' => Carbon::create($now->year, 7, 1)->startOfDay(),
                        'to' => Carbon::create($now->year, 12, 31)->endOfDay()
                    ];
                }
            
            case 'year':
                return ['from' => $now->copy()->startOfYear(), 'to' => $now->copy()->endOfYear()];
            
            case 'custom':
                if ($customFrom && $customTo) {
                    return [
                        'from' => Carbon::parse($customFrom)->startOfDay(),
                        'to' => Carbon::parse($customTo)->endOfDay()
                    ];
                }
                // Fallback to month if custom dates not provided
                return ['from' => $now->copy()->startOfMonth(), 'to' => $now->copy()->endOfMonth()];
            
            default:
                return ['from' => $now->copy()->startOfMonth(), 'to' => $now->copy()->endOfMonth()];
        }
    }

    /**
     * Get financial statistics for the period.
     */
    private function getFinancialStats(Carbon $from, Carbon $to): array
    {
        // Get clinic payments (subscription payments)
        $paymentsQuery = DB::table('subscription_payments')
            ->whereBetween('paid_at', [$from, $to]);

        $totalRevenue = $paymentsQuery->sum('amount');
        $paymentCount = DB::table('subscription_payments')
            ->whereBetween('paid_at', [$from, $to])
            ->count();

        // Calculate expected revenue from active subscriptions
        $expectedRevenue = $this->calculateExpectedRevenue($from, $to);

        // Get service charges
        $serviceCharges = Clinic::whereBetween('service_charge_date', [$from, $to])
            ->sum('service_charge_amount');

        // Calculate expenses (operational costs - if tracked)
        $totalExpenses = 0; // Placeholder for future expense tracking

        // Net profit
        $netProfit = $totalRevenue - $totalExpenses;

        return [
            'total_revenue' => $totalRevenue,
            'expected_revenue' => $expectedRevenue,
            'service_charges' => $serviceCharges,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'payment_count' => $paymentCount,
            'collection_rate' => $expectedRevenue > 0 ? ($totalRevenue / $expectedRevenue) * 100 : 0,
        ];
    }

    /**
     * Calculate expected revenue from active subscriptions.
     */
    private function calculateExpectedRevenue(Carbon $from, Carbon $to): float
    {
        $activeClinics = Clinic::where('is_active', true)
            ->whereNotNull('plan_id')
            ->get();

        $totalExpected = 0;
        $monthsInPeriod = $from->diffInMonths($to) + 1;

        foreach ($activeClinics as $clinic) {
            $monthlyFee = $clinic->custom_monthly_price ?? $clinic->plan->monthly_price ?? 0;
            $totalExpected += $monthlyFee * $monthsInPeriod;
        }

        return $totalExpected;
    }

    /**
     * Get tenant statistics.
     */
    private function getTenantStats(Carbon $from, Carbon $to): array
    {
        $totalTenants = Clinic::count();
        $activeTenants = Clinic::where('is_active', true)->count();
        $inactiveTenants = Clinic::where('is_active', false)->count();
        $demoTenants = Clinic::where('is_demo', true)->count();

        // New tenants in period
        $newTenants = Clinic::whereBetween('created_at', [$from, $to])->count();

        // Total users across all clinics
        $totalUsers = User::whereNotNull('clinic_id')->count();
        $activeUsers = User::whereNotNull('clinic_id')
            ->where('is_active', true)
            ->count();

        // Users by role distribution
        $usersByRole = User::whereNotNull('clinic_id')
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        return [
            'total_tenants' => $totalTenants,
            'active_tenants' => $activeTenants,
            'inactive_tenants' => $inactiveTenants,
            'demo_tenants' => $demoTenants,
            'new_tenants' => $newTenants,
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'users_by_role' => $usersByRole,
        ];
    }

    /**
     * Get recent invoices.
     */
    private function getRecentInvoices()
    {
        return collect(); // Placeholder - will implement master invoices
    }

    /**
     * Get recent receipts/payments.
     */
    private function getRecentReceipts()
    {
        return DB::table('subscription_payments')
            ->join('clinics', 'subscription_payments.clinic_id', '=', 'clinics.id')
            ->select(
                'subscription_payments.*',
                'clinics.name as clinic_name'
            )
            ->orderBy('subscription_payments.paid_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get revenue chart data.
     */
    private function getRevenueChartData(string $period, Carbon $from, Carbon $to): array
    {
        $labels = [];
        $revenueData = [];
        $expenseData = [];

        switch ($period) {
            case 'week':
            case 'today':
                // Daily breakdown
                for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                    $labels[] = $date->format('M d');
                    $revenueData[] = DB::table('subscription_payments')
                        ->whereDate('paid_at', $date)
                        ->sum('amount');
                    $expenseData[] = 0; // Placeholder
                }
                break;

            case 'month':
            case 'quarter':
                // Weekly breakdown
                for ($date = $from->copy(); $date->lte($to); $date->addWeek()) {
                    $weekEnd = $date->copy()->endOfWeek()->min($to);
                    $labels[] = 'Week ' . $date->weekOfYear;
                    $revenueData[] = DB::table('subscription_payments')
                        ->whereBetween('paid_at', [$date, $weekEnd])
                        ->sum('amount');
                    $expenseData[] = 0; // Placeholder
                }
                break;

            case 'semester':
            case 'year':
                // Monthly breakdown
                for ($date = $from->copy(); $date->lte($to); $date->addMonth()) {
                    $monthEnd = $date->copy()->endOfMonth()->min($to);
                    $labels[] = $date->format('M Y');
                    $revenueData[] = DB::table('subscription_payments')
                        ->whereBetween('paid_at', [$date, $monthEnd])
                        ->sum('amount');
                    $expenseData[] = 0; // Placeholder
                }
                break;

            default:
                // Monthly breakdown for custom period
                $labels = ['Period Total'];
                $revenueData = [DB::table('subscription_payments')
                    ->whereBetween('paid_at', [$from, $to])
                    ->sum('amount')];
                $expenseData = [0];
        }

        return [
            'labels' => $labels,
            'revenue' => $revenueData,
            'expenses' => $expenseData,
        ];
    }
}
