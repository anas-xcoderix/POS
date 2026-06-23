<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Part;
use App\Models\PosSession;
use App\Models\PosTerminal;
use App\Services\BranchScopeService;
use App\Services\PosService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        private PosService $posService,
        private BranchScopeService $branchScope,
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

        return redirect()->route('pos.counter', $session)->with('success', 'POS session opened.');
    }

    public function counter(PosSession $session): View
    {
        $session->load(['posTerminal.branch', 'user']);

        if ($session->status !== 'open') {
            abort(404);
        }

        return view('pos.counter', [
            'session' => $session,
            'customers' => Customer::where('is_active', true)->get(),
            'parts' => Part::where('is_active', true)->orderBy('part_number')->get(),
        ]);
    }

    public function quickSale(Request $request, PosSession $session): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_no' => 'required|string|unique:sales_invoices,invoice_no',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $invoice = $this->posService->quickSale($session, [
                'invoice_no' => $data['invoice_no'],
                'customer_id' => $data['customer_id'],
                'invoice_date' => now()->toDateString(),
            ], $data['items'], true);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', __('messages.pos.sale_completed', ['no' => $invoice->invoice_no]));
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

        return redirect()->route('pos.index')->with('success', 'POS session closed.');
    }
}
