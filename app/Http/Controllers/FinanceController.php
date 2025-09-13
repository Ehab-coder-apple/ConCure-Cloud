<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Expense;
use App\Models\Patient;
use App\Mail\InvoiceMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

        // Get patients for the create invoice form
        $patients = Patient::where('clinic_id', $user->clinic_id)
                          ->where('is_active', true)
                          ->orderBy('first_name')
                          ->orderBy('last_name')
                          ->get();

        return view('finance.invoices', compact('invoices', 'patients'));
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
            'due_date' => 'nullable|date|after_or_equal:today',
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
     * Display invoice for printing.
     */
    public function printInvoice(Invoice $invoice)
    {
        $user = auth()->user();

        // Check access
        if (!$user->canAccessFinance() ||
            ($invoice->clinic_id !== $user->clinic_id)) {
            abort(403, 'Unauthorized access to invoice.');
        }

        $invoice->load(['patient', 'clinic', 'items']);

        return view('finance.invoice-print', compact('invoice'));
    }

    /**
     * Generate public PDF access for invoice (no authentication required).
     */
    public function publicInvoicePDF(Invoice $invoice, $token)
    {
        // Verify the token
        $expectedToken = $this->generateInvoiceToken($invoice);
        if (!hash_equals($expectedToken, $token)) {
            abort(403, 'Invalid access token for invoice.');
        }

        $invoice->load(['patient', 'clinic', 'items']);

        $pdf = Pdf::loadView('finance.invoice-pdf', compact('invoice'));

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Get invoice data for editing.
     */
    public function getInvoiceForEdit(Invoice $invoice)
    {
        $user = auth()->user();

        // Check access
        if (!$user->canAccessFinance() ||
            ($invoice->clinic_id !== $user->clinic_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to invoice.'
            ], 403);
        }

        // Load invoice with relationships
        $invoice->load(['patient', 'items']);

        return response()->json([
            'success' => true,
            'invoice' => [
                'id' => $invoice->id,
                'patient_id' => $invoice->patient_id,
                'due_date' => $invoice->due_date,
                'notes' => $invoice->notes,
                'items' => $invoice->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'item_type' => $item->item_type,
                    ];
                })
            ]
        ]);
    }

    /**
     * Update an existing invoice.
     */
    public function updateInvoice(Request $request, Invoice $invoice)
    {
        $user = auth()->user();

        // Check access
        if (!$user->canAccessFinance() ||
            ($invoice->clinic_id !== $user->clinic_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to invoice.'
            ], 403);
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.item_type' => 'nullable|in:consultation,procedure,medication,lab_test,other',
        ]);

        try {
            DB::beginTransaction();

            // Update invoice basic info
            $invoice->update([
                'patient_id' => $request->patient_id,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
            ]);

            // Delete existing items
            $invoice->items()->delete();

            // Add new items and calculate totals
            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $total = $itemData['quantity'] * $itemData['unit_price'];
                $subtotal += $total;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $total,
                    'item_type' => $itemData['item_type'] ?? 'other', // Default to 'other' if not provided
                ]);
            }

            // Update invoice totals (assuming no tax for now)
            $taxAmount = 0; // You can implement tax calculation here
            $totalAmount = $subtotal + $taxAmount;

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'balance' => $totalAmount, // Assuming no payments made yet
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate secure token for invoice public access.
     */
    private function generateInvoiceToken(Invoice $invoice)
    {
        // Create a secure token based on invoice data and app key
        return hash('sha256', $invoice->id . $invoice->invoice_number . $invoice->created_at . config('app.key'));
    }

    /**
     * Get public PDF URL for invoice (for WhatsApp sharing).
     */
    public function getPublicPdfUrl(Invoice $invoice)
    {
        $user = auth()->user();

        // Check access
        if (!$user->canAccessFinance() ||
            ($invoice->clinic_id !== $user->clinic_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to invoice.'
            ], 403);
        }

        // Generate secure token
        $token = $this->generateInvoiceToken($invoice);

        // Create public URL
        $publicUrl = route('invoice.public.pdf', [
            'invoice' => $invoice->id,
            'token' => $token
        ]);

        return response()->json([
            'success' => true,
            'public_url' => $publicUrl,
            'invoice_number' => $invoice->invoice_number
        ]);
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

    /**
     * Send invoice via email.
     */
    public function emailInvoice(Request $request, Invoice $invoice)
    {
        $user = auth()->user();



        // Check access
        if (!$user->canAccessFinance() ||
            ($invoice->clinic_id !== $user->clinic_id)) {


            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to invoice.'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'subject' => 'nullable|string|max:255',
                'message' => 'nullable|string|max:1000',
            ]);



        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Invoice email validation failed', [
                'invoice_id' => $invoice->id,
                'errors' => $e->errors()
            ]);

            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                $errorMessages = array_merge($errorMessages, $messages);
            }

            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $errorMessages)
            ], 422);
        }

        $recipientEmail = $request->email;
        $customMessage = $request->message;
        // Handle checkbox value properly - "on" means checked, null/empty means unchecked
        $attachPdf = $request->has('attach_pdf') && in_array($request->attach_pdf, ['on', 'true', '1', true]);

        try {
            $invoice->load(['patient', 'clinic', 'items']);

            // Send the email
            Mail::to($recipientEmail)->send(new InvoiceMail($invoice, $attachPdf, $customMessage));

            // Update invoice status to 'sent' if it was draft
            if ($invoice->status === 'draft') {
                $invoice->markAsSent();
            }

            return response()->json([
                'success' => true,
                'message' => 'Invoice sent successfully to ' . $recipientEmail
            ]);

        } catch (\Exception $e) {
            \Log::error('Invoice email failed', [
                'invoice_id' => $invoice->id,
                'recipient' => $recipientEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send invoice email. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show email invoice form.
     */
    public function showEmailForm(Invoice $invoice)
    {
        $user = auth()->user();

        // Check access
        if (!$user->canAccessFinance() ||
            ($invoice->clinic_id !== $user->clinic_id)) {
            abort(403, 'Unauthorized access to invoice.');
        }

        $invoice->load(['patient', 'clinic']);

        return response()->json([
            'success' => true,
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'patient_name' => $invoice->patient->first_name . ' ' . $invoice->patient->last_name,
                'patient_email' => $invoice->patient->email,
                'total_amount' => $invoice->total_amount,
                'status' => $invoice->status,
            ]
        ]);
    }

    /**
     * Public invoice view (no authentication required).
     */
    public function publicInvoiceView(Invoice $invoice, $token)
    {
        // Verify the token
        $expectedToken = $this->generateInvoiceToken($invoice);
        if (!hash_equals($expectedToken, $token)) {
            abort(403, 'Invalid access token for invoice.');
        }

        $invoice->load(['patient', 'clinic', 'items']);

        return view('finance.invoice-print', compact('invoice'));
    }


}
