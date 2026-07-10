<?php

namespace Modules\Batch\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Modules\Batch\Models\Batch;

/**
 * Manage a single live class link per batch.
 */
class BatchLiveClassLinkController extends Controller implements HasMiddleware
{
    /**
     * @return array<int, \Illuminate\Routing\Controllers\Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware(
                'role_or_permission:admin|editClassSchedule',
                only: ['edit', 'update']
            ),
        ];
    }

    /**
     * Show the edit form.
     */
    public function edit(Batch $batch)
    {
        abort_unless(Gate::allows('view', $batch), 403);
        abort_unless(Gate::allows('updateLiveClassLink', $batch), 403);

        return view('batch::batches.live_link_edit', compact('batch'));
    }

    /**
     * Update the live class link for the batch.
     */
    public function update(Request $request, Batch $batch)
    {
        abort_unless(Gate::allows('view', $batch), 403);
        abort_unless(Gate::allows('updateLiveClassLink', $batch), 403);

        $validated = $request->validate(
            [
                'live_class_link' => ['nullable', 'url', 'max:2048'],
            ]
        );

        $batch->update(
            [
                'live_class_link' => $validated['live_class_link'] ?? null,
            ]
        );

        return redirect()
            ->route('dashboard.batches.schedules.index', $batch)
            ->with('success', 'Live class link updated successfully.');
    }
}
