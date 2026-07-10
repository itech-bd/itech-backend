<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 leading-tight">Add Review</h2>
            <p class="mt-1 text-sm text-slate-500">Create a new public review.</p>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('dashboard.reviews.store') }}" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700">Name</label>
                <input name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-lg border-slate-300" required />
                @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Designation (optional)</label>
                <input name="designation" value="{{ old('designation') }}" class="mt-1 w-full rounded-lg border-slate-300" />
                @error('designation') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Quote</label>
                <textarea name="quote" rows="5" class="mt-1 w-full rounded-lg border-slate-300" required>{{ old('quote') }}</textarea>
                @error('quote') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Rating</label>
                    <select name="rating" class="mt-1 w-full rounded-lg border-slate-300" required>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" @selected((int) old('rating', 5) === $i)>{{ $i }}</option>
                        @endfor
                    </select>
                    @error('rating') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Sort order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="mt-1 w-full rounded-lg border-slate-300" />
                    @error('sort_order') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Status</label>
                    <select name="status" class="mt-1 w-full rounded-lg border-slate-300" required>
                        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                    @error('status') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="/dashboard/reviews" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create</button>
            </div>
        </form>
    </div>
</x-app-layout>
