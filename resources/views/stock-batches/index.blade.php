@php $title = __('nav.stock_batches'); @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <x-ui.form-field label="Part" name="part_id" type="select" class="!mb-0">
                <option value="">{{ __('pages.filter.all_parts') }}</option>
                @foreach($parts as $p)<option value="{{ $p->id }}" @selected($partId == $p->id)>{{ $p->part_number }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="{{ __('ui.branch') }}" name="branch_id" type="select" class="!mb-0">
                <option value="">{{ __('pages.filter.all_branches') }}</option>
                @foreach($branches as $b)<option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="{{ __('ui.location') }}" name="location_id" type="select" class="!mb-0">
                <option value="">{{ __('pages.filter.all_locations') }}</option>
                @foreach($locations as $l)<option value="{{ $l->id }}" @selected($locationId == $l->id)>{{ $l->branch?->code }} / {{ $l->code }}</option>@endforeach
            </x-ui.form-field>
            <button class="erp-btn-secondary">{{ __('ui.filter') }}</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Part</th><th>Batch</th><th>Lot</th><th>Serial</th><th>Location</th><th>Qty</th><th>Expiry</th><th>Received</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->part?->part_number }}</td>
                        <td>{{ $row->batch_no ?? '—' }}</td>
                        <td>{{ $row->lot_no ?? '—' }}</td>
                        <td>{{ $row->serial_no ?? '—' }}</td>
                        <td>{{ $row->location?->code }}</td>
                        <td>{{ number_format($row->quantity, 2) }}</td>
                        <td>{{ $row->expiry_date?->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ $row->received_date?->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8"><x-ui.empty-state title="{{ __('pages.empty.stock_batches') }}" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
