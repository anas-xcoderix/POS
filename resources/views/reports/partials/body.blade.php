@php $view = $data['meta']['view'] ?? 'table'; @endphp

@if($view === 'income_statement' && !empty($data['summary']))
    <div class="erp-card max-w-lg p-6">
        <dl class="space-y-3">
            @foreach($data['summary'] as $line)
                <div class="flex justify-between border-b border-slate-100 pb-2 text-sm">
                    <dt class="text-slate-600">{{ $isAr ? ($line['label_ar'] ?? $line['label']) : $line['label'] }}</dt>
                    <dd class="font-bold text-slate-900">{{ $line['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
@elseif($view === 'balance_sheet' && !empty($data['sections']))
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        @foreach(['assets' => ['Assets', 'الأصول'], 'liabilities' => ['Liabilities', 'الخصوم'], 'equity' => ['Equity', 'حقوق الملكية']] as $key => [$en, $ar])
            <div class="erp-card overflow-hidden">
                <div class="border-b bg-slate-50 px-4 py-3 font-bold">{{ $isAr ? $ar : $en }}</div>
                <div class="divide-y">
                    @forelse($data['sections'][$key] ?? [] as $row)
                        <div class="flex justify-between gap-2 px-4 py-2 text-sm">
                            <span class="min-w-0 truncate">{{ $row['name'] }}</span>
                            <span class="shrink-0 font-medium">{{ $row['amount'] }}</span>
                        </div>
                    @empty
                        <p class="px-4 py-6 text-center text-sm text-slate-500">—</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
    @if(!empty($data['summary']))
        <div class="erp-card p-4 text-sm text-slate-600">
            {{ $isAr ? 'إجمالي الأصول' : 'Total Assets' }}: <strong>{{ $data['summary']['total_assets'] }}</strong>
            · {{ $isAr ? 'الخصوم + حقوق الملكية' : 'Liabilities + Equity' }}:
            <strong>{{ number_format((float) str_replace(',', '', $data['summary']['total_liabilities']) + (float) str_replace(',', '', $data['summary']['total_equity']), 2) }}</strong>
        </div>
    @endif
@elseif($view === 'expiring')
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="erp-card overflow-hidden">
            <div class="border-b bg-slate-50 px-4 py-3 font-bold">{{ $isAr ? 'الموظفون' : 'Employees' }}</div>
            @include('reports.partials.table-simple', ['rows' => $data['employees'] ?? [], 'headers' => $isAr ? ['الاسم','الإقامة','انتهاء الإقامة','الرخصة','انتهاء الرخصة'] : ['Name','Aqama','Aqama Exp','License','License Exp']])
        </div>
        <div class="erp-card overflow-hidden">
            <div class="border-b bg-slate-50 px-4 py-3 font-bold">{{ $isAr ? 'المركبات' : 'Vehicles' }}</div>
            @include('reports.partials.table-simple', ['rows' => $data['vehicles'] ?? [], 'headers' => $isAr ? ['اللوحة','العميل','انتهاء الاستمارة'] : ['Plate','Customer','Istimara Exp']])
        </div>
    </div>
@else
    <div class="erp-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table min-w-full text-sm">
                <thead class="bg-slate-50/80"><tr>
                    @foreach($data['columns'] as $col)
                        <th class="{{ ($col['align'] ?? '') === 'right' ? 'text-right' : '' }}">
                            {{ $isAr ? $col['labelAr'] : $col['label'] }}
                        </th>
                    @endforeach
                </tr></thead>
                <tbody>
                    @forelse($data['rows'] as $row)
                        <tr>
                            @foreach($data['columns'] as $col)
                                <td class="{{ ($col['align'] ?? '') === 'right' ? 'text-right' : '' }}">{{ $row[$col['key']] ?? '—' }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($data['columns']) }}" class="px-5 py-8 text-center text-slate-500">{{ $isAr ? 'لا توجد بيانات' : 'No data for selected filters' }}</td></tr>
                    @endforelse
                </tbody>
                @if(!empty($data['summary']) && is_array($data['summary']))
                    <tfoot class="bg-slate-50/50">
                        <tr>
                            <td colspan="{{ count($data['columns']) }}" class="px-5 py-3 text-sm text-slate-600">
                                @foreach($data['summary'] as $k => $v)
                                    <span class="mr-4"><strong>{{ ucfirst(str_replace('_', ' ', $k)) }}:</strong> {{ $v }}</span>
                                @endforeach
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endif
