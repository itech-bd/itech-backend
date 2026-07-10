<?php

namespace Modules\Batch\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Modules\Batch\Http\Requests\StoreBatchRequest;
use Modules\Batch\Http\Requests\UpdateBatchRequest;
use Modules\Batch\Models\Batch;
use Modules\Course\Models\Course;
use Yajra\DataTables\Facades\DataTables;

class CourseBatchController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:admin|readBatch', only: ['index', 'show']),
            new Middleware('role_or_permission:admin|addBatch', only: ['create', 'store']),
            new Middleware('role_or_permission:admin|editBatch', only: ['edit', 'update']),
            new Middleware('role_or_permission:admin|deleteBatch', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Course $course)
    {
        abort_unless(Gate::allows('viewAny', Batch::class), 403);

        if (request()->ajax() && request()->has('draw')) {
            $query = Batch::query()
                ->where('course_id', $course->id)
                ->select([
                    'id',
                    'course_id',
                    'name',
                    'class_days',
                    'class_time',
                    'status',
                    'start_date',
                    'end_date',
                    'created_at',
                ])
                ->withCount(['mentors', 'students', 'classSchedules'])
                ->latest();

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('dates', function (Batch $batch) {
                    $start = $batch->start_date ? $batch->start_date->format('d M Y') : '-';
                    $end = $batch->end_date ? $batch->end_date->format('d M Y') : '-';
                    return e($start . ' - ' . $end);
                })
                ->addColumn('batch_display', function (Batch $batch) {
                    $days = (array) ($batch->class_days ?? []);
                    $daysText = $days ? implode(', ', $days) : '-';
                    $timeText = (string) ($batch->class_time ?? '');

                    return '<div class="font-semibold text-slate-900">'
                        . e($batch->name)
                        . '</div><div class="mt-1 text-xs text-slate-500">'
                        . e($daysText)
                        . ' • '
                        . e($timeText)
                        . '</div>';
                })
                ->addColumn('actions', function (Batch $batch) use ($course) {
                    $user = Auth::user();

                    $viewUrl = route('dashboard.batches.show', $batch);
                    $editUrl = route('dashboard.batches.edit', $batch);
                    $deleteUrl = route('dashboard.courses.batches.destroy', [$course, $batch]);
                    $scheduleUrl = route('dashboard.batches.schedules.index', $batch);
                    $mentorsUrl = route('dashboard.batches.mentors.edit', $batch);
                    $studentsUrl = route('dashboard.batches.students.edit', $batch);

                    $buttons = '<div class="inline-flex items-center gap-2">'
                        . '<a href="' . e($viewUrl) . '" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">View</a>';

                    if ($user && $user->can('editBatch')) {
                        $buttons .= '<a href="' . e($editUrl) . '" class="rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100">Edit</a>';
                    }

                    if ($user && $user->can('assignMentorsToBatch')) {
                        $buttons .= '<a href="' . e($mentorsUrl) . '" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Mentors</a>';
                    }

                    if ($user && $user->can('assignStudentsToBatch')) {
                        $buttons .= '<a href="' . e($studentsUrl) . '" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Students</a>';
                    }

                    $buttons .= '<a href="' . e($scheduleUrl) . '" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Schedule</a>';

                    if ($user && $user->can('deleteBatch')) {
                        $buttons .= '<form method="POST" action="' . e($deleteUrl) . '" onsubmit="return confirm(\'Delete this batch?\');">'
                            . '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">'
                            . '<input type="hidden" name="_method" value="DELETE">'
                            . '<button type="submit" class="rounded-md border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800 hover:bg-rose-100">Delete</button>'
                            . '</form>';
                    }

                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['batch_display', 'actions'])
                ->toJson();
        }

        return view('batch::admin.batches.index', compact('course'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Course $course)
    {
        abort_unless(Gate::allows('create', Batch::class), 403);

        return view('batch::admin.batches.create', compact('course'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBatchRequest $request, Course $course)
    {
        abort_unless(Gate::allows('create', Batch::class), 403);

        $validated = $request->validated();

        $adminId = (int) Auth::id();

        $batch = DB::transaction(
            function () use ($course, $validated, $adminId) {
                $batch = Batch::query()->create([
                    'course_id' => $course->id,
                    'name' => $validated['name'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'class_days' => $validated['class_days'],
                    'class_time' => $validated['class_time'],
                    'live_class_link' => $validated['live_class_link'] ?? null,
                    'status' => $validated['status'],
                    'created_by' => $adminId,
                ]);

                // Ensure a newly created batch starts with no assignments.
                // Mentors/students should only be attached manually by an admin.
                $batch->mentors()->detach();
                $batch->students()->detach();

                $batch->autoGenerateClassSchedules($adminId);

                return $batch;
            }
        );

        return redirect()
            ->route('dashboard.batches.index')
            ->with('success', 'Batch created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show(Course $course, Batch $batch)
    {
        $this->assertBatchBelongsToCourse($course, $batch);
        return redirect()->route('dashboard.batches.show', $batch);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course, Batch $batch)
    {
        $this->assertBatchBelongsToCourse($course, $batch);
        return redirect()->route('dashboard.batches.edit', $batch);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBatchRequest $request, Course $course, Batch $batch)
    {
        $this->assertBatchBelongsToCourse($course, $batch);

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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course, Batch $batch)
    {
        $this->assertBatchBelongsToCourse($course, $batch);
        abort_unless(Gate::allows('delete', $batch), 403);

        $batch->delete();

        return redirect()
            ->route('dashboard.courses.batches.index', $course)
            ->with('success', 'Batch deleted successfully.');
    }

    private function assertBatchBelongsToCourse(Course $course, Batch $batch): void
    {
        abort_if($batch->course_id !== $course->id, 404);
    }
}
