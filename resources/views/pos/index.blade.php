@php $title = 'Point of Sale'; @endphp
<x-erp-layout>
<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
    @forelse($terminals as $terminal)
        <div class="erp-card p-6">
            <h3 class="font-bold text-slate-900">{{ $terminal->name }}</h3>
            <p class="text-sm text-slate-500">{{ $terminal->code }} · {{ $terminal->branch?->name }}</p>
            @if($open = $openSessions->get($terminal->id))
                <p class="mt-3 text-sm">Session: <span class="font-medium">{{ $open->session_no }}</span></p>
                <p class="text-sm text-slate-500">Sales: {{ number_format($open->total_sales, 2) }}</p>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('pos.counter', $open) }}" class="erp-btn-primary text-sm">Open Counter</a>
                </div>
            @else
                <form method="POST" action="{{ route('pos.open-session', $terminal) }}" class="mt-4 space-y-3">
                    @csrf
                    <x-ui.form-field label="Opening Float" name="opening_float" type="number" step="0.01" value="0" required />
                    <button class="erp-btn-primary w-full">Open Session</button>
                </form>
            @endif
        </div>
    @empty
        <div class="md:col-span-3"><x-ui.empty-state title="No POS terminals" description="Run Desktop Parity seeder to create terminals." /></div>
    @endforelse
</div>
</x-erp-layout>
