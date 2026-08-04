<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Admin &middot; Course Management</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">Add Course</h2>
                <p class="mt-2 text-sm font-semibold leading-6 text-slate-500">Create a course with detailed description, pricing and publishing status.</p>
            </div>

            <a href="{{ route('dashboard.courses.index') }}" class="inline-flex min-h-11 w-fit items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-extrabold text-slate-700 shadow-sm transition hover:border-[#2E3192]/30 hover:bg-[#2E3192]/5 hover:text-[#2E3192]">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                Courses
            </a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('dashboard.courses.store') }}" enctype="multipart/form-data" class="mx-auto max-w-7xl" data-course-create-form>
        @csrf

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                    <div class="mb-5 border-b border-slate-100 pb-4">
                        <h3 class="text-lg font-black text-slate-950">Course Content</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Title, URL slug and full course description.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-extrabold text-slate-700">Title</label>
                            <input data-course-title name="title" value="{{ old('title') }}" class="mt-1 w-full rounded-xl border-slate-300 font-semibold focus:border-[#2E3192] focus:ring-[#2E3192]" required />
                            @error('title') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-extrabold text-slate-700">Slug</label>
                            <input data-course-slug name="slug" value="{{ old('slug') }}" class="mt-1 w-full rounded-xl border-slate-300 font-semibold focus:border-[#2E3192] focus:ring-[#2E3192]" required />
                            @error('slug') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-extrabold text-slate-700">Description</label>
                        <textarea name="description" rows="14" class="wysiwyg mt-1 w-full rounded-xl border-slate-300 focus:border-[#2E3192] focus:ring-[#2E3192]" required>{{ old('description') }}</textarea>
                        @error('description') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                    <div class="mb-5 border-b border-slate-100 pb-4">
                        <h3 class="text-lg font-black text-slate-950">Pricing</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Set separate admission pricing for online and offline batches.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div class="space-y-4 rounded-2xl border border-sky-200 bg-sky-50 p-4">
                            <div class="flex items-center gap-2 text-sm font-extrabold text-sky-800">
                                <i class="fa-solid fa-wifi"></i>
                                Online Pricing
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700">Online old price</label>
                                <input type="number" name="online_old_price" value="{{ old('online_old_price') }}" min="0" step="0.01" class="mt-1 w-full rounded-xl border-slate-300 font-semibold focus:border-sky-500 focus:ring-sky-500" />
                                @error('online_old_price') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700">Online discount price</label>
                                <input type="number" name="online_discount_price" value="{{ old('online_discount_price') }}" min="0" step="0.01" class="mt-1 w-full rounded-xl border-slate-300 font-semibold focus:border-sky-500 focus:ring-sky-500" />
                                @error('online_discount_price') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="space-y-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                            <div class="flex items-center gap-2 text-sm font-extrabold text-amber-800">
                                <i class="fa-solid fa-building"></i>
                                Offline Pricing
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700">Offline old price</label>
                                <input type="number" name="offline_old_price" value="{{ old('offline_old_price') }}" min="0" step="0.01" class="mt-1 w-full rounded-xl border-slate-300 font-semibold focus:border-amber-500 focus:ring-amber-500" />
                                @error('offline_old_price') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700">Offline discount price</label>
                                <input type="number" name="offline_discount_price" value="{{ old('offline_discount_price') }}" min="0" step="0.01" class="mt-1 w-full rounded-xl border-slate-300 font-semibold focus:border-amber-500 focus:ring-amber-500" />
                                @error('offline_discount_price') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="space-y-6 xl:sticky xl:top-32 xl:self-start">
                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 border-b border-slate-100 pb-4">
                        <h3 class="text-base font-black text-slate-950">Publishing</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Control visibility and thumbnail.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-extrabold text-slate-700">Status</label>
                        <select name="status" class="mt-1 w-full rounded-xl border-slate-300 font-semibold focus:border-[#2E3192] focus:ring-[#2E3192]" required>
                            <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                        </select>
                        @error('status') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-extrabold text-slate-700">Thumbnail</label>
                        <input type="file" name="thumbnail" accept="image/*" class="mt-1 block w-full rounded-xl border border-dashed border-slate-300 bg-slate-50 p-3 text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-white file:px-4 file:py-2 file:text-sm file:font-bold file:text-slate-700 hover:file:bg-slate-100" />
                        <p class="mt-2 text-xs font-semibold text-slate-500">JPG, PNG or WEBP. Max size 2MB.</p>
                        @error('thumbnail') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </section>

                <div class="flex flex-col gap-2 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#F47B20] px-5 py-2 text-sm font-extrabold text-white shadow-[0_12px_26px_rgba(244,123,32,.22)] transition hover:-translate-y-0.5 hover:bg-[#d96816]">
                        <i class="fa-solid fa-plus"></i>
                        Create Course
                    </button>
                    <a href="{{ route('dashboard.courses.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50">
                        Cancel
                    </a>
                </div>
            </aside>
        </div>
    </form>

    @push('scripts')
        <script>
            (function () {
                var form = document.querySelector('[data-course-create-form]');
                if (!form || form.dataset.slugSyncReady === 'true') {
                    return;
                }

                var titleInput = form.querySelector('[data-course-title]');
                var slugInput = form.querySelector('[data-course-slug]');
                if (!titleInput || !slugInput) {
                    return;
                }

                form.dataset.slugSyncReady = 'true';

                function slugify(value) {
                    var normalizedValue = String(value || '').toLowerCase().replace(/&/g, ' and ');
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
            })();
        </script>
    @endpush
</x-app-layout>
