<?php

namespace Modules\Mentors\Policies;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Modules\Mentors\Models\Mentor;

class MentorPolicy
{
    public function before(Authenticatable $user, string $ability): ?bool
    {
        if ($user instanceof User && $user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(Authenticatable $user): bool
    {
        return $user instanceof User && $user->can('readMentor');
    }

    public function view(Authenticatable $user, Mentor $mentor): bool
    {
        if ($user instanceof Mentor) {
            return $mentor->is($user);
        }

        return $user instanceof User && $user->can('readMentor');
    }

    public function create(Authenticatable $user): bool
    {
        return $user instanceof User && $user->can('addMentor');
    }

    public function update(Authenticatable $user, Mentor $mentor): bool
    {
        if ($user instanceof Mentor) {
            return $mentor->is($user);
        }

        return $user instanceof User && $user->can('editMentor');
    }

    public function delete(Authenticatable $user, Mentor $mentor): bool
    {
        return $user instanceof User && $user->can('deleteMentor');
    }
}
