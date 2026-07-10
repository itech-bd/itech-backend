<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 leading-tight">Add Course</h2>
            <p class="mt-1 text-sm text-slate-500">Create a new course.</p>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('dashboard.courses.store') }}" enctype="multipart/form-data" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700">Title</label>
                <input id="course-title" name="title" value="{{ old('title') }}" class="mt-1 w-full rounded-lg border-slate-300" required />
                @error('title') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Slug</label>
                <input id="course-slug" name="slug" value="{{ old('slug') }}" class="mt-1 w-full rounded-lg border-slate-300" required />
                @error('slug') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Description</label>
                <textarea name="description" rows="5" class="wysiwyg mt-1 w-full rounded-lg border-slate-300" required>{{ old('description') }}</textarea>
                @error('description') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="rounded-lg border border-sky-200 bg-sky-50 p-4 space-y-3">
                <div class="text-sm font-semibold text-sky-800">Online Pricing</div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Online old price</label>
                        <input type="number" name="online_old_price" value="{{ old('online_old_price') }}" min="0" step="0.01" class="mt-1 w-full rounded-lg border-slate-300" />
                        @error('online_old_price') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Online discount price</label>
                        <input type="number" name="online_discount_price" value="{{ old('online_discount_price') }}" min="0" step="0.01" class="mt-1 w-full rounded-lg border-slate-300" />
                        @error('online_discount_price') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 space-y-3">
                <div class="text-sm font-semibold text-amber-800">Offline Pricing</div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Offline old price</label>
                        <input type="number" name="offline_old_price" value="{{ old('offline_old_price') }}" min="0" step="0.01" class="mt-1 w-full rounded-lg border-slate-300" />
                        @error('offline_old_price') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Offline discount price</label>
                        <input type="number" name="offline_discount_price" value="{{ old('offline_discount_price') }}" min="0" step="0.01" class="mt-1 w-full rounded-lg border-slate-300" />
                        @error('offline_discount_price') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Thumbnail</label>
                <input type="file" name="thumbnail" accept="image/*" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200" />
                <p class="mt-1 text-xs text-slate-500">Upload a course thumbnail image (jpg/png/webp). Max size 2MB.</p>
                @error('thumbnail') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Status</label>
                <select name="status" class="mt-1 w-full rounded-lg border-slate-300" required>
                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                </select>
                @error('status') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="/dashboard/courses" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create</button>
            </div>
        </form>

        <script>
            (function () {
                var titleInput = document.getElementById('course-title');
                var slugInput = document.getElementById('course-slug');

                if (!titleInput || !slugInput || slugInput.dataset.slugSyncReady === 'true') {
                    return;
                }

                slugInput.dataset.slugSyncReady = 'true';

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
    </div>
</x-app-layout>
