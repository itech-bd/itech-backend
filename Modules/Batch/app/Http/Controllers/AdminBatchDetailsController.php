<?php

// phpcs:disable

namespace Modules\Batch\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Modules\Batch\Http\Requests\UpdateBatchRequest;
use Modules\Batch\Models\Batch;

class AdminBatchDetailsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:admin|readBatch', only: ['show']),
            new Middleware('role_or_permission:admin|editBatch', only: ['edit', 'update']),
        ];
    }

    public function show(Batch $batch)
    {
        abort_unless(Gate::allows('view', $batch), 403);

        $batch->load([
            'course:id,title',
            'mentors:id,name,email',
            'students:id,name,email',
            'classSchedules' => fn ($q) => $q->orderBy('class_date'),
        ]);

        $course = $batch->course;

        return view('batch::admin.batches.show', compact('course', 'batch'));
    }

    public function edit(Batch $batch)
    {
        abort_unless(Gate::allows('update', $batch), 403);

        $batch->load(['course:id,title']);
        $course = $batch->course;

        return view('batch::admin.batches.edit', compact('course', 'batch'));
    }

    public function update(UpdateBatchRequest $request, Batch $batch)
    {
        abort_unless(Gate::allows('update', $batch), 403);

        $validated = $request->validated();

        $batch->update([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'class_days' => $validated['class_days'],
            'class_time' => $validated['class_time'],
            'live_class_link' => $validated['live_class_link'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('dashboard.batches.show', $batch)
            ->with('success', 'Batch updated successfully');
    }

    public function destroy(Batch $batch)
    {
        abort_unless(Gate::allows('delete', $batch), 403);

        $batch->delete();

        return redirect()
            ->route('dashboard.batches.index')
            ->with('success', 'Batch deleted successfully');
    }
}

// phpcs:enable
