<?php

namespace Modules\Mentors\Policies;

use App\Models\User;
use Modules\Mentors\Models\Mentor;

class MentorPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('readMentor');
    }

    public function view(User $user, Mentor $mentor): bool
    {
        return $user->can('readMentor');
    }

    public function create(User $user): bool
    {
        return $user->can('addMentor');
    }

    public function update(User $user, Mentor $mentor): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Mentors can update their own mentor profile (when linked to a user)
        return $user->can('editMentor') && $mentor->user_id && $mentor->user_id === $user->id;
    }

    public function delete(User $user, Mentor $mentor): bool
    {
        return $user->can('deleteMentor');
    }
}
