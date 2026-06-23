@php $title = __('hr.leave_requests'); @endphp
<x-erp-layout>
<div class="space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-lg font-bold text-slate-900">{{ __('hr.leave_requests') }}</h2>
        <a href="{{ route('leave.create') }}" class="erp-btn-primary">{{ __('hr.new_leave_request') }}</a>
    </div>

    <div class="erp-card overflow-hidden">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>{{ __('hr.employee') }}</th>
                <th>{{ __('hr.leave_type') }}</th>
                <th>{{ __('hr.start_date') }}</th>
                <th>{{ __('hr.end_date') }}</th>
                <th>{{ __('hr.days') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th>{{ __('ui.actions') }}</th>
            </tr></thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td class="font-medium">{{ localized($record->employee) }}</td>
                        <td>{{ localized($record->leaveType) }}</td>
                        <td>{{ $record->start_date->format('Y-m-d') }}</td>
                        <td>{{ $record->end_date->format('Y-m-d') }}</td>
                        <td>{{ $record->days }}</td>
                        <td><span class="erp-badge erp-badge-slate">{{ __('hr.'.$record->status) }}</span></td>
                        <td>
                            @if($record->status === 'pending')
                                <form method="POST" action="{{ route('leave.approve', $record) }}" class="inline">
                                    @csrf
                                    <button class="text-sm text-emerald-600 hover:underline">{{ __('hr.approve') }}</button>
                                </form>
                                <form method="POST" action="{{ route('leave.reject', $record) }}" class="inline ms-2">
                                    @csrf
                                    <button class="text-sm text-red-600 hover:underline">{{ __('hr.reject') }}</button>
                                </form>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-ui.empty-state :title="__('hr.leave_requests')" :description="__('hr.new_leave_request')" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $records->links() }}
</div>
</x-erp-layout>
