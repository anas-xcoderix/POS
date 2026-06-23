<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\WorkshopService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkshopReportController extends Controller
{
    public function __construct(private WorkshopService $workshop) {}

    public function wip(Request $request): View
    {
        $branchId = $request->get('branch_id');
        $records = $this->workshop->wipReport($branchId ? (int) $branchId : null);

        return view('workshop.reports.wip', [
            'records' => $records,
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'branchId' => $branchId,
            'totalWip' => $records->sum('total_amount'),
        ]);
    }
}
