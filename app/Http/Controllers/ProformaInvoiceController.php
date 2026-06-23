<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Part;
use App\Models\ProformaInvoice;
use App\Models\SalesInvoice;
use App\Services\ProformaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProformaInvoiceController extends Controller
{
    public function __construct(private ProformaService $proformaService) {}

    public function index(Request $request): View
    {
        $query = ProformaInvoice::with(['customer', 'branch', 'currency'])->latest();

        if ($search = $request->get('search')) {
            $query->where('proforma_no', 'like', "%{$search}%");
        }

        return view('proforma.index', [
            'records' => $query->paginate(15)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('proforma.create', $this->formData(
            'PF-'.now()->format('Ymd').'-'.str_pad((string) (ProformaInvoice::count() + 1), 4, '0', STR_PAD_LEFT)
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProforma($request);
        $data['created_by'] = auth()->id();

        try {
            $this->proformaService->create($data, $data['items']);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('proforma-invoices.index')->with('success', 'Proforma invoice created.');
    }

    public function show(ProformaInvoice $proformaInvoice): View
    {
        $proformaInvoice->load(['items.part', 'customer', 'branch', 'currency']);

        return view('proforma.show', array_merge($this->formData(), [
            'proforma' => $proformaInvoice,
            'invoiceNo' => 'SI-'.now()->format('Ymd').'-'.str_pad((string) (SalesInvoice::count() + 1), 4, '0', STR_PAD_LEFT),
        ]));
    }

    public function convert(Request $request, ProformaInvoice $proformaInvoice): RedirectResponse
    {
        $data = $request->validate([
            'invoice_no' => 'required|string|unique:sales_invoices,invoice_no',
            'invoice_date' => 'required|date',
            'invoice_type' => 'required|string|in:cash,credit',
            'status' => 'required|string',
        ]);

        $data['created_by'] = auth()->id();
        $postStock = $data['status'] === 'posted';

        try {
            $invoice = $this->proformaService->convertToInvoice($proformaInvoice, $data, $postStock);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('sales-invoices.index')
            ->with('success', 'Proforma converted to invoice '.$invoice->invoice_no);
    }

    protected function validateProforma(Request $request): array
    {
        return $request->validate([
            'proforma_no' => 'required|string|unique:proforma_invoices,proforma_no',
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'required|exists:customers,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'proforma_date' => 'required|date',
            'valid_until' => 'nullable|date',
            'status' => 'required|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.vat_percent' => 'nullable|numeric|min:0|max:100',
        ]);
    }

    protected function formData(?string $proformaNo = null): array
    {
        return [
            'branches' => Branch::where('is_active', true)->get(),
            'customers' => Customer::where('is_active', true)->get(),
            'parts' => Part::where('is_active', true)->with('brand')->get(),
            'currencies' => Currency::where('is_active', true)->get(),
            'proformaNo' => $proformaNo,
        ];
    }
}
