@php $title = __('hr.generate_payroll'); @endphp
<x-erp-layout>
<div class="erp-card max-w-lg p-6">
    <h3 class="mb-4 text-lg font-bold text-slate-900">{{ __('hr.generate_payroll') }}</h3>
    <form method="POST" action="{{ route('payroll.store') }}" class="space-y-4">
        @csrf
        <x-ui.form-field :label="__('hr.period_month')" name="period_month" type="select" required>
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" @selected(now()->month == $m)>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
            @endfor
        </x-ui.form-field>
        <x-ui.form-field :label="__('hr.period_year')" name="period_year" type="number" :value="now()->year" required />
        <x-ui.form-field :label="__('ui.branch')" name="branch_id" type="select">
            <option value="">{{ __('hr.all_branches') }}</option>
            @foreach($branches as $b)<option value="{{ $b->id }}">{{ localized($b) }}</option>@endforeach
        </x-ui.form-field>
        <div class="flex gap-3 pt-2">
            <a href="{{ route('payroll.index') }}" class="erp-btn-secondary">{{ __('ui.cancel') }}</a>
            <button type="submit" class="erp-btn-primary">{{ __('hr.generate_payroll') }}</button>
        </div>
    </form>
</div>
</x-erp-layout>
