<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 leading-tight">Edit Course</h2>
            <p class="mt-1 text-sm text-slate-500">Update course details.</p>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('dashboard.courses.update', $course) }}" enctype="multipart/form-data" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700">Title</label>
                <input name="title" value="{{ old('title', $course->title) }}" class="mt-1 w-full rounded-lg border-slate-300" required />
                @error('title') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Slug</label>
                <input name="slug" value="{{ old('slug', $course->slug) }}" class="mt-1 w-full rounded-lg border-slate-300" required />
                <p class="mt-1 text-xs text-slate-500">Use lowercase words with hyphens, for example: computer-hardware-and-networking.</p>
                @error('slug') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Description</label>
                <textarea name="description" rows="5" class="wysiwyg mt-1 w-full rounded-lg border-slate-300" required>{{ old('description', $course->description) }}</textarea>
                @error('description') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="rounded-lg border border-sky-200 bg-sky-50 p-4 space-y-3">
                <div class="text-sm font-semibold text-sky-800">Online Pricing</div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Online old price</label>
                        <input type="number" name="online_old_price" value="{{ old('online_old_price', $course->online_old_price) }}" min="0" step="0.01" class="mt-1 w-full rounded-lg border-slate-300" />
                        @error('online_old_price') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Online discount price</label>
                        <input type="number" name="online_discount_price" value="{{ old('online_discount_price', $course->online_discount_price) }}" min="0" step="0.01" class="mt-1 w-full rounded-lg border-slate-300" />
                        @error('online_discount_price') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 space-y-3">
                <div class="text-sm font-semibold text-amber-800">Offline Pricing</div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Offline old price</label>
                        <input type="number" name="offline_old_price" value="{{ old('offline_old_price', $course->offline_old_price) }}" min="0" step="0.01" class="mt-1 w-full rounded-lg border-slate-300" />
                        @error('offline_old_price') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Offline discount price</label>
                        <input type="number" name="offline_discount_price" value="{{ old('offline_discount_price', $course->offline_discount_price) }}" min="0" step="0.01" class="mt-1 w-full rounded-lg border-slate-300" />
                        @error('offline_discount_price') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Thumbnail</label>

                @php
                    $thumb = $course->thumbnail;
                    if (is_string($thumb)) {
                        $thumb = trim($thumb);
                    }

                    $thumbUrl = null;
                    if (!empty($thumb)) {
                        if (\Illuminate\Support\Str::startsWith($thumb, ['http://', 'https://'])) {
                            $thumbUrl = $thumb;
                        } else {
                            $normalized = ltrim($thumb, '/');
                            if (\Illuminate\Support\Str::startsWith($normalized, 'storage/')) {
                                $normalized = \Illuminate\Support\Str::after($normalized, 'storage/');
                            }
                            $thumbUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($normalized);
                        }
                    }
                @endphp

                @if($thumbUrl)
                    <div class="mt-2">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Current thumbnail</div>
                        <div class="mt-2 overflow-hidden rounded-xl ring-1 ring-slate-200 bg-slate-50">
                            <img src="{{ $thumbUrl }}" alt="{{ $course->title }} thumbnail" class="h-40 w-full object-cover" loading="lazy">
                        </div>
                    </div>
                @endif

                <input type="file" name="thumbnail" accept="image/*" class="mt-3 block w-full rounded-lg border border-slate-300 bg-white file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200" />
                <p class="mt-1 text-xs text-slate-500">Upload a new image to replace the current thumbnail (old one will be deleted). Max size 2MB.</p>
                @error('thumbnail') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Status</label>
                <select name="status" class="mt-1 w-full rounded-lg border-slate-300" required>
                    <option value="active" @selected(old('status', $course->status) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $course->status) === 'inactive')>Inactive</option>
                </select>
                @error('status') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="/dashboard/courses/{{ $course->getRouteKey() }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save</button>
            </div>
        </form>
    </div>
</x-app-layout>
