@if ($items->isEmpty())
    <span class="text-sm text-slate-400">No {{ $label }} assigned</span>
@else
    <div class="flex max-w-xl flex-wrap gap-1.5">
        @foreach ($items->sortBy('name')->take(3) as $item)
            <span class="break-all rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-medium text-slate-600">{{ $item->name }}</span>
        @endforeach
    </div>
    @if ($items->count() > 3)
        <details class="mt-2 text-xs">
            <summary class="cursor-pointer font-semibold text-indigo-700">{{ $items->count() - 3 }} more {{ $label }}</summary>
            <div class="mt-2 flex max-w-xl flex-wrap gap-1.5">
                @foreach ($items->sortBy('name')->skip(3) as $item)
                    <span class="break-all rounded-md bg-slate-50 px-2 py-1 text-slate-600">{{ $item->name }}</span>
                @endforeach
            </div>
        </details>
    @endif
@endif
