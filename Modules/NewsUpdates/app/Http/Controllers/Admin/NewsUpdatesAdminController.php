<?php

namespace Modules\NewsUpdates\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\NewsUpdates\Models\NewsUpdate;

class NewsUpdatesAdminController extends Controller
{
    public function index(): View
    {
        $items = NewsUpdate::query()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('newsupdates::admin.news.index', compact('items'));
    }

    public function create(): View
    {
        $item = new NewsUpdate([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return view('newsupdates::admin.news.form', [
            'item' => $item,
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $slug = $this->makeUniqueSlug($validated['slug'] ?? '', $validated['title']);

        $item = NewsUpdate::query()->create([
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'] ?? null,
            'body' => $validated['body'],
            'status' => $validated['status'],
            'published_at' => $validated['published_at'] ?? null,
            'author_id' => Auth::id(),
        ]);

        return redirect()
            ->route('dashboard.admin.news.edit', $item)
            ->with('success', 'News item created.');
    }

    public function edit(NewsUpdate $newsUpdate): View
    {
        return view('newsupdates::admin.news.form', [
            'item' => $newsUpdate,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, NewsUpdate $newsUpdate): RedirectResponse
    {
        $validated = $this->validatePayload($request, $newsUpdate);

        $slug = $this->makeUniqueSlug($validated['slug'] ?? '', $validated['title'], $newsUpdate->id);

        $newsUpdate->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'] ?? null,
            'body' => $validated['body'],
            'status' => $validated['status'],
            'published_at' => $validated['published_at'] ?? null,
        ]);

        return redirect()
            ->route('dashboard.admin.news.edit', $newsUpdate)
            ->with('success', 'News item updated.');
    }

    public function destroy(NewsUpdate $newsUpdate): RedirectResponse
    {
        $newsUpdate->delete();

        return redirect()
            ->route('dashboard.admin.news.index')
            ->with('success', 'News item deleted.');
    }

    private function validatePayload(Request $request, ?NewsUpdate $existing = null): array
    {
        $uniqueRule = 'unique:news_updates,slug';
        if ($existing) {
            $uniqueRule .= ',' . $existing->id;
        }

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', $uniqueRule],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'body' => ['required', 'string'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
        ]);
    }

    private function makeUniqueSlug(string $slug, string $title, ?int $ignoreId = null): string
    {
        $base = trim($slug) !== '' ? Str::slug($slug) : Str::slug($title);
        $base = $base !== '' ? $base : Str::slug('news');

        $candidate = $base;
        $i = 2;

        while (true) {
            $query = NewsUpdate::query()->where('slug', $candidate);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }

            if (! $query->exists()) {
                return $candidate;
            }

            $candidate = $base . '-' . $i;
            $i++;
        }
    }
}
