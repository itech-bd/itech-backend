@props([
    'course',
    'compact' => false,
])

@php

    $title = (string) ($course->title ?? 'Untitled course');
    $slugTitle = \Illuminate\Support\Str::lower($title);
    $track = 'Professional Skill';

    if (\Illuminate\Support\Str::contains($slugTitle, ['graphic', 'design'])) {
        $track = 'Graphic & Multimedia';
    } elseif (\Illuminate\Support\Str::contains($slugTitle, ['marketing', 'seo', 'digital'])) {
        $track = 'Digital Marketing';
    } elseif (\Illuminate\Support\Str::contains($slugTitle, ['hardware', 'network'])) {
        $track = 'Hardware & Networking';
    } elseif (\Illuminate\Support\Str::contains($slugTitle, ['web', '.net', 'dotnet', 'software', 'development'])) {
        $track = 'Web & Software';
    }

    $thumbnailUrl = $course->thumbnail_url ?? null;
    $plainDescription = trim(strip_tags(html_entity_decode((string) ($course->description ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    $description = \Illuminate\Support\Str::limit(preg_replace('/\s+/u', ' ', $plainDescription) ?: 'Build practical skills through mentor-led classes, projects, and review support.', $compact ? 90 : 130);

    $onlinePrice = $course->online_discount_price ?: $course->discount_price;
    $offlinePrice = $course->offline_discount_price ?: $course->discount_price;
    $lowestPrice = collect([$onlinePrice, $offlinePrice, $course->discount_price])->filter(fn ($value) => $value !== null && (float) $value > 0)->map(fn ($value) => (float) $value)->min();

    $batches = $course->relationLoaded('batches') ? $course->batches : collect();
    $activeBatches = $batches
        ->filter(fn ($batch) => in_array($batch->status, ['upcoming', 'running'], true))
        ->sortBy('start_date')
        ->values();

    $nextBatch = $activeBatches->first();
    $batchRotationPayload = $activeBatches->map(fn ($batch) => [
        'start_date' => optional($batch->start_date)->format('d M Y') ?: 'Announced soon',
        'class_time' => $batch->class_time ?: 'Contact office',
    ])->all();
@endphp

<article {{ $attributes->merge(['class' => 'group overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#f15a24]/40 hover:shadow-xl hover:shadow-[#292b86]/10']) }}>
    <a href="{{ route('courses.show', $course) }}" class="block">
        <div class="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-[#292b86] via-[#4b4bb1] to-[#f15a24]">
            @if($thumbnailUrl)
                <img src="{{ $thumbnailUrl }}" alt="{{ $title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-slate-950/5 to-transparent"></div>
            @else
                <div class="absolute inset-0 opacity-30 [background-image:radial-gradient(#ffffff_1px,transparent_1px)] [background-size:18px_18px]"></div>
                <div class="flex h-full items-center justify-center p-8 text-center text-2xl font-black text-white">
                    {{ $title }}
                </div>
            @endif

            <div class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-xs font-extrabold text-[#292b86] shadow-sm">
                {{ $track }}
            </div>

            @if($lowestPrice)
                <div class="absolute bottom-4 right-4 rounded-full bg-[#ed1c24] px-3 py-1 text-xs font-extrabold text-white shadow-lg">
                    ৳{{ number_format($lowestPrice, 0) }}
                </div>
            @endif
        </div>
    </a>

    <div class="p-5">
        <div class="flex items-start justify-between gap-3">
            <h3 class="line-clamp-2 text-lg font-extrabold leading-snug text-slate-950">
                <a href="{{ route('courses.show', $course) }}" class="hover:text-[#292b86]">{{ $title }}</a>
            </h3>
        </div>

        <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">{{ $description }}</p>

        <div
            class="mt-5 grid gap-2 text-xs text-slate-600 sm:grid-cols-2"
            x-data="batchRotator({{ \Illuminate\Support\Js::from($batchRotationPayload) }})"
        >
            @if($nextBatch)
                <div class="rounded-2xl bg-[#292b86]/5 px-3 py-2">
                    <span class="block font-bold text-slate-900">Next batch</span>
                    <span x-text="currentBatch?.start_date || '{{ optional($nextBatch->start_date)->format('d M Y') ?: 'Announced soon' }}'">{{ optional($nextBatch->start_date)->format('d M Y') ?: 'Announced soon' }}</span>
                </div>
                <div class="rounded-2xl bg-[#f15a24]/10 px-3 py-2">
                    <span class="block font-bold text-slate-900">Class time</span>
                    <span x-text="currentBatch?.class_time || '{{ $nextBatch->class_time ?: 'Contact office' }}'">{{ $nextBatch->class_time ?: 'Contact office' }}</span>
                </div>
            @else
                <div class="rounded-2xl bg-slate-50 px-3 py-2 sm:col-span-2">
                    <span class="block font-bold text-slate-900">Batch update</span>
                    <span>Contact office for the next schedule.</span>
                </div>
            @endif
        </div>

        <div class="mt-5 flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
            <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-[#292b86] transition group-hover:text-[#f15a24]">
                View details <span aria-hidden="true">&rarr;</span>
            </a>

            @auth
                <a href="{{ route('checkout.show', $course) }}" class="rounded-full bg-[#f15a24] px-4 py-2 text-xs font-extrabold text-white transition hover:bg-[#ed1c24]">Enroll</a>
            @else
                <button type="button" data-auth-trigger="login" class="rounded-full bg-[#f15a24] px-4 py-2 text-xs font-extrabold text-white transition hover:bg-[#ed1c24]">Enroll</button>
            @endauth
        </div>
    </div>
</article>
