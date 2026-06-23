@php $title = __('modules.delivery_notes'); @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <h3 class="text-base font-bold text-slate-900">Delivery Notes</h3>
        <a href="{{ route('delivery-notes.create') }}" class="erp-btn-primary">
            <x-ui.icon name="plus" class="h-4 w-4" /> New Delivery Note
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>DN No</th><th>{{ __('ui.customer') }}</th><th>Branch</th><th>Delivery Date</th><th>{{ __('pages.table.invoice') }}</th><th>Driver</th><th>{{ __('ui.status') }}</th><th class="text-right">{{ __('pages.table.action') }}</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">
                            <a href="{{ route('delivery-notes.show', $row) }}" class="text-orange-600 hover:underline">{{ $row->dn_no }}</a>
                        </td>
                        <td>{{ $row->customer?->name }}</td>
                        <td>{{ $row->branch?->code }}</td>
                        <td>{{ $row->delivery_date?->format('M d, Y') }}</td>
                        <td>{{ $row->salesInvoice?->invoice_no ?? '—' }}</td>
                        <td>
                            @if($row->driver_name)
                                <div class="text-sm">{{ $row->driver_name }}</div>
                            @endif
                            @if($row->vehicle_plate)
                                <div class="text-xs text-slate-500">{{ $row->vehicle_plate }}</div>
                            @endif
                            @if(!$row->driver_name && !$row->vehicle_plate)—@endif
                        </td>
                        <td>
                            <span class="erp-badge {{ $row->status === 'delivered' ? 'erp-badge-green' : 'erp-badge-amber' }}">
                                {{ ucfirst($row->status) }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('delivery-notes.show', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">
                        <x-ui.empty-state title="{{ __('pages.empty.delivery_notes') }}" description="{{ __('pages.empty.delivery_notes_hint') }}">
                            <x-slot:action>
                                <a href="{{ route('delivery-notes.create') }}" class="erp-btn-primary">Create Delivery Note</a>
                            </x-slot:action>
                        </x-ui.empty-state>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
