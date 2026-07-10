<?php

namespace Modules\Reviews\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Modules\Reviews\Http\Requests\StoreReviewRequest;
use Modules\Reviews\Http\Requests\UpdateReviewRequest;
use Modules\Reviews\Models\Review;
use Yajra\DataTables\Facades\DataTables;

class ReviewController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:admin|readReview', only: ['index', 'show']),
            new Middleware('role_or_permission:admin|addReview', only: ['create', 'store']),
            new Middleware('role_or_permission:admin|editReview', only: ['edit', 'update']),
            new Middleware('role_or_permission:admin|deleteReview', only: ['destroy']),
        ];
    }

    public function index()
    {
        abort_unless(Gate::allows('viewAny', Review::class), 403);

        if (request()->ajax() && request()->has('draw')) {
            $query = Review::query()
                ->select(['id', 'name', 'designation', 'rating', 'status', 'sort_order', 'created_at'])
                ->latest();

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('rating_stars', function (Review $review) {
                    $full = max(0, min(5, (int) $review->rating));
                    $out = '<div class="inline-flex items-center gap-1 text-amber-500" aria-label="Rating">';
                    for ($i = 0; $i < 5; $i++) {
                        $opacity = $i < $full ? '1' : '0.25';
                        $out .= '<svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" style="opacity:' . $opacity . '">' .
                            '<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.176 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.71c-.783-.57-.38-1.81.588-1.81H7.03a1 1 0 0 0 .951-.69l1.07-3.292Z"/>' .
                            '</svg>';
                    }
                    $out .= '</div>';
                    return $out;
                })
                ->addColumn('status_badge', function (Review $review) {
                    if ($review->status === 'active') {
                        return '<span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">Active</span>';
                    }

                    return '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">Inactive</span>';
                })
                ->addColumn('actions', function (Review $review) {
                    $user = Auth::user();

                    $editUrl = route('dashboard.reviews.edit', $review);
                    $deleteUrl = route('dashboard.reviews.destroy', $review);

                    $buttons = '<div class="inline-flex items-center gap-2">';

                    if ($user && $user->can('update', $review)) {
                        $buttons .= '<a href="' . e($editUrl) . '" class="rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100">Edit</a>';
                    }

                    if ($user && $user->can('delete', $review)) {
                        $buttons .= '<form method="POST" action="' . e($deleteUrl) . '" onsubmit="return confirm(\'Delete this review?\');">'
                            . '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">'
                            . '<input type="hidden" name="_method" value="DELETE">'
                            . '<button type="submit" class="rounded-md border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800 hover:bg-rose-100">Delete</button>'
                            . '</form>';
                    }

                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['rating_stars', 'status_badge', 'actions'])
                ->toJson();
        }

        return view('reviews::reviews.index');
    }

    public function create()
    {
        abort_unless(Gate::allows('create', Review::class), 403);

        return view('reviews::reviews.create');
    }

    public function store(StoreReviewRequest $request)
    {
        abort_unless(Gate::allows('create', Review::class), 403);

        $validated = $request->validated();

        $review = Review::query()->create([
            'name' => $validated['name'],
            'designation' => $validated['designation'] ?? null,
            'quote' => $validated['quote'],
            'rating' => (int) $validated['rating'],
            'status' => $validated['status'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'created_by' => (int) Auth::id(),
        ]);

        return redirect()
            ->route('dashboard.reviews.index')
            ->with('success', 'Review created successfully.');
    }

    public function show(Review $review)
    {
        abort_unless(Gate::allows('view', $review), 403);

        return view('reviews::reviews.show', compact('review'));
    }

    public function edit(Review $review)
    {
        abort_unless(Gate::allows('update', $review), 403);

        return view('reviews::reviews.edit', compact('review'));
    }

    public function update(UpdateReviewRequest $request, Review $review)
    {
        abort_unless(Gate::allows('update', $review), 403);

        $validated = $request->validated();

        $review->update([
            'name' => $validated['name'],
            'designation' => $validated['designation'] ?? null,
            'quote' => $validated['quote'],
            'rating' => (int) $validated['rating'],
            'status' => $validated['status'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()
            ->route('dashboard.reviews.index')
            ->with('success', 'Review updated successfully.');
    }

    public function destroy(Review $review)
    {
        abort_unless(Gate::allows('delete', $review), 403);

        $review->delete();

        return redirect()
            ->route('dashboard.reviews.index')
            ->with('success', 'Review deleted successfully.');
    }
}
