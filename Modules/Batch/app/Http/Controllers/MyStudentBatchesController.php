<?php

namespace Modules\Batch\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Modules\Batch\Models\Batch;

class MyStudentBatchesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $batches = $user->studentBatches()
            ->wherePivotIn('status', ['pending', 'approved'])
            ->with(['course:id,title'])
            ->withCount(['classSchedules', 'mentors'])
            ->orderByDesc('batches.id')
            ->paginate(12);

        return view('batch::student.batches.index', compact('batches'));
    }

    public function show(Batch $batch)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $isEnrolled = $user->studentBatches()
            ->wherePivotIn('status', ['pending', 'approved'])
            ->whereKey($batch->id)
            ->exists();
        abort_unless($isEnrolled, 403);

        abort_unless(Gate::allows('view', $batch), 403);

        $batch->load([
            'course:id,title',
            'mentors:id,name,email',
            'classSchedules' => fn ($q) => $q->orderBy('class_date'),
        ]);

        return view('batch::student.batches.show', compact('batch'));
    }
}
