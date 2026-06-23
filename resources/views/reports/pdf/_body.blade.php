@php
    $isAr = $isAr ?? false;
    $view = $data['meta']['view'] ?? 'table';
@endphp

@if($view === 'income_statement' && !empty($data['summary']))
    <table class="fin-line">
        @foreach($data['summary'] as $line)
            <tr class="{{ $loop->last ? 'net' : '' }}">
                <td style="width: 70%;">{{ $isAr ? ($line['label_ar'] ?? $line['label']) : $line['label'] }}</td>
                <td class="text-right num" style="width: 30%;">{{ $line['value'] }}</td>
            </tr>
        @endforeach
    </table>

@elseif($view === 'balance_sheet' && !empty($data['sections']))
    <table class="bs-columns">
        <tr>
            @foreach([
                'assets' => __('reports.assets'),
                'liabilities' => __('reports.liabilities'),
                'equity' => __('reports.equity'),
            ] as $key => $sectionTitle)
                <td>
                    <div class="bs-col-head">{{ $sectionTitle }}</div>
                    <table class="bs-row">
                        @forelse($data['sections'][$key] ?? [] as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td class="text-right num" style="width: 35%;">{{ $row['amount'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center" style="color:#94a3b8;">—</td></tr>
                        @endforelse
                    </table>
                </td>
            @endforeach
        </tr>
    </table>
    @if(!empty($data['summary']))
        <div class="summary-bar">
            <table class="summary-bar-table">
                <tr>
                    <td><strong>{{ __('reports.total_assets') }}:</strong> {{ $data['summary']['total_assets'] ?? '—' }}</td>
                    <td><strong>{{ __('reports.liabilities_equity') }}:</strong>
                        {{ number_format(
                            (float) str_replace(',', '', $data['summary']['total_liabilities'] ?? 0)
                            + (float) str_replace(',', '', $data['summary']['total_equity'] ?? 0),
                            2
                        ) }}
                    </td>
                </tr>
            </table>
        </div>
    @endif

@elseif($view === 'expiring')
    <div class="section-head">{{ __('reports.employees') }}</div>
    <div class="section-box">
        <table class="rpt">
            <thead><tr>
                @foreach(($isAr ? ['الاسم','الإقامة','انتهاء الإقامة','الرخصة','انتهاء الرخصة'] : ['Name','Aqama','Aqama Exp','License','License Exp']) as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr></thead>
            <tbody>
                @forelse($data['employees'] ?? [] as $row)
                    <tr>
                        @foreach($row as $cell)<td>{{ $cell ?? '—' }}</td>@endforeach
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-msg">{{ __('reports.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section-head">{{ __('reports.vehicles') }}</div>
    <div class="section-box">
        <table class="rpt">
            <thead><tr>
                @foreach(($isAr ? ['اللوحة','العميل','انتهاء الاستمارة'] : ['Plate','Customer','Istimara Exp']) as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr></thead>
            <tbody>
                @forelse($data['vehicles'] ?? [] as $row)
                    <tr>
                        @foreach($row as $cell)<td>{{ $cell ?? '—' }}</td>@endforeach
                    </tr>
                @empty
                    <tr><td colspan="3" class="empty-msg">{{ __('reports.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

@else
    {{-- Default tabular report (Crystal detail band) --}}
    <table class="rpt">
        @if(!empty($data['columns']))
            <thead><tr>
                @foreach($data['columns'] as $col)
                    <th class="{{ ($col['align'] ?? '') === 'right' ? 'text-right' : '' }}">
                        {{ $isAr ? ($col['labelAr'] ?? $col['label']) : $col['label'] }}
                    </th>
                @endforeach
            </tr></thead>
        @endif
        <tbody>
            @forelse($data['rows'] ?? [] as $row)
                @if(!empty($row['_group']))
                    <tr>
                        <td colspan="{{ count($data['columns']) }}" style="background:#334155;color:#fff;font-weight:bold;border-color:#334155;">
                            {{ $row['_group'] }}
                        </td>
                    </tr>
                @else
                    <tr>
                        @foreach($data['columns'] as $col)
                            @php $val = $row[$col['key']] ?? '—'; @endphp
                            <td class="{{ ($col['align'] ?? '') === 'right' ? 'text-right num' : '' }}">{{ $val }}</td>
                        @endforeach
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="{{ max(count($data['columns'] ?? []), 1) }}" class="empty-msg">{{ __('reports.no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
        @if(!empty($data['summary']) && is_array($data['summary']) && !isset($data['summary'][0]))
            <tfoot>
                <tr>
                    @php $cols = count($data['columns'] ?? []); @endphp
                    <td colspan="{{ max($cols - 1, 1) }}" class="text-right" style="background:#f1f5f9;font-weight:bold;">{{ __('reports.totals') }}</td>
                    @if($cols > 1)
                        <td class="text-right num" style="background:#f1f5f9;font-weight:bold;">
                            {{ collect($data['summary'])->last() }}
                        </td>
                    @endif
                </tr>
            </tfoot>
        @endif
    </table>

    @if(!empty($data['summary']) && is_array($data['summary']) && !isset($data['summary'][0]))
        <div class="summary-bar">
            <table class="summary-bar-table">
                <tr>
                    @foreach($data['summary'] as $k => $v)
                        <td><strong>{{ ucfirst(str_replace('_', ' ', $k)) }}:</strong> <span class="num">{{ $v }}</span></td>
                    @endforeach
                </tr>
            </table>
        </div>
    @endif
@endif
