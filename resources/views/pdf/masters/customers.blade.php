@component('pdf.masters._layout', ['title' => __('pdf.master_customer_list'), 'rtl' => false])
    <thead>
        <tr>
            <th>{{ __('pdf.code') }}</th>
            <th>{{ __('pdf.name') }}</th>
            <th>{{ __('pdf.phone') }}</th>
            <th>{{ __('ui.type') }}</th>
            <th class="text-right">{{ __('pdf.credit_limit') }}</th>
            <th class="text-right">{{ __('pdf.balance') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($customers as $c)
            <tr>
                <td>{{ $c->code }}</td>
                <td>{{ localized($c) }}</td>
                <td>{{ $c->phone }}</td>
                <td>{{ $c->customer_type }}</td>
                <td class="text-right num">{{ number_format($c->credit_limit, 2) }}</td>
                <td class="text-right num">{{ number_format($c->balance, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
@endcomponent
