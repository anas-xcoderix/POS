<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\JobCard;
use App\Models\Location;
use App\Models\Part;
use App\Models\SalesInvoice;
use App\Models\Vehicle;
use App\Services\WorkshopService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobCardController extends Controller
{
    public function __construct(private WorkshopService $workshop) {}

    public function index(Request $request): View
    {
        $query = JobCard::with(['customer', 'vehicle', 'mechanic', 'branch'])->latest();

        if ($search = $request->get('search')) {
            $query->where('job_no', 'like', "%{$search}%");
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return view('workshop.job-cards.index', [
            'records' => $query->paginate(15)->withQueryString(),
            'search' => $search,
            'statusFilter' => $status,
        ]);
    }

    public function create(): View
    {
        return view('workshop.job-cards.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateJobCard($request);
        $data['created_by'] = auth()->id();
        $data['job_no'] = $data['job_no'] ?? $this->workshop->nextJobNo();

        try {
            $this->workshop->createJobCard($data, $data['items']);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('job-cards.index')->with('success', 'Job card created.');
    }

    public function show(JobCard $jobCard): View
    {
        $jobCard->load(['items.part', 'customer', 'vehicle', 'branch', 'mechanic', 'location', 'salesInvoice']);

        return view('workshop.job-cards.show', array_merge($this->formData(), [
            'jobCard' => $jobCard,
            'invoiceNo' => 'SI-'.now()->format('Ymd').'-'.str_pad((string) (SalesInvoice::count() + 1), 4, '0', STR_PAD_LEFT),
        ]));
    }

    public function updateStatus(Request $request, JobCard $jobCard): RedirectResponse
    {
        $data = $request->validate(['status' => 'required|in:open,in_progress,completed,cancelled']);

        try {
            if ($data['status'] === 'completed') {
                $this->workshop->complete($jobCard);
            } else {
                $this->workshop->updateStatus($jobCard, $data['status']);
            }
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Job card status updated.');
    }

    public function convert(Request $request, JobCard $jobCard): RedirectResponse
    {
        $data = $request->validate([
            'invoice_no' => 'required|string|unique:sales_invoices,invoice_no',
            'invoice_date' => 'required|date',
            'invoice_type' => 'required|in:cash,credit',
            'status' => 'required|in:draft,posted',
            'default_location_id' => 'nullable|exists:locations,id',
        ]);

        try {
            $invoice = $this->workshop->convertToInvoice($jobCard, $data, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('sales-invoices.index')
            ->with('success', 'Job card converted to invoice '.$invoice->invoice_no);
    }

    protected function validateJobCard(Request $request): array
    {
        return $request->validate([
            'job_no' => 'nullable|string|unique:job_cards,job_no',
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'location_id' => 'nullable|exists:locations,id',
            'mechanic_id' => 'nullable|exists:employees,id',
            'job_date' => 'required|date',
            'promised_date' => 'nullable|date|after_or_equal:job_date',
            'status' => 'required|in:open,in_progress',
            'complaint' => 'nullable|string',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:part,labor',
            'items.*.part_id' => 'nullable|exists:parts,id',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);
    }

    protected function formData(): array
    {
        return [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'vehicles' => Vehicle::where('is_active', true)->with('customer')->orderBy('plate_no')->get(),
            'mechanics' => Employee::where('is_active', true)->orderBy('name')->get(),
            'parts' => Part::where('is_active', true)->orderBy('part_number')->get(),
            'locations' => Location::with('branch')->get(),
            'jobNo' => $this->workshop->nextJobNo(),
        ];
    }
}
