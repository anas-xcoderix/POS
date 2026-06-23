@component('pdf.masters._layout', ['title' => __('pdf.master_vendor_list'), 'rtl' => true])
<table>
    <thead>
        <tr>
            <th>{{ __('pdf.code') }}</th>
            <th>{{ __('pdf.name') }}</th>
            <th>{{ __('pdf.phone') }}</th>
            <th>{{ __('pdf.balance') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($vendors as $v)
            <tr>
                <td>{{ $v->code }}</td>
                <td>{{ localized($v) }}</td>
                <td>{{ $v->phone }}</td>
                <td>{{ number_format($v->balance, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endcomponent
