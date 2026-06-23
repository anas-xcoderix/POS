<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PosSession;
use App\Models\PosTerminal;
use App\Services\BranchScopeService;
use App\Services\GranularPermissionService;
use App\Services\PosService;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        private PosService $posService,
        private BranchScopeService $branchScope,
        private GranularPermissionService $granular,
        private SettingService $settings,
    ) {}

    public function index(): View
    {
        $query = PosTerminal::with(['branch', 'defaultLocation'])->where('is_active', true);
        $this->branchScope->apply($query, 'branch_id');

        $terminals = $query->get();
        $openSessions = PosSession::with(['posTerminal', 'user'])
            ->where('status', 'open')
            ->whereIn('pos_terminal_id', $terminals->pluck('id'))
            ->get()
            ->keyBy('pos_terminal_id');

        return view('pos.index', [
            'terminals' => $terminals,
            'openSessions' => $openSessions,
        ]);
    }

    public function openSession(Request $request, PosTerminal $terminal): RedirectResponse
    {
        $data = $request->validate([
            'opening_float' => 'required|numeric|min:0',
        ]);

        try {
            $session = $this->posService->openSession($terminal, (float) $data['opening_float'], auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('pos.counter', $session)->with('success', __('messages.pos.session_opened'));
    }

    public function counter(PosSession $session): View
    {
        $session->load(['posTerminal.branch', 'posTerminal.defaultLocation', 'user']);

        if ($session->status !== 'open') {
            abort(404);
        }

        $this->branchScope->assertBranchAccess($session->branch_id);

        $stats = $this->posService->sessionStats($session);
        $locationId = $session->posTerminal?->default_location_id;

        return view('pos.counter', [
            'session' => $session,
            'stats' => $stats,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'defaultCustomerId' => Customer::where('is_active', true)->where('customer_type', 'retail')->value('id')
                ?? Customer::where('is_active', true)->value('id'),
            'nextInvoiceNo' => $this->posService->nextInvoiceNo(),
            'vatRate' => (float) $this->settings->get('default_vat_rate', 15),
            'locationId' => $locationId,
            'canDeleteLine' => $this->granular->can(auth()->user(), 'pos.delete_line'),
            'pricingUrl' => route('pricing.resolve'),
            'searchUrl' => route('pos.search-parts', $session),
        ]);
    }

    public function searchParts(Request $request, PosSession $session): JsonResponse
    {
        if ($session->status !== 'open') {
            abort(404);
        }

        $data = $request->validate(['q' => 'required|string|min:1|max:100']);
        $locationId = $session->posTerminal?->default_location_id;

        $results = $this->posService->searchParts(
            $session->branch_id,
            $locationId ? (int) $locationId : null,
            $data['q']
        );

        return response()->json(['results' => $results]);
    }

    public function quickSale(Request $request, PosSession $session): RedirectResponse
    {
        if ($session->status !== 'open') {
            abort(404);
        }

        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_type' => 'required|in:cash,credit',
            'paid_amount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.vat_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $invoice = $this->posService->quickSale($session, [
                'invoice_no' => $this->posService->nextInvoiceNo(),
                'customer_id' => $data['customer_id'],
                'invoice_date' => now()->toDateString(),
                'invoice_type' => $data['invoice_type'],
                'paid_amount' => $data['paid_amount'] ?? 0,
                'remarks' => $data['remarks'] ?? null,
            ], $data['items'], true);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('pos.counter', $session)
            ->with('success', __('messages.pos.sale_completed', ['no' => $invoice->invoice_no]))
            ->with('last_invoice_id', $invoice->id);
    }

    public function sessionReport(PosSession $session): View
    {
        $session->load(['posTerminal.branch', 'user']);
        $this->branchScope->assertBranchAccess($session->branch_id);

        return view('pos.session-report', [
            'session' => $session,
            'stats' => $this->posService->sessionStats($session),
        ]);
    }

    public function closeSession(Request $request, PosSession $session): RedirectResponse
    {
        $data = $request->validate([
            'closing_float' => 'required|numeric|min:0',
        ]);

        try {
            $this->posService->closeSession($session, (float) $data['closing_float']);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('pos.index')->with('success', __('messages.pos.session_closed'));
    }
}
