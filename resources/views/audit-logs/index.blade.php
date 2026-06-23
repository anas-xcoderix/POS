@php $title = __('nav.audit_logs'); @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="border-b border-slate-100 px-5 py-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <x-ui.form-field label="{{ __('ui.from') }}" name="from" type="date" :value="$from" class="!mb-0" />
            <x-ui.form-field label="{{ __('ui.to') }}" name="to" type="date" :value="$to" class="!mb-0" />
            <x-ui.form-field label="User" name="user_id" type="select" class="!mb-0">
                <option value="">{{ __('pages.filter.all_users') }}</option>
                @foreach($users as $u)<option value="{{ $u->id }}" @selected($userId == $u->id)>{{ $u->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Action" name="action" :value="$action" placeholder="e.g. sales.posted" class="!mb-0" />
            <x-ui.form-field label="Document No" name="document_no" :value="$documentNo" class="!mb-0" />
            <button class="erp-btn-secondary">{{ __('ui.filter') }}</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full text-sm">
            <thead class="bg-slate-50/80"><tr>
                <th>Time</th><th>{{ __('pages.table.user') }}</th><th>{{ __('pages.table.action') }}</th><th>Document</th><th>Remarks</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="whitespace-nowrap">{{ $row->created_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $row->user?->name ?? 'System' }}</td>
                        <td><code class="text-xs">{{ $row->action }}</code></td>
                        <td>{{ $row->document_no ?? '—' }}</td>
                        <td>{{ Str::limit($row->remarks, 60) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-ui.empty-state title="{{ __('pages.empty.audit_logs') }}" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
