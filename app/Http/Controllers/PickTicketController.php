<?php

namespace App\Http\Controllers;

use App\Models\PickTicket;
use App\Models\SalesInvoice;
use App\Services\PickTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PickTicketController extends Controller
{
    public function __construct(private PickTicketService $pickTicketService) {}

    public function index(Request $request): View
    {
        $query = PickTicket::with(['salesInvoice.customer', 'branch', 'location'])->latest();

        if ($search = $request->get('search')) {
            $query->where('pick_no', 'like', "%{$search}%");
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return view('pick-tickets.index', [
            'records' => $query->paginate(15)->withQueryString(),
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function createFromInvoice(SalesInvoice $salesInvoice): RedirectResponse
    {
        try {
            $ticket = $this->pickTicketService->createFromInvoice($salesInvoice, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('pick-tickets.show', $ticket)
            ->with('success', 'Pick ticket '.$ticket->pick_no.' created.');
    }

    public function show(PickTicket $pickTicket): View
    {
        $pickTicket->load(['items.part', 'salesInvoice.customer', 'branch', 'location', 'assignee']);

        return view('pick-tickets.show', ['ticket' => $pickTicket]);
    }

    public function confirm(Request $request, PickTicket $pickTicket): RedirectResponse
    {
        $data = $request->validate([
            'picked' => 'required|array',
            'picked.*' => 'nullable|numeric|min:0',
        ]);

        try {
            $this->pickTicketService->confirmPick($pickTicket, $data['picked'], auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('pick-tickets.index')->with('success', 'Pick confirmed.');
    }
}
