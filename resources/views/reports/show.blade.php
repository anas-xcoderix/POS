@php
    $locale = $data['meta']['locale'] ?? 'en';
    $isAr = $locale === 'ar';
    $title = $data['meta']['title'];
    $filters = $def['filters'] ?? [];
    $query = request()->query();
@endphp
<x-erp-layout>
<div class="space-y-4" @if($isAr) dir="rtl" @endif>
    <div class="erp-card p-4">
        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
            <input type="hidden" name="locale" value="{{ $locale }}">
            @if(in_array('from', $filters))
                <x-ui.form-field :label="$isAr ? 'من تاريخ' : 'From'" name="from" type="date" :value="$data['meta']['filters']['from'] ?? ''" />
            @endif
            @if(in_array('to', $filters))
                <x-ui.form-field :label="$isAr ? 'إلى تاريخ' : 'To'" name="to" type="date" :value="$data['meta']['filters']['to'] ?? ''" />
            @endif
            @if(in_array('as_of', $filters))
                <x-ui.form-field :label="$isAr ? 'حتى تاريخ' : 'As of'" name="as_of" type="date" :value="$data['meta']['filters']['as_of'] ?? ''" />
            @endif
            @if(in_array('branch_id', $filters))
                <x-ui.form-field :label="$isAr ? 'الفرع' : 'Branch'" name="branch_id" type="select">
                    <option value="">{{ $isAr ? 'الكل' : 'All' }}</option>
                    @foreach($filterOptions['branches'] as $b)
                        <option value="{{ $b->id }}" @selected(($data['meta']['filters']['branch_id'] ?? '') == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </x-ui.form-field>
            @endif
            @if(in_array('part_id', $filters))
                <x-ui.form-field :label="$isAr ? 'القطعة' : 'Part'" name="part_id" type="select">
                    <option value="">{{ $isAr ? 'الكل' : 'All' }}</option>
                    @foreach($filterOptions['parts'] as $p)
                        <option value="{{ $p->id }}" @selected(($data['meta']['filters']['part_id'] ?? '') == $p->id)>{{ $p->part_number }}</option>
                    @endforeach
                </x-ui.form-field>
            @endif
            @if(in_array('search', $filters))
                <x-ui.form-field :label="$isAr ? 'بحث' : 'Search'" name="search" :value="$data['meta']['filters']['search'] ?? ''" />
            @endif
            @if(in_array('movement_type', $filters))
                <x-ui.form-field :label="$isAr ? 'نوع الحركة' : 'Movement Type'" name="movement_type" type="select">
                    <option value="">{{ $isAr ? 'الكل' : 'All' }}</option>
                    @foreach($filterOptions['movement_types'] as $t)
                        <option value="{{ $t }}" @selected(($data['meta']['filters']['movement_type'] ?? '') == $t)>{{ $t }}</option>
                    @endforeach
                </x-ui.form-field>
            @endif
            @if(in_array('status', $filters))
                <x-ui.form-field :label="$isAr ? 'الحالة' : 'Status'" name="status" type="select">
                    <option value="">{{ $isAr ? 'الكل' : 'All' }}</option>
                    @foreach($filterOptions['job_statuses'] as $s)
                        <option value="{{ $s }}" @selected(($data['meta']['filters']['status'] ?? '') == $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </x-ui.form-field>
            @endif
            @if(in_array('days', $filters))
                <x-ui.form-field :label="$isAr ? 'خلال أيام' : 'Within Days'" name="days" type="number" :value="$data['meta']['filters']['days'] ?? 60" />
            @endif
            <div class="flex items-end gap-2">
                <button class="erp-btn-primary shrink-0">{{ $isAr ? 'تشغيل' : 'Run' }}</button>
            </div>
        </form>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-slate-900">{{ $data['meta']['title'] }}</h2>
            <p class="text-xs text-slate-500">{{ $def['legacy'] ?? '' }} · {{ $data['meta']['generated_at'] }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('reports.index', ['locale' => $locale]) }}" class="erp-btn-secondary text-sm">{{ $isAr ? 'كل التقارير' : 'All Reports' }}</a>
            <a href="{{ route('reports.show', array_merge(['report' => $report], $query, ['locale' => 'en'])) }}" class="erp-btn-ghost text-sm">EN</a>
            <a href="{{ route('reports.show', array_merge(['report' => $report], $query, ['locale' => 'ar'])) }}" class="erp-btn-ghost text-sm">عربي</a>
            <a href="{{ route('reports.pdf', array_merge(['report' => $report], $query)) }}" class="erp-btn-secondary text-sm" target="_blank">{{ __('ui.pdf') }}</a>
            <a href="{{ route('reports.pdf', array_merge(['report' => $report], $query, ['locale' => 'ar'])) }}" class="erp-btn-secondary text-sm" target="_blank">PDF عربي</a>
            <a href="{{ route('reports.csv', array_merge(['report' => $report], $query)) }}" class="erp-btn-primary text-sm">Excel/CSV</a>
        </div>
    </div>

    @include('reports.partials.body', ['data' => $data, 'isAr' => $isAr])
</div>
</x-erp-layout>
