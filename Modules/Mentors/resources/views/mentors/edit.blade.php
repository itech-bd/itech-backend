<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 leading-tight">Edit Mentor</h2>
            <p class="mt-1 text-sm text-slate-500">Update mentor details.</p>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <form method="POST" action="{{ route('dashboard.mentors.update', $mentor) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                @php
                    $currentImageUrl = optional($mentor->user)->profile_image_url;
                @endphp

                <div>
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', optional($mentor->user)->email) }}" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="mentor@example.com" required />
                    @error('email')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Name</label>
                    <input type="text" name="name" value="{{ old('name', $mentor->name) }}" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" required />
                    @error('name')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Profile URL</label>
                    <input type="text" name="slug" value="{{ old('slug', $mentor->slug) }}" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g., khayrul-bashar" autocomplete="off" spellcheck="false" />
                    <p class="mt-1 text-xs text-slate-500">
                        Public URL: {{ url('/mentors/' . ($mentor->slug ?: 'mentor-slug')) }}
                    </p>
                    @error('slug')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Topic</label>
                    <input type="text" name="topic" value="{{ old('topic', $mentor->topic) }}" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g., Web Development" />
                    @error('topic')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Bio</label>
                    <textarea name="bio" rows="5" class="wysiwyg mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Short description...">{{ old('bio', $mentor->bio) }}</textarea>
                    @error('bio')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Mentor Image</label>

                    @if (is_string($currentImageUrl) && $currentImageUrl !== '')
                        <div class="mt-3 flex items-center gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <img
                                src="{{ $currentImageUrl }}"
                                alt="{{ $mentor->name }}"
                                class="h-20 w-20 rounded-2xl object-cover ring-1 ring-slate-200"
                            />
                            <div>
                                <div class="text-sm font-medium text-slate-800">Current image</div>
                                <label class="mt-2 inline-flex items-center gap-2 text-sm text-slate-600">
                                    <input type="checkbox" name="remove_profile_image" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500" @checked(old('remove_profile_image')) />
                                    Remove current image
                                </label>
                            </div>
                        </div>
                    @endif

                    <input type="file" name="profile_image" accept="image/png,image/jpeg,image/webp" class="mt-3 block w-full text-sm text-slate-700" />
                    <p class="mt-2 text-xs text-slate-500">Accepted: JPG, PNG, WEBP. Max size: 2MB.</p>
                    @error('profile_image')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                    @error('remove_profile_image')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_active', $mentor->is_active)) />
                    <label for="is_active" class="text-sm text-slate-700">Active (visible)</label>
                </div>

                <div class="flex items-center gap-3">
                    <a href="/dashboard/mentors" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Update</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
