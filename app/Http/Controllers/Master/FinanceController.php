<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;
use App\Models\MasterExpense;
use App\Models\MasterExpenseCategory;
use App\Models\MasterInvoice;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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
        $recentExpenses = $this->getRecentExpenses();

        // Chart data for revenue trends
        $revenueChart = $this->getRevenueChartData($period, $from, $to);

        // Get the most used currency from actual payments
        $mostUsedCurrency = $this->getMostUsedCurrency();
        $currencySymbol = $this->getCurrencySymbol($mostUsedCurrency);

        // Built-in + custom expense categories for the Record Expense modal.
        $expenseCategories = MasterExpense::categoriesAll();
        $expensePaymentMethods = MasterExpense::PAYMENT_METHODS;

        return view('master.finance.index', compact(
            'period',
            'from',
            'to',
            'stats',
            'tenantStats',
            'recentInvoices',
            'recentReceipts',
            'recentExpenses',
            'revenueChart',
            'currencySymbol',
            'mostUsedCurrency',
            'expenseCategories',
            'expensePaymentMethods'
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

        // Operational SaaS expenses recorded by super-admin (IQD only).
        $totalExpenses = (float) MasterExpense::query()
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        // Net profit. Note: revenue can be multi-currency while expenses are
        // IQD-only by design -- the master finance dashboard already mixes
        // currencies for display, so we keep the same simple subtraction here.
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
     * Excludes demo clinic payments. Expenses come from master_expenses
     * (IQD-only platform operating costs).
     */
    private function getRevenueChartData(string $period, Carbon $from, Carbon $to): array
    {
        $labels = [];
        $revenueData = [];
        $expenseData = [];

        $sumExpenses = function ($start, $end) {
            return (float) DB::table('master_expenses')
                ->whereBetween('expense_date', [
                    $start instanceof Carbon ? $start->toDateString() : $start,
                    $end instanceof Carbon ? $end->toDateString() : $end,
                ])
                ->sum('amount');
        };

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
                    $expenseData[] = $sumExpenses($date, $date);
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
                    $expenseData[] = $sumExpenses($date, $weekEnd);
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
                    $expenseData[] = $sumExpenses($date, $monthEnd);
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
                $expenseData = [$sumExpenses($from, $to)];
        }

        return [
            'labels' => $labels,
            'revenue' => $revenueData,
            'expenses' => $expenseData,
        ];
    }

    /**
     * Recent platform-level expenses for the dashboard side panel.
     */
    private function getRecentExpenses()
    {
        return MasterExpense::with('creator:id,first_name,last_name')
            ->orderBy('expense_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Store new invoice.
     */
    public function storeInvoice(Request $request)
    {
        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'currency' => 'required|in:USD,IQD,JOD,EGP',
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
                'currency' => $request->currency,
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
            'currency' => 'required|in:USD,IQD,JOD,EGP',
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
                'currency' => $request->currency,
                'paid_at' => $request->paid_at,
                'method' => $request->payment_method,
                'notes' => $request->note, // Fixed: column name is 'notes' not 'note'
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
        try {
            $query = MasterInvoice::with('clinic')
                ->orderBy('invoice_date', 'desc');

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('clinic_id') && $request->clinic_id) {
                $query->where('clinic_id', $request->clinic_id);
            }

            $invoices = $query->paginate(20);

            // Get all clinics for filter
            $clinics = \App\Models\Clinic::where('is_active', true)
                ->orderBy('name')
                ->get();

            return view('master.finance.invoices', compact('invoices', 'clinics'));
        } catch (\Exception $e) {
            \Log::error('Error loading invoices page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to load invoices: ' . $e->getMessage());
        }
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

        $brandingLogoUrl = $this->resolveBrandingLogoUrl();

        return view('master.finance.invoice-print', compact('invoice', 'brandingLogoUrl'));
    }

    /**
     * Generate invoice PDF.
     */
    public function downloadInvoicePDF(MasterInvoice $invoice)
    {
        $invoice->load('clinic', 'items');

        $brandingLogoSrc = $this->resolveBrandingLogoDataUri();

        $pdf = Pdf::loadView('master.finance.invoice-pdf', compact('invoice', 'brandingLogoSrc'));

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Resolve the master branding logo as a public URL for HTML views.
     * Returns null when no logo is configured.
     */
    private function resolveBrandingLogoUrl(): ?string
    {
        $rel = \App\Http\Controllers\Master\SettingsController::getMasterBrandingLogoRelPath();
        return $rel ? asset($rel) : null;
    }

    /**
     * Resolve the master branding logo as a base64 data URI for DomPDF.
     * Prefers the PDF-safe (PNG/JPEG) variant. Returns null when unavailable.
     */
    private function resolveBrandingLogoDataUri(): ?string
    {
        $rel = \App\Http\Controllers\Master\SettingsController::getMasterBrandingLogoForPdfRelPath();
        if (!$rel) {
            return null;
        }

        $abs = public_path($rel);
        if (!file_exists($abs)) {
            return null;
        }

        $bytes = @file_get_contents($abs);
        if ($bytes === false) {
            return null;
        }

        $info = @getimagesize($abs);
        $mime = $info['mime'] ?? 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode($bytes);
    }

    /**
     * Update invoice.
     */
    public function updateInvoice(Request $request, MasterInvoice $invoice)
    {
        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'currency' => 'required|in:USD,IQD,JOD,EGP',
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
            $invoice->update([
                'clinic_id' => $request->clinic_id,
                'currency' => $request->currency,
                'due_date' => $request->due_date,
                'tax_rate' => $request->tax_rate ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'notes' => $request->notes,
            ]);

            // Delete existing items
            $invoice->items()->delete();

            // Add new items
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
                'message' => 'Invoice updated successfully',
                'invoice' => $invoice->load('clinic', 'items'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete invoice.
     */
    public function deleteInvoice(MasterInvoice $invoice)
    {
        DB::beginTransaction();
        try {
            $invoiceNumber = $invoice->invoice_number;
            $invoice->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Invoice {$invoiceNumber} deleted successfully",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Record payment for an invoice.
     */
    public function recordInvoicePayment(Request $request, MasterInvoice $invoice)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $amount = $request->amount;

            // Update invoice payment
            $invoice->paid_amount += $amount;
            $invoice->payment_method = $request->payment_method;
            $invoice->payment_date = $request->payment_date;
            $invoice->calculateTotals();
            $invoice->save();

            // Create a subscription payment record for dashboard tracking
            SubscriptionPayment::create([
                'clinic_id' => $invoice->clinic_id,
                'amount' => $amount,
                'currency' => $invoice->currency,
                'paid_at' => $request->payment_date,
                'method' => $request->payment_method,
                'reference' => 'Invoice: ' . $invoice->invoice_number,
                'notes' => 'Payment for invoice ' . $invoice->invoice_number,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'invoice' => $invoice->load('clinic', 'items'),
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
     * Update subscription payment.
     */
    public function updatePayment(Request $request, SubscriptionPayment $payment)
    {
        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'currency' => 'required|in:USD,IQD,JOD,EGP',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'paid_at' => 'required|date',
            'note' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $payment->update([
                'clinic_id' => $request->clinic_id,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'paid_at' => $request->paid_at,
                'method' => $request->payment_method,
                'notes' => $request->note, // Fixed: column name is 'notes' not 'note'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully',
                'payment' => $payment->load('clinic'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete subscription payment.
     */
    public function deletePayment(SubscriptionPayment $payment)
    {
        DB::beginTransaction();
        try {
            $amount = $payment->amount;
            $payment->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Payment of {$amount} deleted successfully",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new master (platform) expense. IQD-only by spec.
     */
    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'category' => ['required', 'string', Rule::in(array_keys(MasterExpense::categoriesAll()))],
            'new_category_label' => 'nullable|string|max:60',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'nullable|string|in:' . implode(',', array_keys(MasterExpense::PAYMENT_METHODS)),
            'notes' => 'nullable|string',
        ]);

        try {
            $data['category'] = $this->resolveCustomCategory(
                $data['category'],
                $request->input('new_category_label')
            );
            unset($data['new_category_label']);

            $expense = MasterExpense::create(array_merge($data, [
                'created_by' => Auth::id(),
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Expense recorded successfully',
                'expense' => $expense,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record expense: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing master expense.
     */
    public function updateExpense(Request $request, MasterExpense $expense)
    {
        $data = $request->validate([
            'category' => ['required', 'string', Rule::in(array_keys(MasterExpense::categoriesAll()))],
            'new_category_label' => 'nullable|string|max:60',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'nullable|string|in:' . implode(',', array_keys(MasterExpense::PAYMENT_METHODS)),
            'notes' => 'nullable|string',
        ]);

        try {
            $data['category'] = $this->resolveCustomCategory(
                $data['category'],
                $request->input('new_category_label')
            );
            unset($data['new_category_label']);

            $expense->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Expense updated successfully',
                'expense' => $expense->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update expense: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * If the operator picked "Other" and supplied a free-text label, persist it
     * as a MasterExpenseCategory and return its slug so the saved expense uses
     * the new key (not 'other'). Reuses an existing built-in slug when the
     * label collides with one (case-insensitive), to avoid silent duplication.
     */
    protected function resolveCustomCategory(string $category, ?string $newLabel): string
    {
        if ($category !== 'other') {
            return $category;
        }

        $label = trim((string) $newLabel);
        if ($label === '') {
            return 'other';
        }

        // Reuse a built-in slug when the label matches a built-in category.
        foreach (MasterExpense::CATEGORIES as $key => $builtInLabel) {
            if (strcasecmp($builtInLabel, $label) === 0) {
                return $key;
            }
        }

        $key = MasterExpenseCategory::makeKey($label);
        $existing = MasterExpenseCategory::where('key', $key)->first();
        if ($existing) {
            return $existing->key;
        }

        $row = MasterExpenseCategory::create([
            'key' => $key,
            'label' => $label,
            'created_by' => Auth::id(),
        ]);

        return $row->key;
    }

    /**
     * Delete a master expense.
     */
    public function deleteExpense(MasterExpense $expense)
    {
        try {
            $expense->delete();

            return response()->json([
                'success' => true,
                'message' => 'Expense deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete expense: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List master expenses with filters.
     */
    public function expenses(Request $request)
    {
        $query = MasterExpense::with('creator:id,first_name,last_name')
            ->orderBy('expense_date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('from')) {
            $query->whereDate('expense_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('expense_date', '<=', $request->to);
        }

        $totalForFilter = (float) MasterExpense::query()
            ->when($request->filled('category'), fn($q) => $q->where('category', $request->category))
            ->when($request->filled('from'), fn($q) => $q->whereDate('expense_date', '>=', $request->from))
            ->when($request->filled('to'), fn($q) => $q->whereDate('expense_date', '<=', $request->to))
            ->sum('amount');

        $expenses = $query->paginate(20)->withQueryString();

        return view('master.finance.expenses', [
            'expenses' => $expenses,
            'expenseCategories' => MasterExpense::categoriesAll(),
            'expensePaymentMethods' => MasterExpense::PAYMENT_METHODS,
            'totalForFilter' => $totalForFilter,
            'filters' => $request->only(['category', 'from', 'to']),
        ]);
    }

    /**
     * Get the most used currency from subscription payments.
     */
    private function getMostUsedCurrency(): string
    {
        $currency = DB::table('subscription_payments')
            ->join('clinics', 'subscription_payments.clinic_id', '=', 'clinics.id')
            ->where('clinics.is_demo', false)
            ->select('subscription_payments.currency', DB::raw('COUNT(*) as count'))
            ->groupBy('subscription_payments.currency')
            ->orderBy('count', 'desc')
            ->value('currency');

        return $currency ?? 'USD';
    }

    /**
     * Get currency symbol from currency code.
     */
    private function getCurrencySymbol(string $currency): string
    {
        $symbols = [
            'USD' => '$',
            'IQD' => 'IQD',
            'JOD' => 'JD',
            'EGP' => 'EGP',
            'EUR' => '€',
            'GBP' => '£',
        ];

        return $symbols[$currency] ?? $currency;
    }
}

