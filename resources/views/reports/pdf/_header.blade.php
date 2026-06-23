@php
    $isAr = $isAr ?? false;
    $title = $isAr ? ($data['meta']['title_ar'] ?? $data['meta']['title']) : ($data['meta']['title_en'] ?? $data['meta']['title']);
    $categoryKey = $data['meta']['category'] ?? '';
    $catLabel = $isAr
        ? (config("reports.categories.{$categoryKey}.label_ar") ?? $categoryKey)
        : (config("reports.categories.{$categoryKey}.label") ?? $categoryKey);
@endphp

<div class="letterhead">
    <table class="letterhead-table">
        <tr>
            <td style="width: 55%;">
                <div class="company-name">{{ $data['meta']['company'] ?? config('app.name') }}</div>
                <div class="company-sub">{{ __('reports.confidential') }}</div>
            </td>
            <td class="letterhead-meta">
                <div>{{ __('reports.generated') }}: {{ $data['meta']['generated_at'] }}</div>
                @if($data['meta']['printed_by'] ?? null)
                    <div>{{ __('reports.printed_by') }}: {{ $data['meta']['printed_by'] }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>

<div class="title-band">
    <table class="title-band-table">
        <tr>
            <td style="width: 75%;">
                <div class="report-title">{{ $title }}</div>
                @if($data['meta']['legacy'] ?? null)
                    <div class="report-legacy">{{ __('reports.legacy_ref') }}: {{ $data['meta']['legacy'] }}.rpt</div>
                @endif
            </td>
            <td style="width: 25%; text-align: {{ $isAr ? 'left' : 'right' }};">
                <span class="report-category">{{ $catLabel }}</span>
            </td>
        </tr>
    </table>
</div>

@if(!empty($data['meta']['filter_display']))
    <div class="filter-strip">
        <table class="filter-strip-table">
            <tr>
                @foreach($data['meta']['filter_display'] as $filter)
                    <td>
                        <span class="filter-label">{{ $filter['label'] }}:</span>
                        <span class="filter-value">{{ $filter['value'] }}</span>
                    </td>
                    @if($loop->iteration % 4 === 0)
                        </tr><tr>
                    @endif
                @endforeach
            </tr>
        </table>
    </div>
@endif

@if(($data['meta']['row_count'] ?? null) !== null)
    <div class="record-count">{{ __('reports.records') }}: {{ number_format($data['meta']['row_count']) }}</div>
@endif
