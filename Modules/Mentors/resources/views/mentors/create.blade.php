<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Admin &middot; Mentor Management</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">Add Mentor</h2>
                <p class="mt-2 text-sm font-semibold leading-6 text-slate-500">Create the mentor account and public mentor profile together.</p>
            </div>

            <a href="{{ route('dashboard.mentors.index') }}" class="inline-flex min-h-11 w-fit items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-extrabold text-slate-700 shadow-sm transition hover:border-[#2E3192]/30 hover:bg-[#2E3192]/5 hover:text-[#2E3192]">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                Mentors
            </a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('dashboard.mentors.store') }}" class="mx-auto max-w-7xl">
        @csrf

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_21rem]">
            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                    <div class="mb-5 border-b border-slate-100 pb-4">
                        <h3 class="text-lg font-black text-slate-950">Account Details</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500">A mentor user account will be created from this information.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-extrabold text-slate-700">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full rounded-xl border-slate-300 font-semibold focus:border-[#2E3192] focus:ring-[#2E3192]" placeholder="mentor@example.com" autocomplete="email" spellcheck="false" required />
                            @error('email') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-extrabold text-slate-700">Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-xl border-slate-300 font-semibold focus:border-[#2E3192] focus:ring-[#2E3192]" required />
                            @error('name') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-extrabold text-slate-700">Topic</label>
                        <input type="text" name="topic" value="{{ old('topic') }}" class="mt-1 w-full rounded-xl border-slate-300 font-semibold focus:border-[#2E3192] focus:ring-[#2E3192]" placeholder="e.g., Web Development" />
                        @error('topic') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                    <div class="mb-5 border-b border-slate-100 pb-4">
                        <h3 class="text-lg font-black text-slate-950">Mentor Bio</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Use the full editor for detailed experience, skills and background.</p>
                    </div>

                    <label class="block text-sm font-extrabold text-slate-700">Bio</label>
                    <textarea name="bio" rows="14" class="wysiwyg mt-1 w-full rounded-xl border-slate-300 focus:border-[#2E3192] focus:ring-[#2E3192]" placeholder="Short description...">{{ old('bio') }}</textarea>
                    @error('bio') <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                </section>
            </div>

            <aside class="space-y-6 xl:sticky xl:top-32 xl:self-start">
                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 border-b border-slate-100 pb-4">
                        <h3 class="text-base font-black text-slate-950">Visibility</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Control whether this mentor appears publicly.</p>
                    </div>

                    <label class="flex min-h-12 items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-extrabold text-slate-700">
                        <span>Active (visible)</span>
                        <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-[#2E3192] focus:ring-[#2E3192]" @checked(old('is_active', true)) />
                    </label>

                    <div class="mt-4 rounded-2xl bg-[#2E3192]/5 p-4 text-sm font-semibold leading-6 text-slate-600 ring-1 ring-[#2E3192]/10">
                        Default password is <span class="font-black text-slate-900">12345678</span>. Mentor must change it after login.
                    </div>
                </section>

                <div class="flex flex-col gap-2 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#F47B20] px-5 py-2 text-sm font-extrabold text-white shadow-[0_12px_26px_rgba(244,123,32,.22)] transition hover:-translate-y-0.5 hover:bg-[#d96816]">
                        <i class="fa-solid fa-plus"></i>
                        Save Mentor
                    </button>
                    <a href="{{ route('dashboard.mentors.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50">
                        Cancel
                    </a>
                </div>
            </aside>
        </div>
    </form>
</x-app-layout>
