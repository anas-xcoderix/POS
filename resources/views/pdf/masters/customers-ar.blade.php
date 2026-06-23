@component('pdf.masters._layout', ['title' => __('pdf.master_customer_list'), 'rtl' => true])
<table>
    <thead>
        <tr>
            <th>{{ __('pdf.code') }}</th>
            <th>{{ __('pdf.name') }}</th>
            <th>{{ __('pdf.phone') }}</th>
            <th>{{ __('ui.type') }}</th>
            <th>{{ __('pdf.credit_limit') }}</th>
            <th>{{ __('pdf.balance') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($customers as $c)
            <tr>
                <td>{{ $c->code }}</td>
                <td>{{ localized($c) }}</td>
                <td>{{ $c->phone }}</td>
                <td>{{ $c->customer_type }}</td>
                <td>{{ number_format($c->credit_limit, 2) }}</td>
                <td>{{ number_format($c->balance, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endcomponent
