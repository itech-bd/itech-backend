@php
    $modalName = $modalName ?? 'create-course';
@endphp

<form method="POST" action="{{ route('dashboard.courses.store') }}" enctype="multipart/form-data" class="space-y-5" data-course-create-form>
    @csrf

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-extrabold text-slate-700">Title</label>
            <input data-course-title name="title" value="{{ old('title') }}" class="mt-1 w-full rounded-xl border-slate-300 font-semibold" required />
            @error('title') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-extrabold text-slate-700">Slug</label>
            <input data-course-slug name="slug" value="{{ old('slug') }}" class="mt-1 w-full rounded-xl border-slate-300 font-semibold" required />
            @error('slug') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-extrabold text-slate-700">Description</label>
        <textarea name="description" rows="5" class="wysiwyg mt-1 w-full rounded-xl border-slate-300" required>{{ old('description') }}</textarea>
        @error('description') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="space-y-3 rounded-2xl border border-sky-200 bg-sky-50 p-4">
            <div class="flex items-center gap-2 text-sm font-extrabold text-sky-800">
                <i class="fa-solid fa-wifi"></i>
                Online Pricing
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-1">
                <div>
                    <label class="block text-sm font-bold text-slate-700">Online old price</label>
                    <input type="number" name="online_old_price" value="{{ old('online_old_price') }}" min="0" step="0.01" class="mt-1 w-full rounded-xl border-slate-300 font-semibold" />
                    @error('online_old_price') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700">Online discount price</label>
                    <input type="number" name="online_discount_price" value="{{ old('online_discount_price') }}" min="0" step="0.01" class="mt-1 w-full rounded-xl border-slate-300 font-semibold" />
                    @error('online_discount_price') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="space-y-3 rounded-2xl border border-amber-200 bg-amber-50 p-4">
            <div class="flex items-center gap-2 text-sm font-extrabold text-amber-800">
                <i class="fa-solid fa-building"></i>
                Offline Pricing
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-1">
                <div>
                    <label class="block text-sm font-bold text-slate-700">Offline old price</label>
                    <input type="number" name="offline_old_price" value="{{ old('offline_old_price') }}" min="0" step="0.01" class="mt-1 w-full rounded-xl border-slate-300 font-semibold" />
                    @error('offline_old_price') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700">Offline discount price</label>
                    <input type="number" name="offline_discount_price" value="{{ old('offline_discount_price') }}" min="0" step="0.01" class="mt-1 w-full rounded-xl border-slate-300 font-semibold" />
                    @error('offline_discount_price') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-extrabold text-slate-700">Thumbnail</label>
        <input type="file" name="thumbnail" accept="image/*" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-bold file:text-slate-700 hover:file:bg-slate-200" />
        <p class="mt-1 text-xs font-semibold text-slate-500">Upload a course thumbnail image (jpg/png/webp). Max size 2MB.</p>
        @error('thumbnail') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-extrabold text-slate-700">Status</label>
        <select name="status" class="mt-1 w-full rounded-xl border-slate-300 font-semibold" required>
            <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
        </select>
        @error('status') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-end">
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: '{{ $modalName }}' }))" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50">
            Cancel
        </button>
        <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#F47B20] px-5 py-2 text-sm font-extrabold text-white shadow-[0_12px_26px_rgba(244,123,32,.22)] transition hover:-translate-y-0.5 hover:bg-[#d96816]">
            <i class="fa-solid fa-plus"></i>
            Create Course
        </button>
    </div>
</form>

@once
    @push('scripts')
        <script>
            (function () {
                function slugify(value) {
                    var normalizedValue = String(value || '')
                        .toLowerCase()
                        .replace(/&/g, ' and ');

                    if (typeof normalizedValue.normalize === 'function') {
                        normalizedValue = normalizedValue.normalize('NFKD');
                    }

                    return normalizedValue
                        .replace(/[\u0300-\u036f]/g, '')
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '')
                        .replace(/-{2,}/g, '-')
                        .slice(0, 255);
                }

                function bindCourseSlugForm(form) {
                    if (!form || form.dataset.slugSyncReady === 'true') {
                        return;
                    }

                    var titleInput = form.querySelector('[data-course-title]');
                    var slugInput = form.querySelector('[data-course-slug]');

                    if (!titleInput || !slugInput) {
                        return;
                    }

                    form.dataset.slugSyncReady = 'true';

                    function syncSlug() {
                        if (slugInput.dataset.manual === 'true') {
                            return;
                        }

                        slugInput.value = slugify(titleInput.value);
                    }

                    var initialGeneratedSlug = slugify(titleInput.value);
                    slugInput.dataset.manual = slugInput.value !== '' && slugInput.value !== initialGeneratedSlug ? 'true' : 'false';

                    titleInput.addEventListener('input', syncSlug);
                    titleInput.addEventListener('change', syncSlug);
                    titleInput.addEventListener('keyup', syncSlug);

                    slugInput.addEventListener('input', function () {
                        var generatedSlug = slugify(titleInput.value);

                        if (slugInput.value === '') {
                            slugInput.dataset.manual = 'false';
                            syncSlug();
                            return;
                        }

                        slugInput.dataset.manual = slugInput.value !== generatedSlug ? 'true' : 'false';
                    });

                    if (slugInput.value === '') {
                        syncSlug();
                    }
                }

                function initCourseCreateForms() {
                    document.querySelectorAll('[data-course-create-form]').forEach(bindCourseSlugForm);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initCourseCreateForms);
                } else {
                    initCourseCreateForms();
                }
            })();
        </script>
    @endpush
@endonce
