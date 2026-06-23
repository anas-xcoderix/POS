@php $title = __('pages.legacy_import.title'); @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-900">{{ __('pages.legacy_import.title') }}</h2>
            <p class="text-sm text-slate-600">{{ __('pages.legacy_import.subtitle') }}</p>
        </div>
        <a href="{{ route('settings.index') }}" class="erp-btn-secondary text-sm">{{ __('pages.legacy_import.back_settings') }}</a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="erp-card p-6">
        <h3 class="mb-3 text-lg font-bold text-slate-900">{{ __('pages.legacy_import.connection') }}</h3>
        <div class="space-y-2 text-sm">
            @foreach($connectionStatus as $name => $status)
                <div class="flex flex-wrap items-center gap-3">
                    <span class="font-medium text-slate-800">{{ $name }}</span>
                    @if($status['ok'])
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 font-medium text-emerald-800">{{ __('pages.legacy_import.connected') }}</span>
                        <span class="text-slate-600">{{ $status['message'] }}</span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 font-medium text-amber-800">{{ __('pages.legacy_import.not_connected') }}</span>
                        <span class="text-slate-600">{{ $status['message'] }}</span>
                    @endif
                </div>
            @endforeach
        </div>
        <p class="mt-3 text-xs text-slate-500">{{ __('pages.legacy_import.cli_hint') }}</p>
        <pre class="mt-2 overflow-x-auto rounded-lg bg-slate-900 p-3 text-xs text-slate-100">php artisan iaapco:import-legacy --inspect
php artisan iaapco:import-legacy --phase=all --force</pre>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="erp-card p-6">
            <h3 class="mb-4 text-lg font-bold text-slate-900">{{ __('pages.legacy_import.run_import') }}</h3>
            <form method="POST" action="{{ route('legacy-import.run') }}" class="space-y-4" onsubmit="return confirm(@json(__('pages.legacy_import.confirm')))">
                @csrf
                <x-ui.form-field label="{{ __('pages.legacy_import.connection_name') }}" name="connection" type="select" required>
                    <option value="legacy_sqlsrv">SQL Server (InventoryHas)</option>
                    <option value="legacy_sqlite">SQLite mock (local test)</option>
                </x-ui.form-field>
                <x-ui.form-field label="{{ __('pages.legacy_import.phase') }}" name="phase" type="select" required>
                    <option value="all">{{ __('pages.legacy_import.phase_all') }}</option>
                    @foreach($phases as $key => $entities)
                        <option value="{{ $key }}">{{ ucfirst($key) }} ({{ implode(', ', $entities) }})</option>
                    @endforeach
                </x-ui.form-field>
                <x-ui.form-field label="{{ __('pages.legacy_import.fresh_maps') }}" name="fresh_maps" type="checkbox" value="1">
                    {{ __('pages.legacy_import.fresh_maps_hint') }}
                </x-ui.form-field>
                <button type="submit" class="erp-btn-primary">{{ __('pages.legacy_import.start') }}</button>
            </form>
        </div>

        <div class="erp-card p-6">
            <h3 class="mb-4 text-lg font-bold text-slate-900">{{ __('pages.legacy_import.id_maps') }}</h3>
            <p class="mb-3 text-sm text-slate-600">{{ __('pages.legacy_import.maps_total', ['count' => number_format($mapCount)]) }}</p>
            @if($mapByEntity->isNotEmpty())
                <div class="max-h-64 overflow-y-auto">
                    <table class="erp-table min-w-full text-sm">
                        <thead class="bg-slate-50/80"><tr><th>{{ __('pages.legacy_import.entity') }}</th><th class="text-right">{{ __('pages.legacy_import.count') }}</th></tr></thead>
                        <tbody>
                            @foreach($mapByEntity as $entity => $total)
                                <tr><td>{{ $entity }}</td><td class="text-right">{{ number_format($total) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-slate-500">{{ __('pages.legacy_import.no_maps') }}</p>
            @endif
        </div>
    </div>

    <div class="erp-card p-6">
        <h3 class="mb-4 text-lg font-bold text-slate-900">{{ __('pages.legacy_import.recent_runs') }}</h3>
        <div class="overflow-x-auto">
            <table class="erp-table min-w-full text-sm">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th>{{ __('pages.legacy_import.started') }}</th>
                        <th>{{ __('pages.legacy_import.connection_name') }}</th>
                        <th>{{ __('pages.legacy_import.phase') }}</th>
                        <th>{{ __('pages.legacy_import.status') }}</th>
                        <th class="text-right">{{ __('pages.legacy_import.imported') }}</th>
                        <th class="text-right">{{ __('pages.legacy_import.skipped') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($importRuns as $run)
                        <tr>
                            <td>{{ $run->started_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $run->connection }}</td>
                            <td>{{ $run->phase }}</td>
                            <td>
                                @if($run->status === 'completed')
                                    <span class="text-emerald-700">{{ $run->status }}</span>
                                @elseif($run->status === 'failed')
                                    <span class="text-red-700">{{ $run->status }}</span>
                                @else
                                    <span class="text-amber-700">{{ $run->status }}</span>
                                @endif
                            </td>
                            <td class="text-right">{{ number_format($run->rows_imported) }}</td>
                            <td class="text-right">{{ number_format($run->rows_skipped) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-slate-500">{{ __('pages.legacy_import.no_runs') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-erp-layout>
