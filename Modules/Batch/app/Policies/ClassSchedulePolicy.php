<?php

namespace Modules\Batch\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Batch\Models\ClassSchedule;

class ClassSchedulePolicy
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
        return $user->can('readClassSchedule');
    }

    public function view(User $user, ClassSchedule $classSchedule): bool
    {
        return $user->can('readClassSchedule');
    }

    public function create(User $user): bool
    {
        return $user->can('addClassSchedule');
    }

    public function update(User $user, ClassSchedule $classSchedule): bool
    {
        return $user->can('editClassSchedule');
    }

    public function delete(User $user, ClassSchedule $classSchedule): bool
    {
        return $user->can('deleteClassSchedule');
    }
}
