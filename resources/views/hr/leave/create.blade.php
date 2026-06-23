@php $title = __('hr.new_leave_request'); @endphp
<x-erp-layout>
<div class="max-w-xl">
    <form method="POST" action="{{ route('leave.store') }}" class="erp-card p-6 space-y-4">
        @csrf
        <x-ui.form-field :label="__('hr.employee')" name="employee_id" type="select" required>
            <option value="">{{ __('forms.select') }}</option>
            @foreach($employees as $e)
                <option value="{{ $e->id }}" @selected(old('employee_id') == $e->id)>{{ localized($e) }}</option>
            @endforeach
        </x-ui.form-field>
        <x-ui.form-field :label="__('hr.leave_type')" name="leave_type_id" type="select" required>
            <option value="">{{ __('forms.select') }}</option>
            @foreach($leaveTypes as $t)
                <option value="{{ $t->id }}" @selected(old('leave_type_id') == $t->id)>{{ localized($t) }}</option>
            @endforeach
        </x-ui.form-field>
        <x-ui.form-field :label="__('hr.start_date')" name="start_date" type="date" :value="old('start_date')" required />
        <x-ui.form-field :label="__('hr.end_date')" name="end_date" type="date" :value="old('end_date')" required />
        <x-ui.form-field :label="__('hr.reason')" name="reason" type="textarea">{{ old('reason') }}</x-ui.form-field>
        <div class="flex gap-3">
            <button class="erp-btn-primary">{{ __('ui.save') }}</button>
            <a href="{{ route('leave.index') }}" class="erp-btn-secondary">{{ __('ui.cancel') }}</a>
        </div>
    </form>
</div>
</x-erp-layout>
