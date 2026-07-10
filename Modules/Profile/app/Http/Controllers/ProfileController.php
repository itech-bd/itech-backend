<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Profile\Http\Requests\AddressUpdateRequest;
use Modules\Profile\Http\Requests\EducationRequest;
use Modules\Profile\Http\Requests\ExperienceRequest;
use Modules\Profile\Http\Requests\PublicUrlUpdateRequest;
use Modules\Profile\Http\Requests\ProfileDetailsRequest;
use Modules\Profile\Http\Requests\ProfileUpdateRequest;
use Modules\Profile\Http\Requests\SkillStoreRequest;
use Modules\Profile\Http\Requests\SkillUpdateRequest;
use Modules\Profile\Models\Address;
use Modules\Profile\Models\Education;
use Modules\Profile\Models\Experience;
use Modules\Profile\Models\Skill;
use Modules\Profile\Models\UserProfile;

/**
 * Handle authenticated user's profile actions.
 */
class ProfileController extends Controller
{
    /**
     * Show the profile edit screen.
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load([
            'profile',
            'address',
            'educations',
            'experiences',
            'skills',
        ]);

        return view('profile.edit', ['user' => $user]);
    }

    /**
     * Update extended profile details (user_profiles).
     */
    public function updateDetails(ProfileDetailsRequest $request): RedirectResponse
    {
        $user = $request->user();

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            $request->validated()
        );

        return Redirect::route('profile.edit')->with('status', 'profile-details-updated');
    }

    /**
     * Update public profile URL (user_profiles.public_url).
     */
    public function updatePublicUrl(PublicUrlUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['public_url' => $request->validated('public_url')]
        );

        return Redirect::route('profile.edit')->with('status', 'public-url-updated');
    }

    /**
     * Create/update current address (one per user).
     */
    public function updateAddress(AddressUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        Address::updateOrCreate(
            ['user_id' => $user->id],
            $request->validated()
        );

        return Redirect::route('profile.edit')->with('status', 'address-updated');
    }

    public function storeEducation(EducationRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->educations()->create($request->validated());

        return Redirect::route('profile.edit')->with('status', 'education-added');
    }

    public function updateEducation(EducationRequest $request, Education $education): RedirectResponse
    {
        $user = $request->user();
        abort_unless($education->user_id === $user->id, 403);

        $education->update($request->validated());

        return Redirect::route('profile.edit')->with('status', 'education-updated');
    }

    public function destroyEducation(Request $request, Education $education): RedirectResponse
    {
        $user = $request->user();
        abort_unless($education->user_id === $user->id, 403);

        $education->delete();

        return Redirect::route('profile.edit')->with('status', 'education-deleted');
    }

    public function storeExperience(ExperienceRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->experiences()->create($request->validated());

        return Redirect::route('profile.edit')->with('status', 'experience-added');
    }

    public function updateExperience(ExperienceRequest $request, Experience $experience): RedirectResponse
    {
        $user = $request->user();
        abort_unless($experience->user_id === $user->id, 403);

        $experience->update($request->validated());

        return Redirect::route('profile.edit')->with('status', 'experience-updated');
    }

    public function destroyExperience(Request $request, Experience $experience): RedirectResponse
    {
        $user = $request->user();
        abort_unless($experience->user_id === $user->id, 403);

        $experience->delete();

        return Redirect::route('profile.edit')->with('status', 'experience-deleted');
    }

    public function storeSkill(SkillStoreRequest $request): RedirectResponse
    {
        $user = $request->user();
        $skillName = (string) Str::of($request->validated('skill_name'))
            ->trim()
            ->replaceMatches('/\s+/', ' ');

        $skill = Skill::firstOrCreate(['name' => $skillName]);

        $user->skills()->syncWithoutDetaching([
            $skill->id => ['proficiency_level' => $request->validated('proficiency_level')],
        ]);

        return Redirect::route('profile.edit')->with('status', 'skill-added');
    }

    public function updateSkill(SkillUpdateRequest $request, Skill $skill): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->skills()->whereKey($skill->id)->exists(), 403);

        $user->skills()->updateExistingPivot(
            $skill->id,
            ['proficiency_level' => $request->validated('proficiency_level')]
        );

        return Redirect::route('profile.edit')->with('status', 'skill-updated');
    }

    public function destroySkill(Request $request, Skill $skill): RedirectResponse
    {
        $user = $request->user();
        $user->skills()->detach($skill->id);

        return Redirect::route('profile.edit')->with('status', 'skill-deleted');
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->safe()->except(['profile_image', 'remove_profile_image']);
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->boolean('remove_profile_image')) {
            $this->_deleteProfileImage($user);
            $user->profile_image = null;
        }

        if ($request->hasFile('profile_image')) {
            $this->_deleteProfileImage($user);
            $user->profile_image = $request->file('profile_image')
                ->store('profile-images', 'public');
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the stored profile image for a user.
     */
    private function _deleteProfileImage(User $user): void
    {
        $path = $user->profile_image;
        if (!is_string($path) || $path === '') {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    /**
     * Delete the authenticated user.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('mentor')) {
            abort(403);
        }

        $request->validateWithBag(
            'userDeletion',
            ['password' => ['required', 'current_password']]
        );

        Auth::logout();

        $this->_deleteProfileImage($user);
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
