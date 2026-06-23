@php $title = __('nav.pos'); @endphp
<x-erp-layout>
<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
    @forelse($terminals as $terminal)
        <div class="erp-card p-6">
            <h3 class="font-bold text-slate-900">{{ $terminal->name }}</h3>
            <p class="text-sm text-slate-500">{{ $terminal->code }} · {{ $terminal->branch?->name }}</p>
            @if($open = $openSessions->get($terminal->id))
                <p class="mt-3 text-sm">{{ __('pages.pos.session') }}: <span class="font-medium">{{ $open->session_no }}</span></p>
                <p class="text-sm text-slate-500">{{ __('pages.pos.sales') }}: {{ number_format($open->total_sales, 2) }}</p>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('pos.counter', $open) }}" class="erp-btn-primary text-sm">{{ __('pages.actions.open_counter') }}</a>
                </div>
            @else
                <form method="POST" action="{{ route('pos.open-session', $terminal) }}" class="mt-4 space-y-3">
                    @csrf
                    <x-ui.form-field :label="__('forms.opening_float')" name="opening_float" type="number" step="0.01" value="0" required />
                    <button class="erp-btn-primary w-full">{{ __('pages.actions.open_session') }}</button>
                </form>
            @endif
        </div>
    @empty
        <div class="md:col-span-3"><x-ui.empty-state :title="__('pages.empty.pos_terminals')" :description="__('pages.empty.pos_terminals_hint')" /></div>
    @endforelse
</div>
</x-erp-layout>
