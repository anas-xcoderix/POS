@php $title = __('transport.shipments'); @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-1 flex-wrap items-end gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('transport.search_shipment') }}" class="erp-input !mt-0 sm:max-w-xs">
            <select name="status" class="erp-input !mt-0 sm:w-40">
                <option value="">{{ __('pages.filter.all_statuses') }}</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ __('transport.status.'.$s) }}</option>
                @endforeach
            </select>
            <select name="driver_id" class="erp-input !mt-0 sm:w-48">
                <option value="">{{ __('transport.all_drivers') }}</option>
                @foreach($drivers as $d)
                    <option value="{{ $d->id }}" @selected($driverId == $d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
            <button class="erp-btn-secondary">{{ __('ui.filter') }}</button>
        </form>
        <a href="{{ route('transport.shipments.create') }}" class="erp-btn-primary"><x-ui.icon name="plus" class="h-4 w-4" /> {{ __('transport.new_shipment') }}</a>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>{{ __('transport.shipment_no') }}</th>
                <th>{{ __('ui.date') }}</th>
                <th>{{ __('ui.customer') }}</th>
                <th>{{ __('transport.driver') }}</th>
                <th>{{ __('transport.cod') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th class="text-right">{{ __('ui.actions') }}</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->shipment_no }}</td>
                        <td>{{ $row->ship_date?->format('Y-m-d') }}</td>
                        <td>{{ $row->customer?->name }}</td>
                        <td>{{ $row->driver?->name ?? '—' }}</td>
                        <td>{{ number_format($row->cod_amount, 2) }}</td>
                        <td><span class="erp-badge erp-badge-slate">{{ __('transport.status.'.$row->status) }}</span></td>
                        <td class="text-right">
                            <a href="{{ route('transport.shipments.show', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs">{{ __('ui.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-ui.empty-state :title="__('transport.empty.shipments')" :description="__('transport.empty.shipments_hint')" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
