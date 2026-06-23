@component('pdf.masters._layout', ['title' => __('pdf.master_parts_list_ar'), 'rtl' => true])
    <thead>
        <tr>
            <th>{{ __('pdf.part_no') }}</th>
            <th>{{ __('pdf.oem') }}</th>
            <th>{{ __('pdf.description') }}</th>
            <th>{{ __('pdf.brand') }}</th>
            <th class="text-right">{{ __('pdf.list_price') }}</th>
            <th class="text-right">{{ __('pdf.cost') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($parts as $p)
            <tr>
                <td>{{ $p->part_number }}</td>
                <td>{{ $p->oem_no }}</td>
                <td>{{ $p->description_ar ?: $p->description_en }}</td>
                <td>{{ $p->brand?->name }}</td>
                <td class="text-right num">{{ number_format($p->list_price, 2) }}</td>
                <td class="text-right num">{{ number_format($p->cost_price, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
@endcomponent
