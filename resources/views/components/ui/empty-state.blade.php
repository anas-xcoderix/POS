@props(['title' => 'No records found', 'description' => 'Get started by creating your first record.'])

<div class="flex flex-col items-center justify-center px-6 py-16 text-center">
    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
        <x-ui.icon name="box" class="h-7 w-7" />
    </div>
    <h3 class="text-base font-semibold text-slate-800">{{ $title }}</h3>
    <p class="mt-1 max-w-sm text-sm text-slate-500">{{ $description }}</p>
    @if(isset($action))
        <div class="mt-5">{{ $action }}</div>
    @endif
</div>
