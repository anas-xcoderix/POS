@php $view = $data['meta']['view'] ?? 'table'; @endphp

@if($view === 'income_statement' && !empty($data['summary']))
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <dl class="divide-y divide-slate-100">
            @foreach($data['summary'] as $line)
                <div class="flex justify-between px-6 py-4 text-sm {{ $loop->last ? 'bg-slate-900 text-white font-bold' : '' }}">
                    <dt>{{ $isAr ? ($line['label_ar'] ?? $line['label']) : $line['label'] }}</dt>
                    <dd class="font-mono">{{ $line['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
@elseif($view === 'balance_sheet' && !empty($data['sections']))
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        @foreach(['assets' => __('reports.assets'), 'liabilities' => __('reports.liabilities'), 'equity' => __('reports.equity')] as $key => $sectionTitle)
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-orange-100 bg-orange-50 px-4 py-3 text-sm font-bold text-slate-800">{{ $sectionTitle }}</div>
                <div class="divide-y divide-slate-50">
                    @forelse($data['sections'][$key] ?? [] as $row)
                        <div class="flex justify-between gap-2 px-4 py-2.5 text-sm">
                            <span class="min-w-0 truncate text-slate-700">{{ $row['name'] }}</span>
                            <span class="shrink-0 font-mono font-medium">{{ $row['amount'] }}</span>
                        </div>
                    @empty
                        <p class="px-4 py-6 text-center text-sm text-slate-400">—</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
    @if(!empty($data['summary']))
        <div class="mt-4 rounded-xl border border-orange-200 bg-orange-50 px-5 py-3 text-sm text-slate-700">
            {{ __('reports.total_assets') }}: <strong class="font-mono">{{ $data['summary']['total_assets'] }}</strong>
            · {{ __('reports.liabilities_equity') }}:
            <strong class="font-mono">{{ number_format((float) str_replace(',', '', $data['summary']['total_liabilities'] ?? 0) + (float) str_replace(',', '', $data['summary']['total_equity'] ?? 0), 2) }}</strong>
        </div>
    @endif
@elseif($view === 'expiring')
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b bg-slate-800 px-4 py-3 text-sm font-bold text-white">{{ __('reports.employees') }}</div>
            @include('reports.partials.table-simple', ['rows' => $data['employees'] ?? [], 'headers' => $isAr ? ['الاسم','الإقامة','انتهاء الإقامة','الرخصة','انتهاء الرخصة'] : ['Name','Aqama','Aqama Exp','License','License Exp']])
        </div>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b bg-slate-800 px-4 py-3 text-sm font-bold text-white">{{ __('reports.vehicles') }}</div>
            @include('reports.partials.table-simple', ['rows' => $data['vehicles'] ?? [], 'headers' => $isAr ? ['اللوحة','العميل','انتهاء الاستمارة'] : ['Plate','Customer','Istimara Exp']])
        </div>
    </div>
@else
    {{-- Crystal-style report preview --}}
    <div class="report-preview overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        @if(!empty($data['meta']['filter_display']))
            <div class="border-b border-slate-100 bg-slate-50 px-5 py-3">
                <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs">
                    @foreach($data['meta']['filter_display'] as $filter)
                        <span><span class="font-semibold text-slate-500">{{ $filter['label'] }}:</span> {{ $filter['value'] }}</span>
                    @endforeach
                </div>
            </div>
        @endif
        @if(($data['meta']['row_count'] ?? null) !== null)
            <div class="border-b border-slate-100 px-5 py-2 text-xs text-slate-500">{{ __('reports.records') }}: {{ number_format($data['meta']['row_count']) }}</div>
        @endif
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-orange-50"><tr>
                    @foreach($data['columns'] as $col)
                        <th class="border-b border-slate-200 px-4 py-3 text-start text-xs font-bold uppercase tracking-wide text-slate-600 {{ ($col['align'] ?? '') === 'right' ? 'text-end' : '' }}">
                            {{ $isAr ? $col['labelAr'] : $col['label'] }}
                        </th>
                    @endforeach
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($data['rows'] as $row)
                        <tr class="hover:bg-slate-50/80">
                            @foreach($data['columns'] as $col)
                                <td class="px-4 py-2.5 {{ ($col['align'] ?? '') === 'right' ? 'text-end font-mono' : '' }}">{{ $row[$col['key']] ?? '—' }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($data['columns']) }}" class="px-5 py-10 text-center text-slate-400">{{ __('reports.no_data') }}</td></tr>
                    @endforelse
                </tbody>
                @if(!empty($data['summary']) && is_array($data['summary']) && !isset($data['summary'][0]))
                    <tfoot class="bg-slate-100 font-semibold">
                        <tr>
                            <td colspan="{{ count($data['columns']) }}" class="px-5 py-3 text-sm text-slate-700">
                                @foreach($data['summary'] as $k => $v)
                                    <span class="me-6"><span class="text-slate-500">{{ ucfirst(str_replace('_', ' ', $k)) }}:</span> <span class="font-mono">{{ $v }}</span></span>
                                @endforeach
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endif
