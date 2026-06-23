<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\PaymentReceipt;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Models\Vendor;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentReceiptController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index(Request $request): View
    {
        $query = PaymentReceipt::with(['customer', 'vendor', 'branch'])->latest();

        if ($search = $request->get('search')) {
            $query->where('receipt_no', 'like', "%{$search}%");
        }

        return view('payments.index', [
            'records' => $query->paginate(15)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(Request $request): View
    {
        $partyType = $request->get('party_type', 'customer');

        return view('payments.create', [
            'partyType' => $partyType,
            'branches' => Branch::where('is_active', true)->get(),
            'customers' => Customer::where('is_active', true)->get(),
            'vendors' => Vendor::where('is_active', true)->get(),
            'salesInvoices' => SalesInvoice::where('status', 'posted')->whereNull('voided_at')->latest()->take(50)->get(),
            'purchaseInvoices' => PurchaseInvoice::where('status', 'posted')->whereNull('voided_at')->latest()->take(50)->get(),
            'receiptNo' => 'RCP-'.now()->format('Ymd').'-'.str_pad((string) (PaymentReceipt::count() + 1), 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'receipt_no' => 'required|string|unique:payment_receipts,receipt_no',
            'party_type' => 'required|in:customer,vendor',
            'customer_id' => 'required_if:party_type,customer|nullable|exists:customers,id',
            'vendor_id' => 'required_if:party_type,vendor|nullable|exists:vendors,id',
            'branch_id' => 'required|exists:branches,id',
            'receipt_date' => 'required|date',
            'payment_method' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'reference_no' => 'nullable|string',
            'sales_invoice_id' => 'nullable|exists:sales_invoices,id',
            'purchase_invoice_id' => 'nullable|exists:purchase_invoices,id',
            'remarks' => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();
        $data['status'] = 'posted';

        try {
            $this->paymentService->createReceipt($data);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('payments.index')->with('success', __('messages.payment.recorded'));
    }

    public function customerStatement(Request $request, Customer $customer): View
    {
        $from = $request->get('from', now()->startOfYear()->toDateString());
        $to = $request->get('to', now()->toDateString());

        return view('payments.customer-statement', [
            'customer' => $customer,
            'from' => $from,
            'to' => $to,
            'lines' => $this->paymentService->customerStatement($customer->id, $from, $to),
        ]);
    }

    public function vendorStatement(Request $request, Vendor $vendor): View
    {
        $from = $request->get('from', now()->startOfYear()->toDateString());
        $to = $request->get('to', now()->toDateString());

        return view('payments.vendor-statement', [
            'vendor' => $vendor,
            'from' => $from,
            'to' => $to,
            'lines' => $this->paymentService->vendorStatement($vendor->id, $from, $to),
        ]);
    }
}
