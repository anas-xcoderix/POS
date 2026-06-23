@php $title = $asset->asset_code; @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <h2 class="text-xl font-bold">{{ $asset->name }}</h2>
        <p class="text-sm text-slate-500">{{ $asset->asset_code }} · {{ $asset->category?->name }}</p>
        <dl class="mt-4 grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
            <div><dt class="text-slate-500">Purchase Date</dt><dd class="font-medium">{{ $asset->purchase_date?->format('Y-m-d') }}</dd></div>
            <div><dt class="text-slate-500">Purchase Value</dt><dd class="font-medium">{{ number_format($asset->purchase_value, 2) }}</dd></div>
            <div><dt class="text-slate-500">Accum. Depreciation</dt><dd class="font-medium">{{ number_format($asset->accumulated_depreciation, 2) }}</dd></div>
            <div><dt class="text-slate-500">Net Book Value</dt><dd class="font-medium">{{ number_format($asset->net_book_value, 2) }}</dd></div>
        </dl>
    </div>
    <div class="erp-card overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-3 font-bold">Depreciation History</div>
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr><th>Period</th><th>Amount</th><th>Posted</th></tr></thead>
            <tbody>
                @forelse($asset->depreciations as $d)
                    <tr>
                        <td>{{ $d->dep_year }}-{{ str_pad($d->dep_month, 2, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ number_format($d->amount, 2) }}</td>
                        <td>{{ $d->posted_at?->format('Y-m-d') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-slate-500">No depreciation posted yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-erp-layout>
