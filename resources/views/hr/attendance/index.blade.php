@php $title = __('modules.attendance'); @endphp
<x-erp-layout>
<div class="space-y-4">
    <div class="erp-card p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <x-ui.form-field label="Month" name="month" type="select" class="w-40">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected($month == $m)>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                @endfor
            </x-ui.form-field>
            <x-ui.form-field label="Year" name="year" type="number" :value="$year" class="w-28" />
            <button class="erp-btn-primary shrink-0">Load</button>
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
                        <th class="sticky left-0 bg-slate-50">Employee</th>
                        @for($d = 1; $d <= $start->daysInMonth; $d++)
                            <th class="min-w-[90px] text-center">{{ $d }}</th>
                        @endfor
                    </tr></thead>
                    <tbody>
                        @foreach($employees as $ei => $employee)
                            <tr>
                                <td class="sticky left-0 bg-white font-medium whitespace-nowrap">{{ $employee->name }}</td>
                                @for($d = 1; $d <= $start->daysInMonth; $d++)
                                    @php
                                        $date = $start->copy()->day($d)->format('Y-m-d');
                                        $key = $employee->id.'|'.$date;
                                        $existing = $records->get($key);
                                        $idx = $ei * $start->daysInMonth + ($d - 1);
                                    @endphp
                                    <td class="p-1">
                                        <input type="hidden" name="entries[{{ $idx }}][employee_id]" value="{{ $employee->id }}">
                                        <input type="hidden" name="entries[{{ $idx }}][attendance_date]" value="{{ $date }}">
                                        <select name="entries[{{ $idx }}][status]" class="erp-input !mt-0 !py-1 !text-xs w-full">
                                            @foreach(['present' => 'P', 'absent' => 'A', 'leave' => 'L', 'half_day' => '½'] as $val => $lbl)
                                                <option value="{{ $val }}" @selected(($existing?->status ?? 'present') === $val)>{{ $lbl }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit" class="erp-btn-primary">Save Attendance</button>
        </div>
    </form>

    <p class="text-xs text-slate-500">P = Present · A = Absent · L = Leave · ½ = Half day. Fridays excluded from payroll working days.</p>
</div>
</x-erp-layout>
