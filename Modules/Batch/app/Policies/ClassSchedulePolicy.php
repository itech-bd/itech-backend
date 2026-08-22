<?php

namespace Modules\Batch\Policies;

use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;
use Modules\Batch\Models\ClassSchedule;
use Modules\Mentors\Models\Mentor;

class ClassSchedulePolicy
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

        return $user instanceof User && $user->can('readClassSchedule');
    }

    public function view(Authenticatable $user, ClassSchedule $classSchedule): bool
    {
        if ($user instanceof User) {
            return $user->can('readClassSchedule');
        }

        $batch = $classSchedule->batch;
        if (! $batch) {
            return false;
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
        return $user instanceof User && $user->can('addClassSchedule');
    }

    public function update(Authenticatable $user, ClassSchedule $classSchedule): bool
    {
        return $user instanceof User && $user->can('editClassSchedule');
    }

    public function delete(Authenticatable $user, ClassSchedule $classSchedule): bool
    {
        return $user instanceof User && $user->can('deleteClassSchedule');
    }
}
