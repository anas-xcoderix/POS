@php $title = __('transport.shipping_status'); @endphp
<x-erp-layout>
<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
    @foreach(['pending','dispatched','in_transit','delivered','failed','cancelled'] as $s)
        <div class="erp-stat-card !p-4">
            <p class="text-xs font-medium text-slate-500">{{ __('transport.status.'.$s) }}</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $counts[$s] ?? 0 }}</p>
        </div>
    @endforeach
</div>

<div class="erp-card overflow-hidden">
    <div class="border-b border-slate-100 px-5 py-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <x-ui.form-field :label="__('ui.from')" name="from" type="date" :value="$from" class="!mb-0" />
            <x-ui.form-field :label="__('ui.to')" name="to" type="date" :value="$to" class="!mb-0" />
            <button class="erp-btn-secondary">{{ __('ui.filter') }}</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>{{ __('transport.shipment_no') }}</th>
                <th>{{ __('ui.date') }}</th>
                <th>{{ __('ui.customer') }}</th>
                <th>{{ __('transport.driver') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th>{{ __('transport.expected_date') }}</th>
                <th class="text-right">{{ __('ui.actions') }}</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->shipment_no }}</td>
                        <td>{{ $row->ship_date?->format('Y-m-d') }}</td>
                        <td>{{ $row->customer?->name }}</td>
                        <td>{{ $row->driver?->name ?? '—' }}</td>
                        <td><span class="erp-badge erp-badge-slate">{{ __('transport.status.'.$row->status) }}</span></td>
                        <td>{{ $row->expected_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="text-right">
                            <a href="{{ route('transport.shipments.show', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs">{{ __('ui.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-ui.empty-state :title="__('transport.empty.shipments')" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
