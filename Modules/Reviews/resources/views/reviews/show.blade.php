<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="min-w-0">
                <h2 class="text-xl font-semibold text-slate-900 leading-tight truncate">Review</h2>
                <p class="mt-1 text-sm text-slate-500">Preview review details.</p>
            </div>

            <div class="flex items-center gap-2">
                @can('editReview')
                    <a href="/dashboard/reviews/{{ $review->getRouteKey() }}/edit" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100">Edit</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="text-sm font-semibold text-slate-900">{{ $review->name }}</div>
        @if($review->designation)
            <div class="mt-1 text-xs text-slate-500">{{ $review->designation }}</div>
        @endif
        <div class="mt-4 text-sm text-slate-700 whitespace-pre-line">{{ $review->quote }}</div>
        <div class="mt-4 text-xs text-slate-500">Rating: {{ $review->rating }} • Status: {{ $review->status }} • Sort: {{ $review->sort_order }}</div>
    </div>
</x-app-layout>
