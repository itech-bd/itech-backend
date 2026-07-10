<?php

// phpcs:disable

namespace Modules\Batch\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Batch\Models\Batch;
use Modules\Course\Models\Course;
use Yajra\DataTables\Facades\DataTables;

class AdminBatchesController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:admin|readBatch', only: ['index']),
            new Middleware('role_or_permission:admin|addBatch', only: ['create', 'redirectToCourseCreate']),
        ];
    }

    public function index()
    {
        $allowedStatuses = ['all', 'upcoming', 'running', 'completed'];
        $activeStatus = (string) request()->query('status', 'all');
        if (! in_array($activeStatus, $allowedStatuses, true)) {
            $activeStatus = 'all';
        }

        if ($activeStatus === 'upcoming') {
            $today = now()->toDateString();

            Batch::query()
                ->where('status', 'upcoming')
                ->whereDate('start_date', '<=', $today)
                ->update(['status' => 'running']);
        }

        if (request()->ajax() && request()->has('draw')) {
            $query = Batch::query()
                ->with(['course:id,title'])
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
                ->when($activeStatus !== 'all', fn ($q) => $q->where('status', $activeStatus))
                ->latest();

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('course_title', fn (Batch $batch) => e($batch->course?->title ?? '-'))
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
                ->addColumn('actions', function (Batch $batch) {
                    $openUrl = route('dashboard.batches.show', $batch);

                    $scheduleUrl = route('dashboard.batches.schedules.index', $batch);
                    $mentorsUrl = route('dashboard.batches.mentors.edit', $batch);
                    $studentsUrl = route('dashboard.batches.students.edit', $batch);

                    $buttons = '<div class="inline-flex items-center gap-2">';

                    if ($openUrl) {
                        $buttons .= '<a href="' . e($openUrl) . '" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Open</a>';
                    }

                    $editUrl = route('dashboard.batches.edit', $batch);

                    $buttons .= '<a href="' . e($editUrl) . '" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Edit</a>';

                    $buttons .= '<a href="' . e($scheduleUrl) . '" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Schedule</a>'
                        . '<a href="' . e($mentorsUrl) . '" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Mentors</a>'
                        . '<a href="' . e($studentsUrl) . '" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Students</a>';

                    $deleteUrl = route('dashboard.batches.destroy', $batch);

                    $buttons .= '<form method="POST" action="' . e($deleteUrl) . '" onsubmit="return confirm(\'Are you sure you want to delete this batch?\');" class="inline-block ml-2">'
                        . '<input type="hidden" name="_token" value="' . csrf_token() . '">'
                        . '<input type="hidden" name="_method" value="DELETE">'
                        . '<button type="submit" class="rounded-md border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Delete</button>'
                        . '</form>'
                        . '</div>';

                    return $buttons;
                })
                ->filterColumn('course_title', function ($query, $keyword) {
                    $query->whereHas('course', function ($q) use ($keyword) {
                        $q->where('title', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['batch_display', 'actions'])
                ->toJson();
        }

        return view('batch::admin.batches.all', [
            'activeStatus' => $activeStatus,
        ]);
    }

    public function create(Request $request)
    {
        abort_unless(Gate::allows('create', Batch::class), 403);

        $courses = Course::query()
            ->select(['id', 'title', 'status'])
            ->orderBy('title')
            ->limit(500)
            ->get();

        return view('batch::admin.batches.create_from_all', [
            'courses' => $courses,
        ]);
    }

    public function redirectToCourseCreate(Request $request)
    {
        abort_unless(Gate::allows('create', Batch::class), 403);

        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
        ]);

        $course = Course::query()->findOrFail((int) $validated['course_id']);

        return redirect()->route('dashboard.batches.create.course', $course);
    }
}

// phpcs:enable
