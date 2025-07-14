<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Expense;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class FinanceController extends Controller
{
    /**
     * Display the finance dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        
        if (!$user->canAccessFinance()) {
            abort(403, 'Access denied to finance module.');
        }

        // Get financial statistics
        $stats = $this->getFinancialStats($user);
        
        return view('finance.index', $stats);
    }

    /**
     * Display invoices.
     */
    public function invoices(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->canAccessFinance()) {
            abort(403, 'Access denied to invoices.');
        }

        $query = Invoice::with(['patient', 'clinic', 'creator']);

        // Filter by clinic for all users
        $query->where('clinic_id', $user->clinic_id);

        // Apply filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('patient_id', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        $invoices = $query->latest()->paginate(15);

        return view('finance.invoices', compact('invoices'));
    }

    /**
     * Store a new invoice.
     */
    public function storeInvoice(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->canAccessFinance()) {
            abort(403, 'Access denied to create invoices.');
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'due_date' => 'nullable|date|after:today',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.item_type' => 'required|in:consultation,procedure,medication,lab_test,other',
        ]);

        DB::transaction(function () use ($request, $user) {
            $invoice = Invoice::create([
                'patient_id' => $request->patient_id,
                'clinic_id' => $user->clinic_id,
                'due_date' => $request->due_date,
                'tax_rate' => $request->tax_rate ?? 0,
                'discount_rate' => $request->discount_rate ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'notes' => $request->notes,
                'terms' => $request->terms,
                'created_by' => $user->id,
                'status' => 'draft',
                'subtotal' => 0, // Will be calculated when items are added
            ]);

            foreach ($request->items as $itemData) {
                $invoice->addItem([
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'item_type' => $itemData['item_type'],
                ]);
            }
        });

        return back()->with('success', 'Invoice created successfully.');
    }

    /**
     * Display expenses.
     */
    public function expenses(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->canAccessFinance()) {
            abort(403, 'Access denied to expenses.');
        }

        $query = Expense::with(['clinic', 'creator', 'approver']);

        // Filter by clinic for all users
        $query->where('clinic_id', $user->clinic_id);

        // Apply filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('expense_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('vendor_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        $expenses = $query->latest()->paginate(15);

        return view('finance.expenses', compact('expenses'));
    }

    /**
     * Store a new expense.
     */
    public function storeExpense(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->canAccessFinance()) {
            abort(403, 'Access denied to create expenses.');
        }

        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|in:salary,rent,utilities,equipment,supplies,marketing,insurance,taxes,maintenance,other',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,card,bank_transfer,check,other',
            'vendor_name' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:255',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|in:monthly,quarterly,yearly',
        ]);

        $expenseData = [
            'description' => $request->description,
            'amount' => $request->amount,
            'category' => $request->category,
            'expense_date' => $request->expense_date,
            'payment_method' => $request->payment_method,
            'vendor_name' => $request->vendor_name,
            'receipt_number' => $request->receipt_number,
            'notes' => $request->notes,
            'is_recurring' => $request->boolean('is_recurring'),
            'recurring_frequency' => $request->recurring_frequency,
            'clinic_id' => $user->clinic_id,
            'created_by' => $user->id,
            'status' => 'pending',
        ];

        // Handle receipt file upload
        if ($request->hasFile('receipt_file')) {
            $file = $request->file('receipt_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs("expenses/{$user->clinic_id}/receipts", $filename, 'public');
            $expenseData['receipt_file'] = $path;
        }

        Expense::create($expenseData);

        return back()->with('success', 'Expense created successfully.');
    }

    /**
     * Approve an expense.
     */
    public function approveExpense(Expense $expense)
    {
        $user = auth()->user();
        
        if (!$user->canAccessFinance() || $user->role !== 'admin') {
            abort(403, 'Only admins can approve expenses.');
        }

        if (!$expense->canBeApproved()) {
            return back()->withErrors(['error' => 'Expense cannot be approved in its current status.']);
        }

        $expense->markAsApproved($user);

        return back()->with('success', 'Expense approved successfully.');
    }

    /**
     * Reject an expense.
     */
    public function rejectExpense(Expense $expense)
    {
        $user = auth()->user();
        
        if (!$user->canAccessFinance() || $user->role !== 'admin') {
            abort(403, 'Only admins can reject expenses.');
        }

        if (!$expense->canBeApproved()) {
            return back()->withErrors(['error' => 'Expense cannot be rejected in its current status.']);
        }

        $expense->markAsRejected($user);

        return back()->with('success', 'Expense rejected.');
    }

    /**
     * Generate invoice PDF.
     */
    public function generateInvoicePDF(Invoice $invoice)
    {
        $user = auth()->user();
        
        // Check access
        if (!$user->canAccessFinance() || 
            ($invoice->clinic_id !== $user->clinic_id)) {
            abort(403, 'Unauthorized access to invoice.');
        }

        $invoice->load(['patient', 'clinic', 'items']);

        $pdf = Pdf::loadView('finance.invoice-pdf', compact('invoice'));
        
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Display financial reports dashboard.
     */
    public function reports()
    {
        $user = auth()->user();

        if (!$user->canAccessFinance()) {
            abort(403, 'Access denied to financial reports.');
        }

        // Get basic report data
        $reportData = $this->getReportData($user);

        return view('finance.reports', $reportData);
    }

    /**
     * Generate cash flow report.
     */
    public function cashFlowReport(Request $request)
    {
        $user = auth()->user();

        if (!$user->canAccessFinance()) {
            abort(403, 'Access denied to cash flow reports.');
        }

        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : now()->startOfMonth();
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to) : now()->endOfMonth();

        // Get cash flow data
        $cashFlowData = $this->getCashFlowData($user, $dateFrom, $dateTo);

        if ($request->wantsJson()) {
            return response()->json($cashFlowData);
        }

        return view('finance.reports.cash-flow', compact('cashFlowData', 'dateFrom', 'dateTo'));
    }

    /**
     * Generate profit and loss report.
     */
    public function profitLossReport(Request $request)
    {
        $user = auth()->user();

        if (!$user->canAccessFinance()) {
            abort(403, 'Access denied to profit and loss reports.');
        }

        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : now()->startOfMonth();
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to) : now()->endOfMonth();

        // Get profit and loss data
        $profitLossData = $this->getProfitLossData($user, $dateFrom, $dateTo);

        if ($request->wantsJson()) {
            return response()->json($profitLossData);
        }

        return view('finance.reports.profit-loss', compact('profitLossData', 'dateFrom', 'dateTo'));
    }

    /**
     * Get financial statistics.
     */
    private function getFinancialStats($user): array
    {
        $stats = [];
        
        // Base queries
        $invoicesQuery = Invoice::query();
        $expensesQuery = Expense::query();
        
        

        // Current month stats
        $currentMonth = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        
        $stats['monthlyRevenue'] = $invoicesQuery->clone()
            ->byDateRange($currentMonth, $currentMonthEnd)
            ->sum('total_amount');
            
        $stats['monthlyExpenses'] = $expensesQuery->clone()
            ->approved()
            ->byDateRange($currentMonth, $currentMonthEnd)
            ->sum('amount');
            
        $stats['monthlyProfit'] = $stats['monthlyRevenue'] - $stats['monthlyExpenses'];

        // Outstanding amounts
        $stats['outstandingInvoices'] = $invoicesQuery->clone()
            ->whereIn('status', ['sent', 'overdue'])
            ->sum('balance');
            
        $stats['pendingExpenses'] = $expensesQuery->clone()
            ->pending()
            ->sum('amount');

        // Counts
        $stats['totalInvoices'] = $invoicesQuery->clone()->count();
        $stats['overdueInvoices'] = $invoicesQuery->clone()->overdue()->count();
        $stats['pendingExpenseCount'] = $expensesQuery->clone()->pending()->count();

        // Recent activity
        $stats['recentInvoices'] = $invoicesQuery->clone()
            ->with(['patient'])
            ->latest()
            ->limit(5)
            ->get();
            
        $stats['recentExpenses'] = $expensesQuery->clone()
            ->with(['creator'])
            ->latest()
            ->limit(5)
            ->get();

        return $stats;
    }

    /**
     * Get report data for reports dashboard.
     */
    private function getReportData($user): array
    {
        $data = [];

        // Base queries filtered by clinic
        $invoicesQuery = Invoice::where('clinic_id', $user->clinic_id);
        $expensesQuery = Expense::where('clinic_id', $user->clinic_id);

        // Current month data
        $currentMonth = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();

        $data['currentMonth'] = [
            'revenue' => $invoicesQuery->clone()->byDateRange($currentMonth, $currentMonthEnd)->sum('total_amount'),
            'expenses' => $expensesQuery->clone()->approved()->byDateRange($currentMonth, $currentMonthEnd)->sum('amount'),
        ];
        $data['currentMonth']['profit'] = $data['currentMonth']['revenue'] - $data['currentMonth']['expenses'];

        // Previous month data for comparison
        $previousMonth = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();

        $data['previousMonth'] = [
            'revenue' => $invoicesQuery->clone()->byDateRange($previousMonth, $previousMonthEnd)->sum('total_amount'),
            'expenses' => $expensesQuery->clone()->approved()->byDateRange($previousMonth, $previousMonthEnd)->sum('amount'),
        ];
        $data['previousMonth']['profit'] = $data['previousMonth']['revenue'] - $data['previousMonth']['expenses'];

        // Year to date
        $yearStart = now()->startOfYear();
        $data['yearToDate'] = [
            'revenue' => $invoicesQuery->clone()->byDateRange($yearStart, now())->sum('total_amount'),
            'expenses' => $expensesQuery->clone()->approved()->byDateRange($yearStart, now())->sum('amount'),
        ];
        $data['yearToDate']['profit'] = $data['yearToDate']['revenue'] - $data['yearToDate']['expenses'];

        return $data;
    }

    /**
     * Get cash flow data for specified period.
     */
    private function getCashFlowData($user, $dateFrom, $dateTo): array
    {
        $data = [];

        // Cash inflows (invoices)
        $inflows = Invoice::where('clinic_id', $user->clinic_id)
            ->byDateRange($dateFrom, $dateTo)
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as amount')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Cash outflows (expenses)
        $outflows = Expense::where('clinic_id', $user->clinic_id)
            ->approved()
            ->byDateRange($dateFrom, $dateTo)
            ->selectRaw('DATE(expense_date) as date, SUM(amount) as amount')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $data['inflows'] = $inflows;
        $data['outflows'] = $outflows;
        $data['totalInflows'] = $inflows->sum('amount');
        $data['totalOutflows'] = $outflows->sum('amount');
        $data['netCashFlow'] = $data['totalInflows'] - $data['totalOutflows'];

        return $data;
    }

    /**
     * Get profit and loss data for specified period.
     */
    private function getProfitLossData($user, $dateFrom, $dateTo): array
    {
        $data = [];

        // Revenue breakdown
        $revenue = Invoice::where('clinic_id', $user->clinic_id)
            ->byDateRange($dateFrom, $dateTo)
            ->with('items')
            ->get();

        $revenueByType = $revenue->flatMap->items
            ->groupBy('item_type')
            ->map(function ($items) {
                return $items->sum(function ($item) {
                    return $item->quantity * $item->unit_price;
                });
            });

        // Expense breakdown
        $expenses = Expense::where('clinic_id', $user->clinic_id)
            ->approved()
            ->byDateRange($dateFrom, $dateTo)
            ->get();

        $expensesByCategory = $expenses->groupBy('category')
            ->map(function ($expenses) {
                return $expenses->sum('amount');
            });

        $data['revenue'] = [
            'total' => $revenue->sum('total_amount'),
            'byType' => $revenueByType,
        ];

        $data['expenses'] = [
            'total' => $expenses->sum('amount'),
            'byCategory' => $expensesByCategory,
        ];

        $data['grossProfit'] = $data['revenue']['total'] - $data['expenses']['total'];
        $data['profitMargin'] = $data['revenue']['total'] > 0
            ? ($data['grossProfit'] / $data['revenue']['total']) * 100
            : 0;

        return $data;
    }
}
