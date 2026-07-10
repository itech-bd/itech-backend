<?php

namespace Modules\Batch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Modules\Batch\Models\Batch;

class MyMentorBatchesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        $allowedStatuses = ['upcoming', 'running', 'completed'];
        $activeStatus = (string) request()->query('status', 'upcoming');
        if (! in_array($activeStatus, $allowedStatuses, true)) {
            $activeStatus = 'upcoming';
        }

        if ($activeStatus === 'upcoming') {
            $today = now()->toDateString();

            Batch::query()
                ->where('status', 'upcoming')
                ->whereDate('start_date', '<=', $today)
                ->update(['status' => 'running']);
        }

        $batches = $user->mentorBatches()
            ->where('batches.status', $activeStatus)
            ->with(['course:id,title'])
            ->withCount(['students', 'classSchedules'])
            ->orderByDesc('batches.id')
            ->paginate(12)
            ->withQueryString();

        return view(
            'batch::mentor.batches.index',
            compact('batches', 'activeStatus')
        );
    }

    public function show(Batch $batch)
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        $isAssigned = $user->mentorBatches()->whereKey($batch->id)->exists();
        abort_unless($isAssigned, 403);

        abort_unless(Gate::allows('view', $batch), 403);

        $batch->load(
            [
                'course:id,title',
                'classSchedules' => fn ($q) => $q->orderBy('class_date'),
            ]
        );

        return view('batch::mentor.batches.show', compact('batch'));
    }
}
