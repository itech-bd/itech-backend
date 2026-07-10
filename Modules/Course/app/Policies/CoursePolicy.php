<?php

namespace Modules\Course\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Course\Models\Course;

class CoursePolicy
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
        return $user->can('readCourse');
    }

    public function view(User $user, Course $course): bool
    {
        return $user->can('readCourse');
    }

    public function create(User $user): bool
    {
        return $user->can('addCourse');
    }

    public function update(User $user, Course $course): bool
    {
        return $user->can('editCourse');
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->can('deleteCourse');
    }
}
