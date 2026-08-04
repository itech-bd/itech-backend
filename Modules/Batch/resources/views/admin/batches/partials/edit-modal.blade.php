@php
    $modalName = $modalName ?? 'edit-batch';
    $batch = $batch ?? null;
    $show = $show ?? false;
    $isOldEdit = old('_batch_form') === 'edit';
    $batchId = $isOldEdit ? old('_batch_id') : $batch?->getRouteKey();
    $courseTitle = $isOldEdit ? old('_batch_course_title') : $batch?->course?->title;
    $formAction = $batchId ? route('dashboard.batches.update', ['batch' => $batchId]) : '#';
    $selectedDays = $isOldEdit ? old('class_days', []) : (array) ($batch?->class_days ?? []);
    $returnUrl = old('redirect_to', request()->getRequestUri());
    $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
@endphp

<x-modal name="{{ $modalName }}" :show="$show" maxWidth="2xl" focusable>
    <div class="max-h-[calc(100vh-6rem)] overflow-y-auto bg-white">
        <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-slate-100 bg-white px-6 py-5">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Edit Batch</p>
                <h3 class="mt-1 text-xl font-black text-slate-950" data-batch-edit-heading>{{ old('name', $batch?->name ?? 'Update batch') }}</h3>
                <p class="mt-1 text-sm font-semibold text-slate-500" data-batch-edit-course>{{ $courseTitle ? 'Course: '.$courseTitle : 'Update schedule, status and live class link.' }}</p>
            </div>
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: '{{ $modalName }}' }))" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-800">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ $formAction }}" class="space-y-5 px-6 py-5" data-batch-edit-form>
            @csrf
            @method('PUT')
            <input type="hidden" name="_batch_form" value="edit">
            <input type="hidden" name="_batch_id" value="{{ $batchId }}" data-batch-edit-id>
            <input type="hidden" name="_batch_course_title" value="{{ $courseTitle }}" data-batch-edit-course-title>
            <input type="hidden" name="redirect_to" value="{{ $returnUrl }}" data-batch-edit-return>

            <div>
                <label class="block text-sm font-extrabold text-slate-700">Name</label>
                <input name="name" value="{{ old('name', $batch?->name) }}" class="mt-1 w-full rounded-xl border-slate-300 font-semibold focus:border-[#2E3192] focus:ring-[#2E3192]" required data-batch-edit-field="name" />
                @error('name') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-extrabold text-slate-700">Start date</label>
                    <input type="date" name="start_date" value="{{ old('start_date', optional($batch?->start_date)->format('Y-m-d')) }}" class="mt-1 w-full rounded-xl border-slate-300 font-semibold focus:border-[#2E3192] focus:ring-[#2E3192]" required data-batch-edit-field="start_date" />
                    @error('start_date') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-extrabold text-slate-700">End date</label>
                    <input type="date" name="end_date" value="{{ old('end_date', optional($batch?->end_date)->format('Y-m-d')) }}" class="mt-1 w-full rounded-xl border-slate-300 font-semibold focus:border-[#2E3192] focus:ring-[#2E3192]" required data-batch-edit-field="end_date" />
                    @error('end_date') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-extrabold text-slate-700">Class days</label>
                <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach($days as $day)
                        <label class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 transition hover:border-[#2E3192]/30 hover:bg-[#2E3192]/5">
                            <input type="checkbox" name="class_days[]" value="{{ $day }}" class="rounded border-slate-300 text-[#2E3192] focus:ring-[#2E3192]" @checked(in_array($day, $selectedDays, true)) data-batch-edit-day />
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
                    <input name="class_time" value="{{ old('class_time', $batch?->class_time) }}" class="mt-1 w-full rounded-xl border-slate-300 font-semibold focus:border-[#2E3192] focus:ring-[#2E3192]" required data-batch-edit-field="class_time" />
                    @error('class_time') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-extrabold text-slate-700">Status</label>
                    <select name="status" class="mt-1 w-full rounded-xl border-slate-300 font-semibold focus:border-[#2E3192] focus:ring-[#2E3192]" required data-batch-edit-field="status">
                        <option value="upcoming" @selected(old('status', $batch?->status) === 'upcoming')>Upcoming</option>
                        <option value="running" @selected(old('status', $batch?->status) === 'running')>Running</option>
                        <option value="completed" @selected(old('status', $batch?->status) === 'completed')>Completed</option>
                    </select>
                    @error('status') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-extrabold text-slate-700">Live class link (optional)</label>
                <input name="live_class_link" value="{{ old('live_class_link', $batch?->live_class_link) }}" class="mt-1 w-full rounded-xl border-slate-300 font-semibold focus:border-[#2E3192] focus:ring-[#2E3192]" data-batch-edit-field="live_class_link" />
                @error('live_class_link') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-end">
                <button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: '{{ $modalName }}' }))" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#F47B20] px-5 py-2 text-sm font-extrabold text-white shadow-[0_12px_26px_rgba(244,123,32,.22)] transition hover:-translate-y-0.5 hover:bg-[#d96816]">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-modal>

@once
    @push('scripts')
        <script>
            (function () {
                function setField(form, name, value) {
                    var field = form.querySelector('[data-batch-edit-field="' + name + '"]');
                    if (field) {
                        field.value = value || '';
                    }
                }

                window.ItechBatchEditModal = {
                    open: function (trigger) {
                        var form = document.querySelector('[data-batch-edit-form]');
                        if (!form || !trigger) {
                            return;
                        }

                        form.action = trigger.dataset.updateUrl || '#';

                        var idField = form.querySelector('[data-batch-edit-id]');
                        var courseTitleField = form.querySelector('[data-batch-edit-course-title]');
                        var returnField = form.querySelector('[data-batch-edit-return]');
                        var heading = document.querySelector('[data-batch-edit-heading]');
                        var courseLine = document.querySelector('[data-batch-edit-course]');

                        if (idField) {
                            idField.value = trigger.dataset.batchId || '';
                        }
                        if (courseTitleField) {
                            courseTitleField.value = trigger.dataset.courseTitle || '';
                        }
                        if (returnField) {
                            returnField.value = window.location.pathname + window.location.search;
                        }

                        setField(form, 'name', trigger.dataset.name);
                        setField(form, 'start_date', trigger.dataset.startDate);
                        setField(form, 'end_date', trigger.dataset.endDate);
                        setField(form, 'class_time', trigger.dataset.classTime);
                        setField(form, 'live_class_link', trigger.dataset.liveClassLink);
                        setField(form, 'status', trigger.dataset.status);

                        var selectedDays = [];
                        try {
                            selectedDays = JSON.parse(trigger.dataset.classDays || '[]');
                        } catch (_) {
                            selectedDays = [];
                        }

                        form.querySelectorAll('[data-batch-edit-day]').forEach(function (checkbox) {
                            checkbox.checked = selectedDays.indexOf(checkbox.value) !== -1;
                        });

                        if (heading) {
                            heading.textContent = trigger.dataset.name || 'Update batch';
                        }
                        if (courseLine) {
                            courseLine.textContent = trigger.dataset.courseTitle
                                ? 'Course: ' + trigger.dataset.courseTitle
                                : 'Update schedule, status and live class link.';
                        }

                        window.dispatchEvent(new CustomEvent('open-modal', { detail: '{{ $modalName }}' }));
                    }
                };
            })();
        </script>
    @endpush
@endonce
