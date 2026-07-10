<?php

namespace Modules\Course\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Course\Models\Course;

class MyCoursesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $courses = Course::query()
            ->whereHas('batches.students', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->withCount('batches')
            ->orderByDesc('id')
            ->paginate(12);

        return view('course::student.courses.index', compact('courses'));
    }

    public function show(Course $course)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $isEnrolled = $course->batches()
            ->whereHas('students', fn ($q) => $q->where('users.id', $user->id))
            ->exists();

        abort_unless($isEnrolled, 403);

        $course->load(['batches' => function ($query) use ($user) {
            $query->whereHas('students', fn ($q) => $q->where('users.id', $user->id))
                ->withCount(['mentors', 'students'])
                ->orderByDesc('id');
        }]);

        return view('course::student.courses.show', compact('course'));
    }
}
