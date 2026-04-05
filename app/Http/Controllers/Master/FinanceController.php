<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;
use App\Models\MasterInvoice;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

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
     * Excludes demo clinics - only counts paying tenants.
     */
    private function getFinancialStats(Carbon $from, Carbon $to): array
    {
        // Get clinic payments (subscription payments) - Exclude demo clinics
        $totalRevenue = DB::table('subscription_payments')
            ->join('clinics', 'subscription_payments.clinic_id', '=', 'clinics.id')
            ->where('clinics.is_demo', false)
            ->whereBetween('subscription_payments.paid_at', [$from, $to])
            ->sum('subscription_payments.amount');

        $paymentCount = DB::table('subscription_payments')
            ->join('clinics', 'subscription_payments.clinic_id', '=', 'clinics.id')
            ->where('clinics.is_demo', false)
            ->whereBetween('subscription_payments.paid_at', [$from, $to])
            ->count();

        // Calculate expected revenue from active subscriptions (excluding demo)
        $expectedRevenue = $this->calculateExpectedRevenue($from, $to);

        // Get service charges (excluding demo clinics)
        $serviceCharges = Clinic::where('is_demo', false)
            ->whereBetween('service_charge_date', [$from, $to])
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
     * Excludes demo clinics - only counts paying tenants.
     */
    private function calculateExpectedRevenue(Carbon $from, Carbon $to): float
    {
        $activeClinics = Clinic::where('is_active', true)
            ->where('is_demo', false) // Exclude demo clinics
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
     * Separates demo clinics from paying tenants.
     */
    private function getTenantStats(Carbon $from, Carbon $to): array
    {
        // Paying tenants (excluding demos)
        $totalTenants = Clinic::where('is_demo', false)->count();
        $activeTenants = Clinic::where('is_active', true)->where('is_demo', false)->count();
        $inactiveTenants = Clinic::where('is_active', false)->where('is_demo', false)->count();

        // Demo clinics (separate count)
        $demoTenants = Clinic::where('is_demo', true)->count();
        $activeDemos = Clinic::where('is_demo', true)->where('is_active', true)->count();

        // New paying tenants in period (excluding demos)
        $newTenants = Clinic::where('is_demo', false)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        // New demo clinics in period
        $newDemos = Clinic::where('is_demo', true)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        // Total users across all clinics (excluding demo clinic users)
        $totalUsers = User::whereNotNull('clinic_id')
            ->whereHas('clinic', function($query) {
                $query->where('is_demo', false);
            })
            ->count();

        $activeUsers = User::whereNotNull('clinic_id')
            ->where('is_active', true)
            ->whereHas('clinic', function($query) {
                $query->where('is_demo', false);
            })
            ->count();

        // Users by role distribution (paying tenants only)
        $usersByRole = User::whereNotNull('clinic_id')
            ->whereHas('clinic', function($query) {
                $query->where('is_demo', false);
            })
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        return [
            'total_tenants' => $totalTenants,
            'active_tenants' => $activeTenants,
            'inactive_tenants' => $inactiveTenants,
            'demo_tenants' => $demoTenants,
            'active_demos' => $activeDemos,
            'new_tenants' => $newTenants,
            'new_demos' => $newDemos,
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
     * Excludes demo clinic payments.
     */
    private function getRecentReceipts()
    {
        return DB::table('subscription_payments')
            ->join('clinics', 'subscription_payments.clinic_id', '=', 'clinics.id')
            ->where('clinics.is_demo', false) // Exclude demo clinics
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
     * Excludes demo clinic payments.
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
                        ->join('clinics', 'subscription_payments.clinic_id', '=', 'clinics.id')
                        ->where('clinics.is_demo', false)
                        ->whereDate('subscription_payments.paid_at', $date)
                        ->sum('subscription_payments.amount');
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
                        ->join('clinics', 'subscription_payments.clinic_id', '=', 'clinics.id')
                        ->where('clinics.is_demo', false)
                        ->whereBetween('subscription_payments.paid_at', [$date, $weekEnd])
                        ->sum('subscription_payments.amount');
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
                        ->join('clinics', 'subscription_payments.clinic_id', '=', 'clinics.id')
                        ->where('clinics.is_demo', false)
                        ->whereBetween('subscription_payments.paid_at', [$date, $monthEnd])
                        ->sum('subscription_payments.amount');
                    $expenseData[] = 0; // Placeholder
                }
                break;

            default:
                // Monthly breakdown for custom period
                $labels = ['Period Total'];
                $revenueData = [DB::table('subscription_payments')
                    ->join('clinics', 'subscription_payments.clinic_id', '=', 'clinics.id')
                    ->where('clinics.is_demo', false)
                    ->whereBetween('subscription_payments.paid_at', [$from, $to])
                    ->sum('subscription_payments.amount')];
                $expenseData = [0];
        }

        return [
            'labels' => $labels,
            'revenue' => $revenueData,
            'expenses' => $expenseData,
        ];
    }

    /**
     * Store new invoice.
     */
    public function storeInvoice(Request $request)
    {
        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'due_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $invoice = MasterInvoice::create([
                'clinic_id' => $request->clinic_id,
                'invoice_date' => now(),
                'due_date' => $request->due_date,
                'tax_rate' => $request->tax_rate ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'status' => 'sent',
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            // Add items
            foreach ($request->items as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            // Refresh to recalculate totals
            $invoice->refresh();
            $invoice->calculateTotals();
            $invoice->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'invoice' => $invoice->load('clinic', 'items'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Record payment.
     */
    public function recordPayment(Request $request)
    {
        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'paid_at' => 'required|date',
            'note' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $payment = SubscriptionPayment::create([
                'clinic_id' => $request->clinic_id,
                'amount' => $request->amount,
                'paid_at' => $request->paid_at,
                'note' => $request->note,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'payment' => $payment->load('clinic'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get invoices list.
     */
    public function invoices(Request $request)
    {
        $query = MasterInvoice::with('clinic')
            ->orderBy('invoice_date', 'desc');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('clinic_id') && $request->clinic_id) {
            $query->where('clinic_id', $request->clinic_id);
        }

        $invoices = $query->paginate(20);

        return view('master.finance.invoices', compact('invoices'));
    }

    /**
     * Show invoice details.
     */
    public function showInvoice(MasterInvoice $invoice)
    {
        $invoice->load('clinic', 'items', 'creator');
        return view('master.finance.invoice-show', compact('invoice'));
    }

    /**
     * Print invoice.
     */
    public function printInvoice(MasterInvoice $invoice)
    {
        $invoice->load('clinic', 'items');
        $currencySymbol = config('concure.currency_symbol', '$');

        return view('master.finance.invoice-print', compact('invoice', 'currencySymbol'));
    }

    /**
     * Generate invoice PDF.
     */
    public function downloadInvoicePDF(MasterInvoice $invoice)
    {
        $invoice->load('clinic', 'items');
        $currencySymbol = config('concure.currency_symbol', '$');

        $pdf = Pdf::loadView('master.finance.invoice-pdf', compact('invoice', 'currencySymbol'));

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}

