@php $title = __('nav.cash_book'); @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-1 flex-wrap items-end gap-3">
            <x-ui.form-field :label="__('ui.from')" name="from" type="date" :value="$from" class="!mb-0" />
            <x-ui.form-field :label="__('ui.to')" name="to" type="date" :value="$to" class="!mb-0" />
            <x-ui.form-field :label="__('ui.branch')" name="branch_id" type="select" class="!mb-0">
                <option value="">{{ __('ui.all') }}</option>
                @foreach($branches as $b)<option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>@endforeach
            </x-ui.form-field>
            <button class="erp-btn-secondary">{{ __('ui.filter') }}</button>
        </form>
        <a href="{{ route('cash-book.create') }}" class="erp-btn-primary"><x-ui.icon name="plus" class="h-4 w-4" /> {{ __('pages.actions.new_entry') }}</a>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>{{ __('ui.date') }}</th><th>{{ __('ui.type') }}</th><th>{{ __('pages.table.account') }}</th><th>{{ __('ui.description') }}</th><th>{{ __('ui.amount') }}</th><th>{{ __('ui.balance') }}</th>
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
                    <tr><td colspan="6"><x-ui.empty-state :title="__('pages.empty.cash_book')" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
