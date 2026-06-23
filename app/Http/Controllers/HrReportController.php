<?php

namespace App\Http\Controllers;

use App\Services\HrService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrReportController extends Controller
{
    public function __construct(private HrService $hr) {}

    public function expiringDocuments(Request $request): View
    {
        $days = (int) $request->get('days', 60);

        return view('hr.reports.expiring-documents', [
            'employees' => $this->hr->expiringDocuments($days),
            'vehicles' => $this->hr->expiringVehicles($days),
            'days' => $days,
        ]);
    }
}
