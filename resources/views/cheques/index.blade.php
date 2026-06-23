@php $title = __('modules.cheques'); @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <x-ui.form-field label="Status" name="status" type="select" class="!mb-0 min-w-[160px]">
                <option value="">All statuses</option>
                @foreach(['pending', 'cleared', 'bounced', 'cancelled'] as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </x-ui.form-field>
            <button type="submit" class="erp-btn-secondary !py-2.5">Filter</button>
            @if($status)
                <a href="{{ route('cheques.index') }}" class="erp-btn-ghost text-sm">Clear</a>
            @endif
        </form>
        <a href="{{ route('cheques.create') }}" class="erp-btn-primary">
            <x-ui.icon name="plus" class="h-4 w-4" /> Record Cheque
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Cheque No</th><th>Type</th><th>Party</th><th>Branch</th><th>Date</th><th>Due</th><th class="text-right">Amount</th><th>Status</th><th class="text-right">Action</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    @php
                        $statusClass = match($row->status) {
                            'cleared' => 'erp-badge-green',
                            'bounced' => 'erp-badge-red',
                            'cancelled' => 'erp-badge-slate',
                            default => 'erp-badge-amber',
                        };
                    @endphp
                    <tr>
                        <td class="font-semibold">{{ $row->cheque_no }}</td>
                        <td>
                            <span class="erp-badge erp-badge-slate">{{ ucfirst($row->cheque_type) }}</span>
                        </td>
                        <td>
                            @if($row->cheque_type === 'received')
                                <div class="text-sm">{{ $row->customer?->name ?? '—' }}</div>
                            @else
                                <div class="text-sm">{{ $row->vendor?->name ?? '—' }}</div>
                            @endif
                            @if($row->bank_name)
                                <div class="text-xs text-slate-500">{{ $row->bank_name }}</div>
                            @endif
                        </td>
                        <td>{{ $row->branch?->code }}</td>
                        <td>{{ $row->cheque_date?->format('M d, Y') }}</td>
                        <td>{{ $row->due_date?->format('M d, Y') ?? '—' }}</td>
                        <td class="text-right font-medium">{{ number_format($row->amount, 2) }}</td>
                        <td><span class="erp-badge {{ $statusClass }}">{{ ucfirst($row->status) }}</span></td>
                        <td class="text-right">
                            <form method="POST" action="{{ route('cheques.update', $row) }}" class="inline-flex items-center gap-1">
                                @csrf @method('PUT')
                                <select name="status" class="erp-input !mt-0 !py-1.5 text-xs w-28" onchange="this.form.submit()">
                                    @foreach(['pending', 'cleared', 'bounced', 'cancelled'] as $s)
                                        <option value="{{ $s }}" @selected($row->status === $s)>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9">
                        <x-ui.empty-state title="No cheques" description="Record received or issued cheques to track status.">
                            <x-slot:action>
                                <a href="{{ route('cheques.create') }}" class="erp-btn-primary">Record Cheque</a>
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
