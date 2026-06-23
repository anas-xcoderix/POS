@php $title = __('hr.attendance'); @endphp
<x-erp-layout>
<div class="space-y-4" x-data="{ showTimes: false }">
    <div class="erp-card p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <x-ui.form-field :label="__('hr.period_month')" name="month" type="select" class="w-40">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected($month == $m)>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                @endfor
            </x-ui.form-field>
            <x-ui.form-field :label="__('hr.period_year')" name="year" type="number" :value="$year" class="w-28" />
            <button class="erp-btn-primary shrink-0">{{ __('ui.filter') }}</button>
            <label class="ml-auto flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" x-model="showTimes" class="rounded border-slate-300">
                {{ __('hr.show_check_times') }}
            </label>
        </form>
    </div>

    <form method="POST" action="{{ route('attendance.store') }}">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">

        <div class="erp-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="erp-table min-w-full text-sm">
                    <thead class="bg-slate-50/80"><tr>
                        <th class="sticky left-0 bg-slate-50">{{ __('hr.employee') }}</th>
                        @for($d = 1; $d <= $start->daysInMonth; $d++)
                            <th class="min-w-[100px] text-center">{{ $d }}</th>
                        @endfor
                    </tr></thead>
                    <tbody>
                        @foreach($employees as $ei => $employee)
                            <tr>
                                <td class="sticky left-0 bg-white font-medium whitespace-nowrap">{{ localized($employee) }}</td>
                                @for($d = 1; $d <= $start->daysInMonth; $d++)
                                    @php
                                        $date = $start->copy()->day($d)->format('Y-m-d');
                                        $key = $employee->id.'|'.$date;
                                        $existing = $records->get($key);
                                        $idx = $ei * $start->daysInMonth + ($d - 1);
                                    @endphp
                                    <td class="p-1 align-top">
                                        <input type="hidden" name="entries[{{ $idx }}][employee_id]" value="{{ $employee->id }}">
                                        <input type="hidden" name="entries[{{ $idx }}][attendance_date]" value="{{ $date }}">
                                        <select name="entries[{{ $idx }}][status]" class="erp-input !mt-0 !py-1 !text-xs w-full">
                                            @foreach(['present' => __('hr.present_short'), 'absent' => __('hr.absent_short'), 'leave' => __('hr.leave_short'), 'half_day' => __('hr.half_day_short')] as $val => $lbl)
                                                <option value="{{ $val }}" @selected(($existing?->status ?? 'present') === $val)>{{ $lbl }}</option>
                                            @endforeach
                                        </select>
                                        <div x-show="showTimes" x-cloak class="mt-1 space-y-0.5">
                                            <input type="time" name="entries[{{ $idx }}][check_in]" value="{{ $existing?->check_in ? substr($existing->check_in, 0, 5) : '' }}" class="erp-input !mt-0 !py-0.5 !text-[10px] w-full" placeholder="{{ __('hr.check_in') }}">
                                            <input type="time" name="entries[{{ $idx }}][check_out]" value="{{ $existing?->check_out ? substr($existing->check_out, 0, 5) : '' }}" class="erp-input !mt-0 !py-0.5 !text-[10px] w-full" placeholder="{{ __('hr.check_out') }}">
                                        </div>
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit" class="erp-btn-primary">{{ __('hr.save_attendance') }}</button>
        </div>
    </form>
</div>
</x-erp-layout>
