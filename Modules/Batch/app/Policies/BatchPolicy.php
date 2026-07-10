<?php

namespace Modules\Batch\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Batch\Models\Batch;

class BatchPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('readBatch');
    }

    public function view(User $user, Batch $batch): bool
    {
        // Batch managers (admin) can view any batch.
        if ($user->can('addBatch') || $user->can('editBatch') || $user->can('deleteBatch')) {
            return true;
        }

        // Mentors/students can only view batches they belong to.
        $belongsToBatch = $batch->mentors()->where('users.id', $user->id)->exists()
            || $batch->students()
                ->wherePivotIn('status', ['pending', 'approved'])
                ->where('users.id', $user->id)
                ->exists();

        if ($belongsToBatch) {
            return true;
        }

        return $user->can('readBatch');
    }

    public function create(User $user): bool
    {
        return $user->can('addBatch');
    }

    public function update(User $user, Batch $batch): bool
    {
        return $user->can('editBatch');
    }

    public function updateLiveClassLink(User $user, Batch $batch): bool
    {
        if ($user->can('editBatch')) {
            return true;
        }

        if (! $user->can('editClassSchedule')) {
            return false;
        }

        return $batch->mentors()->where('users.id', $user->id)->exists();
    }

    public function delete(User $user, Batch $batch): bool
    {
        return $user->can('deleteBatch');
    }

    public function assignMentors(User $user, Batch $batch): bool
    {
        return $user->can('assignMentorsToBatch');
    }

    public function assignStudents(User $user, Batch $batch): bool
    {
        return $user->can('assignStudentsToBatch');
    }
}
