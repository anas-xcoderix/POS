@php
    $locale = request('locale', 'en');
    $isAr = $locale === 'ar';
    $title = $isAr ? 'مركز التقارير' : 'Reports Center';
@endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">{{ $isAr ? 'مركز التقارير' : 'Reports Center' }}</h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $isAr ? "{$totalReports} تقرير — بديل لـ Crystal Reports مع تصدير PDF و Excel" : "{$totalReports} reports — Crystal Reports parity with PDF & Excel export" }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.index', ['locale' => 'en']) }}" class="erp-btn-secondary {{ !$isAr ? '!border-orange-400 !text-orange-600' : '' }}">English</a>
                <a href="{{ route('reports.index', ['locale' => 'ar']) }}" class="erp-btn-secondary {{ $isAr ? '!border-orange-400 !text-orange-600' : '' }}">العربية</a>
            </div>
        </div>
    </div>

    @foreach($categories as $catKey => $category)
        <div>
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">
                {{ $isAr ? ($category['label_ar'] ?? $category['label']) : $category['label'] }}
            </h3>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($category['reports'] as $report)
                    <a href="{{ route('reports.show', ['report' => $report['key'], 'locale' => $locale]) }}" class="erp-quick-link group">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-slate-800">{{ $isAr ? ($report['title_ar'] ?? $report['title']) : $report['title'] }}</p>
                            <p class="text-xs text-slate-400">{{ $report['legacy'] ?? '' }}</p>
                        </div>
                        <svg class="h-5 w-5 shrink-0 text-slate-300 group-hover:text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
</x-erp-layout>
