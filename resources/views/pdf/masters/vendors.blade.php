@component('pdf.masters._layout', ['title' => __('pdf.master_vendor_list'), 'rtl' => false])
    <thead>
        <tr>
            <th>{{ __('pdf.code') }}</th>
            <th>{{ __('pdf.name') }}</th>
            <th>{{ __('pdf.phone') }}</th>
            <th class="text-right">{{ __('pdf.balance') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($vendors as $v)
            <tr>
                <td>{{ $v->code }}</td>
                <td>{{ localized($v) }}</td>
                <td>{{ $v->phone }}</td>
                <td class="text-right num">{{ number_format($v->balance, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
@endcomponent
