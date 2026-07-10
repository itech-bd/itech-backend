<?php

namespace Modules\Batch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Modules\Batch\Http\Requests\UpdateBatchStudentsRequest;
use Modules\Batch\Models\Batch;

/**
 * Batch student assignment controller.
 *
 * @category Controller
 * @package  Modules\Batch\Http\Controllers
 * @author   Unknown <unknown@example.invalid>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://laravel.com
 */
class BatchStudentAssignmentController extends Controller implements HasMiddleware
{
    /**
     * Define controller middleware.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:admin|assignStudentsToBatch'),
        ];
    }

    /**
     * Display the batch students management page.
     *
     * @param Batch $batch The batch model.
     *
     * @return View
     */
    public function edit(Batch $batch): View
    {
        abort_unless(Gate::allows('assignStudents', $batch), 403);

        $approvedStudents = $batch->students()
            ->wherePivot('status', 'approved')
            ->orderBy('users.name')
            ->get(['users.id', 'users.name', 'users.email']);

        $pendingStudents = $batch->students()
            ->wherePivot('status', 'pending')
            ->orderBy('users.name')
            ->get(['users.id', 'users.name', 'users.email']);

        $existingStudentIds = $batch->students()
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $availableStudents = User::query()
            ->role('student')
            ->when(
                count($existingStudentIds) > 0,
                fn ($q) => $q->whereNotIn('id', $existingStudentIds),
                fn ($q) => $q
            )
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'name', 'email']);

        return view(
            'batch::admin.batches.students',
            compact(
                'batch',
                'approvedStudents',
                'pendingStudents',
                'availableStudents'
            )
        );
    }

    /**
     * Add (admit) a student to the batch.
     *
     * @param Request $request The incoming request.
     * @param Batch   $batch   The batch model.
     *
     * @return RedirectResponse
     */
    public function add(Request $request, Batch $batch): RedirectResponse
    {
        abort_unless(Gate::allows('assignStudents', $batch), 403);

        $adminId = (int) Auth::id();
        abort_unless($adminId > 0, 403);

        $validated = $request->validate(
            [
                'student_id' => ['required', 'integer', 'exists:users,id'],
                'batch_type' => ['nullable', 'in:online,offline'],
            ]
        );

        $studentId = (int) $validated['student_id'];
        $batchType = $validated['batch_type'] ?? null;
        $student = User::query()->findOrFail($studentId);
        abort_unless($student->hasRole('student'), 422);

        $existing = DB::table('batch_students')
            ->where('batch_id', $batch->id)
            ->where('student_id', $studentId)
            ->first();

        if ($existing && ($existing->status ?? null) === 'approved') {
            return redirect()
                ->route('dashboard.batches.students.edit', $batch)
                ->withErrors(
                    ['student_id' => 'Student is already admitted to this batch.']
                );
        }

        $updated = DB::table('batch_students')
            ->where('batch_id', $batch->id)
            ->where('student_id', $student->id)
            ->update(
                [
                    'status' => 'approved',
                    'batch_type' => $batchType,
                    'approved_at' => now(),
                    'approved_by' => $adminId,
                    'updated_at' => now(),
                ]
            );

        if ($updated === 0) {
            DB::table('batch_students')->insert(
                [
                    'batch_id' => $batch->id,
                    'student_id' => $student->id,
                    'status' => 'approved',
                    'batch_type' => $batchType,
                    'approved_at' => now(),
                    'approved_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        return redirect()
            ->route('dashboard.batches.students.edit', $batch)
            ->with('success', 'Student added successfully.');
    }

    /**
     * Update the batch type for an admitted student.
     *
     * @param Request $request The incoming request.
     * @param Batch   $batch   The batch model.
     * @param User    $student The student user.
     *
     * @return RedirectResponse
     */
    public function updateBatchType(Request $request, Batch $batch, User $student): RedirectResponse
    {
        abort_unless(Gate::allows('assignStudents', $batch), 403);

        $validated = $request->validate([
            'batch_type' => ['nullable', 'in:online,offline'],
        ]);

        DB::table('batch_students')
            ->where('batch_id', $batch->id)
            ->where('student_id', $student->id)
            ->update(['batch_type' => $validated['batch_type'] ?? null, 'updated_at' => now()]);

        return redirect()
            ->route('dashboard.batches.students.edit', $batch)
            ->with('success', 'Student type updated.');
    }

    /**
     * Remove a student from the batch.
     *
     * @param Batch $batch   The batch model.
     * @param User  $student The student user.
     *
     * @return RedirectResponse
     */
    public function remove(Batch $batch, User $student): RedirectResponse
    {
        abort_unless(Gate::allows('assignStudents', $batch), 403);

        DB::table('batch_students')
            ->where('batch_id', $batch->id)
            ->where('student_id', $student->id)
            ->delete();

        return redirect()
            ->route('dashboard.batches.students.edit', $batch)
            ->with('success', 'Student removed successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateBatchStudentsRequest $request The validated request.
     * @param Batch                      $batch   The batch model.
     *
     * @return RedirectResponse
     */
    public function update(
        UpdateBatchStudentsRequest $request,
        Batch $batch
    ): RedirectResponse {
        abort_unless(Gate::allows('assignStudents', $batch), 403);

        $adminId = (int) Auth::id();
        abort_unless($adminId > 0, 403);

        $studentIds = array_map(
            'intval',
            $request->validated()['student_ids'] ?? []
        );
        $allowedStudentIds = User::query()
            ->role('student')
            ->whereIn('id', $studentIds)
            ->pluck('id')
            ->all();

        // Remove approved students not selected (keep pending requests intact).
        DB::table('batch_students')
            ->where('batch_id', $batch->id)
            ->where('status', 'approved')
            ->when(
                count($allowedStudentIds) > 0,
                fn ($q) => $q->whereNotIn('student_id', $allowedStudentIds),
                fn ($q) => $q
            )
            ->delete();

        foreach ($allowedStudentIds as $studentId) {
            $studentId = (int) $studentId;

            $updated = DB::table('batch_students')
                ->where('batch_id', $batch->id)
                ->where('student_id', $studentId)
                ->update(
                    [
                        'status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => $adminId,
                        'updated_at' => now(),
                    ]
                );

            if ($updated === 0) {
                DB::table('batch_students')->insert(
                    [
                        'batch_id' => $batch->id,
                        'student_id' => $studentId,
                        'status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => $adminId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        return redirect()
            ->route('dashboard.batches.students.edit', $batch)
            ->with('success', 'Batch students updated successfully.');
    }
}
