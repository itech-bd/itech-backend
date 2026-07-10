<?php

namespace Modules\Batch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Modules\Batch\Models\Batch;

class BatchStudentApprovalController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:admin|assignStudentsToBatch'),
        ];
    }

    public function approve(Batch $batch, User $student): RedirectResponse
    {
        abort_unless(Gate::allows('assignStudents', $batch), 403);

        $adminId = (int) Auth::id();
        abort_unless($adminId > 0, 403);

        DB::table('batch_students')
            ->where('batch_id', $batch->id)
            ->where('student_id', $student->id)
            ->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $adminId,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('dashboard.batches.students.edit', $batch)
            ->with('success', 'Student approved successfully.');
    }

    public function reject(Batch $batch, User $student): RedirectResponse
    {
        abort_unless(Gate::allows('assignStudents', $batch), 403);

        DB::table('batch_students')
            ->where('batch_id', $batch->id)
            ->where('student_id', $student->id)
            ->where('status', 'pending')
            ->delete();

        return redirect()
            ->route('dashboard.batches.students.edit', $batch)
            ->with('success', 'Student rejected successfully.');
    }
}
