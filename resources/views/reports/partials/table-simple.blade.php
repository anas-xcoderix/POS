<table class="erp-table min-w-full text-sm">
    <thead class="bg-slate-50/80"><tr>
        @foreach($headers as $h)<th>{{ $h }}</th>@endforeach
    </tr></thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                @foreach($row as $cell)<td>{{ $cell ?? '—' }}</td>@endforeach
            </tr>
        @empty
            <tr><td colspan="{{ count($headers) }}" class="px-4 py-6 text-center text-slate-500">—</td></tr>
        @endforelse
    </tbody>
</table>
