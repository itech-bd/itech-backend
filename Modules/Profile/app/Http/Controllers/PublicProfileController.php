<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

/**
 * Public (guest) view of a user's profile.
 */
class PublicProfileController extends Controller
{
    /**
     * Display a public profile by its public URL slug.
     */
    public function show(string $public_url): View
    {
        $user = User::query()
            ->whereHas(
                'profile',
                fn ($query) => $query->where('public_url', $public_url)
            )
            ->with(
                [
                    'profile',
                    'address',
                    'educations' => fn ($query) => $query
                        ->orderByDesc('end_year')
                        ->orderByDesc('start_year')
                        ->orderByDesc('id'),
                    'experiences' => fn ($query) => $query
                        ->orderByDesc('end_date')
                        ->orderByDesc('start_date')
                        ->orderByDesc('id'),
                    'skills' => fn ($query) => $query->orderBy('name'),
                ]
            )
            ->firstOrFail();

        return view('profile.public', compact('user'));
    }
}
