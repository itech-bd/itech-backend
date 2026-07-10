<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Api\V1\ApiController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Modules\Profile\Http\Requests\AddressUpdateRequest;
use Modules\Profile\Http\Requests\EducationRequest;
use Modules\Profile\Http\Requests\ExperienceRequest;
use Modules\Profile\Http\Requests\ProfileDetailsRequest;
use Modules\Profile\Http\Requests\ProfileUpdateRequest;
use Modules\Profile\Http\Requests\PublicUrlUpdateRequest;
use Modules\Profile\Http\Requests\SkillStoreRequest;
use Modules\Profile\Http\Requests\SkillUpdateRequest;
use Modules\Profile\Models\Address;
use Modules\Profile\Models\Education;
use Modules\Profile\Models\Experience;
use Modules\Profile\Models\Skill;
use Modules\Profile\Models\UserProfile;

class StudentProfileController extends ApiController
{
    public function show(Request $request): JsonResponse
    {
        return $this->success($this->profilePayload($this->loadUser($request->user())));
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->safe()->except(['profile_image', 'remove_profile_image']);
        $user->fill($data);
        $emailChanged = $user->isDirty('email');

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        if ($request->boolean('remove_profile_image')) {
            $this->deleteProfileImage($user);
            $user->profile_image = null;
        }

        if ($request->hasFile('profile_image')) {
            $this->deleteProfileImage($user);
            $user->profile_image = $request->file('profile_image')->store('profile-images', 'public');
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return $this->success([
            'profile' => $this->profilePayload($this->loadUser($user)),
            'email_verification_required' => $emailChanged,
        ], 'Profile updated.');
    }

    public function updateDetails(ProfileDetailsRequest $request): JsonResponse
    {
        $profile = UserProfile::query()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->validated()
        );

        return $this->success($profile, 'Profile details updated.');
    }

    public function updatePublicUrl(PublicUrlUpdateRequest $request): JsonResponse
    {
        $profile = UserProfile::query()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['public_url' => $request->validated('public_url')]
        );

        return $this->success([
            'public_url' => $profile->public_url,
            'public_profile_path' => $profile->public_url ? '/p/'.$profile->public_url : null,
        ], 'Public profile URL updated.');
    }

    public function updateAddress(AddressUpdateRequest $request): JsonResponse
    {
        $address = Address::query()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->validated()
        );

        return $this->success($address, 'Address updated.');
    }

    public function storeEducation(EducationRequest $request): JsonResponse
    {
        $education = $request->user()->educations()->create($request->validated());

        return $this->success($education, 'Education added.', 201);
    }

    public function updateEducation(EducationRequest $request, Education $education): JsonResponse
    {
        abort_unless((int) $education->user_id === (int) $request->user()->id, 403);
        $education->update($request->validated());

        return $this->success($education->fresh(), 'Education updated.');
    }

    public function destroyEducation(Request $request, Education $education): JsonResponse
    {
        abort_unless((int) $education->user_id === (int) $request->user()->id, 403);
        $education->delete();

        return $this->success(null, 'Education deleted.');
    }

    public function storeExperience(ExperienceRequest $request): JsonResponse
    {
        $experience = $request->user()->experiences()->create($request->validated());

        return $this->success($experience, 'Experience added.', 201);
    }

    public function updateExperience(ExperienceRequest $request, Experience $experience): JsonResponse
    {
        abort_unless((int) $experience->user_id === (int) $request->user()->id, 403);
        $experience->update($request->validated());

        return $this->success($experience->fresh(), 'Experience updated.');
    }

    public function destroyExperience(Request $request, Experience $experience): JsonResponse
    {
        abort_unless((int) $experience->user_id === (int) $request->user()->id, 403);
        $experience->delete();

        return $this->success(null, 'Experience deleted.');
    }

    public function storeSkill(SkillStoreRequest $request): JsonResponse
    {
        $skillName = (string) Str::of($request->validated('skill_name'))
            ->trim()
            ->replaceMatches('/\s+/', ' ');

        $skill = Skill::query()->firstOrCreate(['name' => $skillName]);
        $request->user()->skills()->syncWithoutDetaching([
            $skill->id => ['proficiency_level' => $request->validated('proficiency_level')],
        ]);

        return $this->success([
            'id' => $skill->id,
            'name' => $skill->name,
            'proficiency_level' => $request->validated('proficiency_level'),
        ], 'Skill added.', 201);
    }

    public function updateSkill(SkillUpdateRequest $request, Skill $skill): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->skills()->whereKey($skill->id)->exists(), 403);

        $user->skills()->updateExistingPivot($skill->id, [
            'proficiency_level' => $request->validated('proficiency_level'),
        ]);

        return $this->success([
            'id' => $skill->id,
            'name' => $skill->name,
            'proficiency_level' => $request->validated('proficiency_level'),
        ], 'Skill updated.');
    }

    public function destroySkill(Request $request, Skill $skill): JsonResponse
    {
        $request->user()->skills()->detach($skill->id);

        return $this->success(null, 'Skill deleted.');
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = $request->user();
        $user->forceFill([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
        ])->save();

        $user->tokens()->delete();

        return $this->success([
            'reauthentication_required' => true,
        ], 'Password updated. Please sign in again.');
    }

    private function loadUser(User $user): User
    {
        return $user->load([
            'profile',
            'address',
            'educations' => fn ($query) => $query->orderByDesc('end_year')->orderByDesc('start_year')->orderByDesc('id'),
            'experiences' => fn ($query) => $query->orderByDesc('end_date')->orderByDesc('start_date')->orderByDesc('id'),
            'skills' => fn ($query) => $query->orderBy('name'),
        ]);
    }

    private function profilePayload(User $user): array
    {
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => ! is_null($user->email_verified_at),
                'profile_image_url' => $user->profile_image_url,
            ],
            'details' => $user->profile,
            'public_profile_path' => $user->profile?->public_url ? '/p/'.$user->profile->public_url : null,
            'address' => $user->address,
            'educations' => $user->educations,
            'experiences' => $user->experiences,
            'skills' => $user->skills->map(fn ($skill) => [
                'id' => $skill->id,
                'name' => $skill->name,
                'proficiency_level' => $skill->pivot?->proficiency_level,
            ])->values(),
        ];
    }

    private function deleteProfileImage(User $user): void
    {
        if (is_string($user->profile_image) && $user->profile_image !== '') {
            Storage::disk('public')->delete($user->profile_image);
        }
    }
}
