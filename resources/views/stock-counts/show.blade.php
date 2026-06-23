@php $title = __('modules.stock_count').' '.$session->count_no; @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">{{ $session->count_no }}</h2>
                <p class="text-sm text-slate-500">
                    {{ $session->count_date?->format('M d, Y') }} · {{ $session->branch?->name }}
                    @if($session->location) · {{ $session->location->code }} @endif
                </p>
                @if($session->remarks)
                    <p class="mt-1 text-sm text-slate-600">{{ $session->remarks }}</p>
                @endif
            </div>
            <span class="erp-badge {{ $session->status === 'posted' ? 'erp-badge-green' : 'erp-badge-amber' }} shrink-0">
                {{ ucfirst($session->status) }}
            </span>
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table min-w-full">
                <thead class="bg-slate-50/80"><tr>
                    <th>Part</th><th>Location</th><th class="text-right">System Qty</th><th class="text-right">Counted Qty</th><th class="text-right">Variance</th>
                </tr></thead>
                <tbody>
                    @forelse($session->items as $item)
                        <tr>
                            <td>
                                <span class="font-semibold">{{ $item->part?->part_number }}</span>
                                <div class="text-xs text-slate-500">{{ Str::limit($item->part?->description_en, 40) }}</div>
                            </td>
                            <td>{{ $item->location?->branch?->code }} / {{ $item->location?->code }}</td>
                            <td class="text-right">{{ number_format($item->system_qty, 2) }}</td>
                            <td class="text-right font-medium">{{ number_format($item->counted_qty, 2) }}</td>
                            <td class="text-right font-semibold {{ $item->variance != 0 ? ($item->variance > 0 ? 'text-emerald-600' : 'text-red-600') : 'text-slate-500' }}">
                                {{ $item->variance > 0 ? '+' : '' }}{{ number_format($item->variance, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">
                            <x-ui.empty-state title="No count lines" description="This session has no items." />
                        </td></tr>
                    @endforelse
                </tbody>
                @if($session->items->isNotEmpty())
                    <tfoot class="bg-slate-50">
                        <tr>
                            <td colspan="2" class="font-semibold text-right">Totals</td>
                            <td class="text-right font-bold">{{ number_format($session->items->sum('system_qty'), 2) }}</td>
                            <td class="text-right font-bold">{{ number_format($session->items->sum('counted_qty'), 2) }}</td>
                            <td class="text-right font-bold {{ $session->items->sum('variance') != 0 ? 'text-amber-600' : '' }}">
                                {{ number_format($session->items->sum('variance'), 2) }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
        <a href="{{ route('stock-counts.index') }}" class="erp-btn-secondary text-center">Back to Counts</a>
        @if($session->status === 'draft')
            <form method="POST" action="{{ route('stock-counts.post', $session) }}" onsubmit="return confirm('Post variances and adjust stock?')">
                @csrf
                <button type="submit" class="erp-btn-primary w-full sm:w-auto">Post Count & Adjust Stock</button>
            </form>
        @endif
    </div>
</div>
</x-erp-layout>
