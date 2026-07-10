<?php

namespace Modules\Batch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Modules\Batch\Http\Requests\StoreClassScheduleRequest;
use Modules\Batch\Http\Requests\UpdateClassScheduleRequest;
use Modules\Batch\Models\Batch;
use Modules\Batch\Models\ClassSchedule;
use Yajra\DataTables\Facades\DataTables;

class ClassScheduleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'role_or_permission:admin|readClassSchedule',
                only: ['index', 'show']
            ),
            new Middleware(
                'role_or_permission:admin|addClassSchedule',
                only: ['create', 'store']
            ),
            new Middleware(
                'role_or_permission:admin|editClassSchedule',
                only: ['edit', 'update']
            ),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Batch $batch)
    {
        abort_unless(Gate::allows('view', $batch), 403);
        abort_unless(Gate::allows('viewAny', ClassSchedule::class), 403);

        if (request()->ajax() && request()->has('draw')) {
            $query = ClassSchedule::query()
                ->where('batch_id', $batch->id)
                ->select([
                    'id',
                    'batch_id',
                    'class_date',
                    'topic',
                    'recorded_video_link',
                    'created_at',
                ])
                ->orderBy('class_date');

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('class_date_display', function (ClassSchedule $classSchedule) {
                    return e(optional($classSchedule->class_date)->format('d M Y'));
                })
                ->addColumn('recording_link', function (ClassSchedule $classSchedule) {
                    if ($classSchedule->recorded_video_link) {
                        return '<a class="text-indigo-600 hover:text-indigo-500" href="'
                            . e($classSchedule->recorded_video_link)
                            . '" target="_blank" rel="noreferrer">Recording link</a>';
                    }

                    return '<span class="text-slate-400">-</span>';
                })
                ->addColumn('actions', function (ClassSchedule $classSchedule) use ($batch) {
                    $viewUrl = route('dashboard.batches.schedules.show', [$batch, $classSchedule]);
                    $editUrl = route('dashboard.batches.schedules.edit', [$batch, $classSchedule]);

                    $buttons = '<div class="inline-flex items-center gap-2">'
                        . '<a href="' . e($viewUrl) . '" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">View</a>';

                    if (Gate::allows('update', $classSchedule)) {
                        $buttons .= '<a href="' . e($editUrl) . '" class="rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100">Edit</a>';
                    }

                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['recording_link', 'actions'])
                ->toJson();
        }

        $nextSchedule = $batch->classSchedules()
            ->whereDate('class_date', '>=', today())
            ->orderBy('class_date')
            ->first();

        if (! $nextSchedule) {
            $nextSchedule = $batch->classSchedules()
                ->orderBy('class_date')
                ->first();
        }

        $schedules = $batch->classSchedules()
            ->orderBy('class_date')
            ->get();

        return view('batch::schedules.index', compact('batch', 'nextSchedule', 'schedules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Batch $batch)
    {
        $this->assertCanManageSchedulesForBatch($batch);

        return view('batch::schedules.create', compact('batch'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClassScheduleRequest $request, Batch $batch)
    {
        $this->assertCanManageSchedulesForBatch($batch);

        $validated = $request->validated();

        $schedule = $batch->classSchedules()->create([
            'class_date' => $validated['class_date'],
            'topic' => $validated['topic'],
            'recorded_video_link' => $validated['recorded_video_link'] ?? null,
            'created_by' => (int) Auth::id(),
        ]);

        return redirect()
            ->route('dashboard.batches.schedules.show', [$batch, $schedule])
            ->with('success', 'Class schedule created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show(Batch $batch, ClassSchedule $classSchedule)
    {
        abort_unless(Gate::allows('view', $batch), 403);
        abort_if($classSchedule->batch_id !== $batch->id, 404);
        abort_unless(Gate::allows('view', $classSchedule), 403);

        return view('batch::schedules.show', compact('batch', 'classSchedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Batch $batch, ClassSchedule $classSchedule)
    {
        $this->assertCanManageSchedulesForBatch($batch);
        abort_if($classSchedule->batch_id !== $batch->id, 404);
        abort_unless(Gate::allows('update', $classSchedule), 403);

        return view('batch::schedules.edit', compact('batch', 'classSchedule'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClassScheduleRequest $request, Batch $batch, ClassSchedule $classSchedule)
    {
        $this->assertCanManageSchedulesForBatch($batch);
        abort_if($classSchedule->batch_id !== $batch->id, 404);
        abort_unless(Gate::allows('update', $classSchedule), 403);

        $validated = $request->validated();

        $classSchedule->update([
            'class_date' => $validated['class_date'],
            'topic' => $validated['topic'],
            'recorded_video_link' => $validated['recorded_video_link'] ?? null,
        ]);

        return redirect()
            ->route('dashboard.batches.schedules.show', [$batch, $classSchedule])
            ->with('success', 'Class schedule updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    private function assertCanManageSchedulesForBatch(Batch $batch): void
    {
        $user = Auth::user();
        abort_if(! $user, 403);

        if (! $user instanceof User) {
            abort(403);
        }

        if ($user->hasRole('admin')) {
            return;
        }

        // Batch managers (admin) can manage schedules for any batch.
        if ($user->can('editBatch') || $user->can('addBatch') || $user->can('deleteBatch')) {
            return;
        }

        // Mentors can manage schedules only for assigned batches.
        $isAssignedMentor = $batch->mentors()->where('users.id', $user->id)->exists();
        abort_unless($isAssignedMentor, 403);
    }
}
