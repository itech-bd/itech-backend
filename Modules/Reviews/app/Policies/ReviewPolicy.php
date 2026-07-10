<?php

namespace Modules\Reviews\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Reviews\Models\Review;

class ReviewPolicy
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
        return $user->can('readReview');
    }

    public function view(User $user, Review $review): bool
    {
        return $user->can('readReview');
    }

    public function create(User $user): bool
    {
        return $user->can('addReview');
    }

    public function update(User $user, Review $review): bool
    {
        return $user->can('editReview');
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->can('deleteReview');
    }
}
