<div class="page-footer">
    <table class="page-footer-table">
        <tr>
            <td style="width: 33%;">{{ $data['meta']['company'] ?? config('app.name') }}</td>
            <td style="width: 34%; text-align: center;">{{ __('reports.report_center') }}</td>
            <td style="width: 33%; text-align: {{ ($isAr ?? false) ? 'left' : 'right' }};">
                {{ __('reports.generated') }}: {{ $data['meta']['generated_at'] }}
            </td>
        </tr>
    </table>
</div>

@if($isAr ?? false)
<script type="text/php">
if (isset($pdf)) {
    $font = $fontMetrics->getFont('DejaVu Sans');
    $pdf->page_text($pdf->get_width() - 120, $pdf->get_height() - 24, 'صفحة {PAGE_NUM} من {PAGE_COUNT}', $font, 7, array(0.58, 0.64, 0.72));
}
</script>
@else
<script type="text/php">
if (isset($pdf)) {
    $font = $fontMetrics->getFont('DejaVu Sans');
    $pdf->page_text($pdf->get_width() - 100, $pdf->get_height() - 24, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 7, array(0.58, 0.64, 0.72));
}
</script>
@endif
