<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Part;
use App\Models\Quotation;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function __construct(private SalesService $salesService) {}

    public function index(Request $request): View
    {
        $query = Quotation::with(['customer', 'branch'])->latest();

        if ($search = $request->get('search')) {
            $query->where('quotation_no', 'like', "%{$search}%");
        }

        return view('quotations.index', [
            'records' => $query->paginate(15)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('quotations.create', $this->formData(
            'QT-'.now()->format('Ymd').'-'.str_pad((string) (Quotation::count() + 1), 4, '0', STR_PAD_LEFT)
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateQuotation($request);
        $data['created_by'] = auth()->id();
        try {
            $this->salesService->createQuotation($data, $data['items']);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('quotations.index')->with('success', 'Quotation created.');
    }

    public function show(Quotation $quotation): View
    {
        $quotation->load(['items.part', 'customer', 'branch']);

        return view('quotations.show', array_merge($this->formData(), [
            'quotation' => $quotation,
            'invoiceNo' => 'SI-'.now()->format('Ymd').'-'.str_pad((string) (\App\Models\SalesInvoice::count() + 1), 4, '0', STR_PAD_LEFT),
        ]));
    }

    public function convert(Request $request, Quotation $quotation): RedirectResponse
    {
        $data = $request->validate([
            'invoice_no' => 'required|string|unique:sales_invoices,invoice_no',
            'invoice_date' => 'required|date',
            'invoice_type' => 'required|string',
            'status' => 'required|string',
            'default_location_id' => 'nullable|exists:locations,id',
        ]);

        $data['created_by'] = auth()->id();
        $postStock = $data['status'] === 'posted';

        try {
            $invoice = $this->salesService->convertQuotationToInvoice($quotation, $data, $postStock);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('sales-invoices.index')
            ->with('success', __('messages.quotation.converted', ['no' => $invoice->invoice_no]));
    }

    protected function validateQuotation(Request $request): array
    {
        return $request->validate([
            'quotation_no' => 'required|string|unique:quotations,quotation_no',
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'required|exists:customers,id',
            'quotation_date' => 'required|date',
            'valid_until' => 'nullable|date',
            'status' => 'required|string',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.vat_percent' => 'nullable|numeric|min:0|max:100',
        ]);
    }

    protected function formData(?string $quotationNo = null): array
    {
        return [
            'branches' => Branch::where('is_active', true)->get(),
            'customers' => Customer::where('is_active', true)->get(),
            'parts' => Part::where('is_active', true)->with('brand')->get(),
            'locations' => Location::with('branch')->get(),
            'quotationNo' => $quotationNo,
        ];
    }
}
