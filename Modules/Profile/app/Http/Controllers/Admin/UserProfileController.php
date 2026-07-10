<?php

namespace Modules\Profile\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Profile\Http\Requests\AddressUpdateRequest;
use Modules\Profile\Http\Requests\EducationRequest;
use Modules\Profile\Http\Requests\ExperienceRequest;
use Modules\Profile\Http\Requests\ProfileDetailsRequest;
use Modules\Profile\Http\Requests\ProfileUpdateRequest;
use Modules\Profile\Http\Requests\SkillStoreRequest;
use Modules\Profile\Http\Requests\SkillUpdateRequest;
use Modules\Profile\Models\Address;
use Modules\Profile\Models\Education;
use Modules\Profile\Models\Experience;
use Modules\Profile\Models\Skill;
use Modules\Profile\Models\UserProfile;

class UserProfileController extends Controller
{
    public function edit(User $user): View
    {
        $user->load([
            'profile',
            'address',
            'educations',
            'experiences',
            'skills',
        ]);

        return view('admin.users.profile.edit', ['user' => $user]);
    }

    public function update(ProfileUpdateRequest $request, User $user): RedirectResponse
    {
        $data = $request->safe()->except(['profile_image', 'remove_profile_image']);
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->boolean('remove_profile_image')) {
            $this->deleteProfileImage($user);
            $user->profile_image = null;
        }

        if ($request->hasFile('profile_image')) {
            $this->deleteProfileImage($user);
            $user->profile_image = $request->file('profile_image')
                ->store('profile-images', 'public');
        }

        $user->save();

        return Redirect::route('admin.users.profile.edit', ['user' => $user])
            ->with('status', 'profile-updated');
    }

    public function updateDetails(ProfileDetailsRequest $request, User $user): RedirectResponse
    {
        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            $request->validated()
        );

        return Redirect::route('admin.users.profile.edit', ['user' => $user])
            ->with('status', 'profile-details-updated');
    }

    public function updateAddress(AddressUpdateRequest $request, User $user): RedirectResponse
    {
        Address::updateOrCreate(
            ['user_id' => $user->id],
            $request->validated()
        );

        return Redirect::route('admin.users.profile.edit', ['user' => $user])
            ->with('status', 'address-updated');
    }

    public function storeEducation(EducationRequest $request, User $user): RedirectResponse
    {
        $user->educations()->create($request->validated());

        return Redirect::route('admin.users.profile.edit', ['user' => $user])
            ->with('status', 'education-added');
    }

    public function updateEducation(EducationRequest $request, User $user, Education $education): RedirectResponse
    {
        abort_unless($education->user_id === $user->id, 404);

        $education->update($request->validated());

        return Redirect::route('admin.users.profile.edit', ['user' => $user])
            ->with('status', 'education-updated');
    }

    public function destroyEducation(Request $request, User $user, Education $education): RedirectResponse
    {
        abort_unless($education->user_id === $user->id, 404);

        $education->delete();

        return Redirect::route('admin.users.profile.edit', ['user' => $user])
            ->with('status', 'education-deleted');
    }

    public function storeExperience(ExperienceRequest $request, User $user): RedirectResponse
    {
        $user->experiences()->create($request->validated());

        return Redirect::route('admin.users.profile.edit', ['user' => $user])
            ->with('status', 'experience-added');
    }

    public function updateExperience(ExperienceRequest $request, User $user, Experience $experience): RedirectResponse
    {
        abort_unless($experience->user_id === $user->id, 404);

        $experience->update($request->validated());

        return Redirect::route('admin.users.profile.edit', ['user' => $user])
            ->with('status', 'experience-updated');
    }

    public function destroyExperience(Request $request, User $user, Experience $experience): RedirectResponse
    {
        abort_unless($experience->user_id === $user->id, 404);

        $experience->delete();

        return Redirect::route('admin.users.profile.edit', ['user' => $user])
            ->with('status', 'experience-deleted');
    }

    public function storeSkill(SkillStoreRequest $request, User $user): RedirectResponse
    {
        $skillName = (string) Str::of($request->validated('skill_name'))
            ->trim()
            ->replaceMatches('/\s+/', ' ');

        $skill = Skill::firstOrCreate(['name' => $skillName]);

        $user->skills()->syncWithoutDetaching([
            $skill->id => ['proficiency_level' => $request->validated('proficiency_level')],
        ]);

        return Redirect::route('admin.users.profile.edit', ['user' => $user])
            ->with('status', 'skill-added');
    }

    public function updateSkill(SkillUpdateRequest $request, User $user, Skill $skill): RedirectResponse
    {
        abort_unless($user->skills()->whereKey($skill->id)->exists(), 404);

        $user->skills()->updateExistingPivot(
            $skill->id,
            ['proficiency_level' => $request->validated('proficiency_level')]
        );

        return Redirect::route('admin.users.profile.edit', ['user' => $user])
            ->with('status', 'skill-updated');
    }

    public function destroySkill(Request $request, User $user, Skill $skill): RedirectResponse
    {
        $user->skills()->detach($skill->id);

        return Redirect::route('admin.users.profile.edit', ['user' => $user])
            ->with('status', 'skill-deleted');
    }

    private function deleteProfileImage(User $user): void
    {
        $path = $user->profile_image;
        if (!is_string($path) || $path === '') {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
