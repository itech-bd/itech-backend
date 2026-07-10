<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 leading-tight">Edit Review</h2>
            <p class="mt-1 text-sm text-slate-500">Update review details.</p>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('dashboard.reviews.update', $review) }}" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700">Name</label>
                <input name="name" value="{{ old('name', $review->name) }}" class="mt-1 w-full rounded-lg border-slate-300" required />
                @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Designation (optional)</label>
                <input name="designation" value="{{ old('designation', $review->designation) }}" class="mt-1 w-full rounded-lg border-slate-300" />
                @error('designation') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Quote</label>
                <textarea name="quote" rows="5" class="mt-1 w-full rounded-lg border-slate-300" required>{{ old('quote', $review->quote) }}</textarea>
                @error('quote') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Rating</label>
                    <select name="rating" class="mt-1 w-full rounded-lg border-slate-300" required>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" @selected((int) old('rating', $review->rating) === $i)>{{ $i }}</option>
                        @endfor
                    </select>
                    @error('rating') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Sort order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $review->sort_order) }}" min="0" class="mt-1 w-full rounded-lg border-slate-300" />
                    @error('sort_order') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Status</label>
                    <select name="status" class="mt-1 w-full rounded-lg border-slate-300" required>
                        <option value="active" @selected(old('status', $review->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $review->status) === 'inactive')>Inactive</option>
                    </select>
                    @error('status') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="/dashboard/reviews" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save</button>
            </div>
        </form>
    </div>
</x-app-layout>
