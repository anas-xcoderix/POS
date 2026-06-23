<?php

namespace App\Http\Controllers;

use App\Models\TransportShipment;
use App\Services\BranchScopeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransportShippingStatusController extends Controller
{
    public function __construct(private BranchScopeService $branchScope) {}

    public function index(Request $request): View
    {
        $query = TransportShipment::with(['customer', 'driver', 'branch'])
            ->orderByRaw("FIELD(status, 'pending','dispatched','in_transit','delivered','failed','cancelled')")
            ->orderByDesc('ship_date');

        $this->branchScope->apply($query);

        if ($from = $request->get('from')) {
            $query->where('ship_date', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->where('ship_date', '<=', $to);
        }

        $records = $query->paginate(20)->withQueryString();

        $counts = TransportShipment::query()
            ->when($request->get('from'), fn ($q, $f) => $q->where('ship_date', '>=', $f))
            ->when($request->get('to'), fn ($q, $t) => $q->where('ship_date', '<=', $t))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('transport.shipping-status.index', [
            'records' => $records,
            'counts' => $counts,
            'from' => $from ?? now()->startOfMonth()->toDateString(),
            'to' => $to ?? now()->toDateString(),
        ]);
    }
}
