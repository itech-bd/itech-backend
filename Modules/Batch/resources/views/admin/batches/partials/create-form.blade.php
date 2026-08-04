@php
    $modalName = $modalName ?? 'create-batch';
    $includeCourseSelect = $includeCourseSelect ?? ! isset($course);
    $courses = $courses ?? collect();
    $formAction = $formAction ?? (
        $includeCourseSelect
            ? route('dashboard.batches.store')
            : route('dashboard.batches.store.course', $course)
    );
    $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    $selectedDays = old('class_days', []);
    $courseSelectDisabled = $includeCourseSelect && $courses->isEmpty();
@endphp

<form method="POST" action="{{ $formAction }}" class="space-y-5">
    @csrf

    @if($includeCourseSelect)
        <div>
            <label class="block text-sm font-extrabold text-slate-700">Course</label>
            <select name="course_id" class="mt-1 w-full rounded-xl border-slate-300 font-semibold" required @disabled($courseSelectDisabled)>
                <option value="">Select a course...</option>
                @foreach ($courses as $courseOption)
                    <option value="{{ $courseOption->id }}" @selected((string) old('course_id') === (string) $courseOption->id)>
                        {{ $courseOption->title }}{{ $courseOption->status ? ' (' . $courseOption->status . ')' : '' }}
                    </option>
                @endforeach
            </select>
            @error('course_id') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror

            @if($courseSelectDisabled)
                <p class="mt-2 rounded-xl bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800 ring-1 ring-amber-100">
                    No courses found. Please create a course before adding a batch.
                </p>
            @endif
        </div>
    @else
        <div class="rounded-2xl border border-[#2E3192]/10 bg-[#2E3192]/5 px-4 py-3">
            <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#2E3192]/70">Course</div>
            <div class="mt-1 text-sm font-black text-slate-950">{{ $course->title }}</div>
        </div>
    @endif

    <div>
        <label class="block text-sm font-extrabold text-slate-700">Name</label>
        <input name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-xl border-slate-300 font-semibold" required />
        @error('name') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-extrabold text-slate-700">Start date</label>
            <input type="date" name="start_date" value="{{ old('start_date') }}" class="mt-1 w-full rounded-xl border-slate-300 font-semibold" required />
            @error('start_date') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-extrabold text-slate-700">End date</label>
            <input type="date" name="end_date" value="{{ old('end_date') }}" class="mt-1 w-full rounded-xl border-slate-300 font-semibold" required />
            @error('end_date') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-extrabold text-slate-700">Class days</label>
        <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
            @foreach($days as $day)
                <label class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 transition hover:border-[#2E3192]/30 hover:bg-[#2E3192]/5">
                    <input type="checkbox" name="class_days[]" value="{{ $day }}" class="rounded border-slate-300 text-[#2E3192] focus:ring-[#2E3192]" @checked(in_array($day, $selectedDays, true)) />
                    <span>{{ $day }}</span>
                </label>
            @endforeach
        </div>
        @error('class_days') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
        @error('class_days.*') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-extrabold text-slate-700">Class time</label>
            <input name="class_time" value="{{ old('class_time') }}" placeholder="e.g. 8:00 PM" class="mt-1 w-full rounded-xl border-slate-300 font-semibold" required />
            @error('class_time') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-extrabold text-slate-700">Status</label>
            <select name="status" class="mt-1 w-full rounded-xl border-slate-300 font-semibold" required>
                <option value="upcoming" @selected(old('status', 'upcoming') === 'upcoming')>Upcoming</option>
                <option value="running" @selected(old('status') === 'running')>Running</option>
                <option value="completed" @selected(old('status') === 'completed')>Completed</option>
            </select>
            @error('status') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-extrabold text-slate-700">Live class link (optional)</label>
        <input name="live_class_link" value="{{ old('live_class_link') }}" class="mt-1 w-full rounded-xl border-slate-300 font-semibold" />
        @error('live_class_link') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-end">
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: '{{ $modalName }}' }))" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50">
            Cancel
        </button>
        <button type="submit" @disabled($courseSelectDisabled) class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#F47B20] px-5 py-2 text-sm font-extrabold text-white shadow-[0_12px_26px_rgba(244,123,32,.22)] transition hover:-translate-y-0.5 hover:bg-[#d96816] disabled:cursor-not-allowed disabled:opacity-50">
            <i class="fa-solid fa-plus"></i>
            Create Batch
        </button>
    </div>
</form>
