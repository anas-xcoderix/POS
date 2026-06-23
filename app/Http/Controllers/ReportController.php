<?php

namespace App\Http\Controllers;

use App\Services\PermissionService;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reports,
        private PermissionService $permissions,
    ) {}

    public function index(): View
    {
        $grouped = [];
        foreach ($this->reports->allDefinitions() as $key => $def) {
            if (! $this->canAccessReport($def)) {
                continue;
            }
            $cat = $def['category'];
            $grouped[$cat]['label'] = config("reports.categories.{$cat}.label");
            $grouped[$cat]['label_ar'] = config("reports.categories.{$cat}.label_ar");
            $grouped[$cat]['reports'][] = array_merge($def, ['key' => $key]);
        }

        return view('reports.index', [
            'categories' => $grouped,
            'totalReports' => collect($grouped)->sum(fn ($c) => count($c['reports'])),
        ]);
    }

    public function show(Request $request, string $report): View
    {
        $def = $this->reports->definition($report);
        abort_unless($this->canAccessReport($def), 403);

        $data = $this->reports->generate($report, $request->all());

        return view('reports.show', [
            'report' => $report,
            'def' => $def,
            'data' => $data,
            'filterOptions' => $this->reports->filterOptions(),
        ]);
    }

    public function pdf(Request $request, string $report): Response
    {
        $def = $this->reports->definition($report);
        abort_unless($this->canAccessReport($def), 403);

        $data = $this->reports->generate($report, array_merge($request->all(), ['locale' => $request->get('locale', 'en')]));
        $view = $data['meta']['locale'] === 'ar' ? 'reports.pdf.document-ar' : 'reports.pdf.document';

        $pdf = Pdf::loadView($view, compact('data', 'report', 'def'));

        return $pdf->download($report.'-'.now()->format('Ymd').'.pdf');
    }

    public function csv(Request $request, string $report): StreamedResponse
    {
        $def = $this->reports->definition($report);
        abort_unless($this->canAccessReport($def), 403);

        $data = $this->reports->generate($report, $request->all());
        $locale = $data['meta']['locale'] ?? 'en';
        $filename = $report.'-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($data, $locale) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            if (! empty($data['columns'])) {
                fputcsv($out, collect($data['columns'])->map(fn ($c) => $locale === 'ar' ? $c['labelAr'] : $c['label'])->all());
                foreach ($data['rows'] as $row) {
                    fputcsv($out, collect($data['columns'])->map(fn ($c) => $row[$c['key']] ?? '')->all());
                }
            } elseif (! empty($data['summary'])) {
                fputcsv($out, [$locale === 'ar' ? 'البند' : 'Item', $locale === 'ar' ? 'القيمة' : 'Value']);
                foreach ($data['summary'] as $line) {
                    $label = is_array($line) ? ($locale === 'ar' ? ($line['label_ar'] ?? $line['label']) : $line['label']) : $line;
                    $value = is_array($line) ? $line['value'] : '';
                    fputcsv($out, [$label, $value]);
                }
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function canAccessReport(array $def): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $this->permissions->can($user, $def['permission'] ?? 'reports');
    }
}
