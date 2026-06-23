<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Part;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class MasterPrintController extends Controller
{
    public function customers(): Response
    {
        $customers = Customer::with('branch')->where('is_active', true)->orderBy('code')->get();
        $view = is_rtl() ? 'pdf.masters.customers-ar' : 'pdf.masters.customers';

        $pdf = Pdf::loadView($view, compact('customers'));

        return $pdf->download('customer-master-'.now()->format('Ymd').'.pdf');
    }

    public function vendors(): Response
    {
        $vendors = Vendor::where('is_active', true)->orderBy('code')->get();
        $view = is_rtl() ? 'pdf.masters.vendors-ar' : 'pdf.masters.vendors';

        $pdf = Pdf::loadView($view, compact('vendors'));

        return $pdf->download('vendor-master-'.now()->format('Ymd').'.pdf');
    }

    public function parts(): Response
    {
        $parts = Part::with('brand')->where('is_active', true)->orderBy('part_number')->get();
        $view = is_rtl() ? 'pdf.masters.parts-ar' : 'pdf.masters.parts';

        $pdf = Pdf::loadView($view, compact('parts'));

        return $pdf->download('parts-master-'.now()->format('Ymd').'.pdf');
    }
}
