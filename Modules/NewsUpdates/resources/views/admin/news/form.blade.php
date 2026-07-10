@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">
                {{ $mode === 'create' ? 'Add News' : 'Edit News' }}
            </h1>
            <p class="mt-1 text-sm text-slate-600">News & Updates will appear on the website when published.</p>
        </div>
        <a href="{{ route('dashboard.admin.news.index') }}" class="inline-flex items-center rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-800 ring-1 ring-slate-200 hover:bg-slate-200">Back</a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-800 ring-1 ring-rose-200">
            <div class="font-semibold">Please fix the errors below.</div>
        </div>
    @endif

    <form method="POST" action="{{ $mode === 'create' ? route('dashboard.admin.news.store') : route('dashboard.admin.news.update', $item) }}" class="space-y-6">
        @csrf
        @if($mode !== 'create')
            @method('PATCH')
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <label class="block text-sm font-semibold text-slate-700">Title</label>
                    <input name="title" value="{{ old('title', $item->title) }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('title')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <label class="block text-sm font-semibold text-slate-700">Excerpt (optional)</label>
                    <textarea name="excerpt" rows="3" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('excerpt', $item->excerpt) }}</textarea>
                    @error('excerpt')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <label class="block text-sm font-semibold text-slate-700">Body</label>
                    <textarea name="body" rows="14" class="wysiwyg mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('body', $item->body) }}</textarea>
                    @error('body')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <label class="block text-sm font-semibold text-slate-700">Slug (optional)</label>
                    <input name="slug" value="{{ old('slug', $item->slug) }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="auto-from-title" />
                    @error('slug')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    <p class="mt-2 text-xs text-slate-500">Leave empty to auto-generate from title.</p>
                </div>

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <label class="block text-sm font-semibold text-slate-700">Status</label>
                    <select name="status" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @php $statusVal = old('status', $item->status ?: 'published'); @endphp
                        <option value="draft" @selected($statusVal === 'draft')>Draft</option>
                        <option value="published" @selected($statusVal === 'published')>Published</option>
                    </select>
                    @error('status')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <label class="block text-sm font-semibold text-slate-700">Published At</label>
                    @php
                        $publishedVal = old('published_at', optional($item->published_at)->format('Y-m-d\TH:i'));
                    @endphp
                    <input type="datetime-local" name="published_at" value="{{ $publishedVal }}" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('published_at')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    <p class="mt-2 text-xs text-slate-500">Optional. If empty, it can still be published but may not sort as expected.</p>
                </div>

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        {{ $mode === 'create' ? 'Create' : 'Save Changes' }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
