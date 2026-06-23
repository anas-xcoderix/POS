@php $title = $ticket->pick_no; @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold">{{ $ticket->pick_no }}</h2>
                <p class="text-sm text-slate-500">Invoice {{ $ticket->salesInvoice?->invoice_no }} · {{ $ticket->salesInvoice?->customer?->name }}</p>
            </div>
            <span class="erp-badge {{ $ticket->status === 'picked' ? 'erp-badge-green' : 'erp-badge-orange' }}">{{ ucfirst($ticket->status) }}</span>
        </div>
        <dl class="mt-4 grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
            <div><dt class="text-slate-500">Branch</dt><dd class="font-medium">{{ $ticket->branch?->name }}</dd></div>
            <div><dt class="text-slate-500">Location</dt><dd class="font-medium">{{ $ticket->location?->code }}</dd></div>
            <div><dt class="text-slate-500">Assigned</dt><dd class="font-medium">{{ $ticket->assignee?->name ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">Picked At</dt><dd class="font-medium">{{ $ticket->picked_at?->format('Y-m-d H:i') ?? '—' }}</dd></div>
        </dl>
    </div>

    @if($ticket->status !== 'picked')
        <form method="POST" action="{{ route('pick-tickets.confirm', $ticket) }}" class="erp-card overflow-hidden">
            @csrf
            <table class="erp-table min-w-full">
                <thead class="bg-slate-50/80"><tr><th>Part</th><th>Ordered</th><th>Picked Qty</th></tr></thead>
                <tbody>
                    @foreach($ticket->items as $item)
                        <tr>
                            <td>{{ $item->part?->part_number }}</td>
                            <td>{{ $item->qty_ordered }}</td>
                            <td><input type="number" step="0.01" min="0" name="picked[{{ $item->id }}]" value="{{ $item->qty_picked ?? $item->qty_ordered }}" class="erp-input !mt-0 w-28"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-slate-100 px-5 py-4">
                <button class="erp-btn-primary">Confirm Pick</button>
            </div>
        </form>
    @else
        <div class="erp-card overflow-hidden">
            <table class="erp-table min-w-full">
                <thead class="bg-slate-50/80"><tr><th>Part</th><th>Ordered</th><th>Picked</th></tr></thead>
                <tbody>
                    @foreach($ticket->items as $item)
                        <tr>
                            <td>{{ $item->part?->part_number }}</td>
                            <td>{{ $item->qty_ordered }}</td>
                            <td>{{ $item->qty_picked }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
</x-erp-layout>
