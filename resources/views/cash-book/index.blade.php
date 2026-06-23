@php $title = 'Cash Book'; @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-1 flex-wrap items-end gap-3">
            <x-ui.form-field label="From" name="from" type="date" :value="$from" class="!mb-0" />
            <x-ui.form-field label="To" name="to" type="date" :value="$to" class="!mb-0" />
            <x-ui.form-field label="Branch" name="branch_id" type="select" class="!mb-0">
                <option value="">All</option>
                @foreach($branches as $b)<option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>@endforeach
            </x-ui.form-field>
            <button class="erp-btn-secondary">Filter</button>
        </form>
        <a href="{{ route('cash-book.create') }}" class="erp-btn-primary"><x-ui.icon name="plus" class="h-4 w-4" /> New Entry</a>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Date</th><th>Type</th><th>Account</th><th>Description</th><th>Amount</th><th>Balance</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td>{{ $row->entry_date?->format('Y-m-d') }}</td>
                        <td><span class="erp-badge erp-badge-slate">{{ ucfirst($row->entry_type) }}</span></td>
                        <td>{{ $row->account?->account_code }} {{ $row->account?->name }}</td>
                        <td>{{ Str::limit($row->description ?? $row->reference_no, 40) }}</td>
                        <td class="font-medium">{{ number_format($row->amount, 2) }}</td>
                        <td>{{ $row->running_balance !== null ? number_format($row->running_balance, 2) : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.empty-state title="No cash book entries" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
