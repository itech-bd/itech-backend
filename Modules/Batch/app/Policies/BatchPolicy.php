<?php

namespace Modules\Batch\Policies;

use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;
use Modules\Batch\Models\Batch;
use Modules\Mentors\Models\Mentor;

class BatchPolicy
{
    use HandlesAuthorization;

    public function before(Authenticatable $user, string $ability): ?bool
    {
        if ($user instanceof User && $user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(Authenticatable $user): bool
    {
        if ($user instanceof Student || $user instanceof Mentor) {
            return true;
        }

        return $user instanceof User && $user->can('readBatch');
    }

    public function view(Authenticatable $user, Batch $batch): bool
    {
        // Batch managers (admin) can view any batch.
        if ($user instanceof User) {
            if ($user->can('addBatch') || $user->can('editBatch') || $user->can('deleteBatch')) {
                return true;
            }

            return $user->can('readBatch');
        }

        if ($user instanceof Mentor) {
            return $batch->mentors()->where('mentors.id', $user->id)->exists();
        }

        if ($user instanceof Student) {
            return $batch->students()
                ->wherePivotIn('status', ['pending', 'approved'])
                ->where('students.id', $user->id)
                ->exists();
        }

        return false;
    }

    public function create(Authenticatable $user): bool
    {
        return $user instanceof User && $user->can('addBatch');
    }

    public function update(Authenticatable $user, Batch $batch): bool
    {
        return $user instanceof User && $user->can('editBatch');
    }

    public function updateLiveClassLink(Authenticatable $user, Batch $batch): bool
    {
        if ($user instanceof User) {
            if ($user->can('editBatch')) {
                return true;
            }

            if (! $user->can('editClassSchedule')) {
                return false;
            }

            return $batch->mentors()->where('mentors.user_id', $user->id)->exists();
        }

        if ($user instanceof Mentor) {
            return $batch->mentors()->where('mentors.id', $user->id)->exists();
        }

        return false;
    }

    public function delete(Authenticatable $user, Batch $batch): bool
    {
        return $user instanceof User && $user->can('deleteBatch');
    }

    public function assignMentors(Authenticatable $user, Batch $batch): bool
    {
        return $user instanceof User && $user->can('assignMentorsToBatch');
    }

    public function assignStudents(Authenticatable $user, Batch $batch): bool
    {
        return $user instanceof User && $user->can('assignStudentsToBatch');
    }
}
