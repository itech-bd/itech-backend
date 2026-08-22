<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Api\V1\ApiController;
use App\Models\Student;
use App\Support\Accounts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Modules\Profile\Http\Requests\AddressUpdateRequest;
use Modules\Profile\Http\Requests\EducationRequest;
use Modules\Profile\Http\Requests\ExperienceRequest;
use Modules\Profile\Http\Requests\ProfileDetailsRequest;
use Modules\Profile\Http\Requests\SkillStoreRequest;
use Modules\Profile\Http\Requests\SkillUpdateRequest;

class StudentProfileController extends ApiController
{
    public function show(Request $request): JsonResponse
    {
        return $this->success($this->profilePayload($this->student($request)));
    }

    public function update(Request $request): JsonResponse
    {
        $user = $this->student($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', ...Accounts::emailUniqueRules($user)],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_profile_image' => ['nullable', 'boolean'],
        ]);

        $user->fill(collect($data)->only(['name', 'email'])->all());
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
            'profile' => $this->profilePayload($user->fresh()),
            'email_verification_required' => $emailChanged,
        ], 'Profile updated.');
    }

    public function updateDetails(ProfileDetailsRequest $request): JsonResponse
    {
        $user = $this->student($request);
        $user->fill($request->validated())->save();

        return $this->success($this->detailsPayload($user->fresh()), 'Profile details updated.');
    }

    public function updatePublicUrl(Request $request): JsonResponse
    {
        $user = $this->student($request);

        $data = $request->validate([
            'public_url' => [
                'nullable',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('students', 'public_url')->ignore($user->id),
                Rule::unique('mentors', 'public_url'),
                Rule::unique('user_profiles', 'public_url')->ignore($user->legacy_user_id, 'user_id'),
            ],
        ]);

        $publicUrl = is_string($data['public_url'] ?? null) ? Str::slug($data['public_url']) : null;
        $user->forceFill(['public_url' => $publicUrl ?: null])->save();

        return $this->success([
            'public_url' => $user->public_url,
            'public_profile_path' => $user->public_url ? '/p/'.$user->public_url : null,
        ], 'Public profile URL updated.');
    }

    public function updateAddress(AddressUpdateRequest $request): JsonResponse
    {
        $user = $this->student($request);
        $user->fill($request->validated())->save();

        return $this->success($this->addressPayload($user->fresh()), 'Address updated.');
    }

    public function storeEducation(EducationRequest $request): JsonResponse
    {
        $user = $this->student($request);
        $items = $this->items($user, 'educations');
        $education = array_merge(['id' => $this->nextItemId($items)], $request->validated());
        $items->push($education);
        $this->saveItems($user, 'educations', $items);

        return $this->success($education, 'Education added.', 201);
    }

    public function updateEducation(EducationRequest $request, string|int $education): JsonResponse
    {
        return $this->updateJsonItem($request, 'educations', (int) $education, $request->validated(), 'Education updated.');
    }

    public function destroyEducation(Request $request, string|int $education): JsonResponse
    {
        return $this->deleteJsonItem($request, 'educations', (int) $education, 'Education deleted.');
    }

    public function storeExperience(ExperienceRequest $request): JsonResponse
    {
        $user = $this->student($request);
        $items = $this->items($user, 'experiences');
        $experience = array_merge(['id' => $this->nextItemId($items)], $request->validated());
        $items->push($experience);
        $this->saveItems($user, 'experiences', $items);

        return $this->success($experience, 'Experience added.', 201);
    }

    public function updateExperience(ExperienceRequest $request, string|int $experience): JsonResponse
    {
        return $this->updateJsonItem($request, 'experiences', (int) $experience, $request->validated(), 'Experience updated.');
    }

    public function destroyExperience(Request $request, string|int $experience): JsonResponse
    {
        return $this->deleteJsonItem($request, 'experiences', (int) $experience, 'Experience deleted.');
    }

    public function storeSkill(SkillStoreRequest $request): JsonResponse
    {
        $user = $this->student($request);
        $items = $this->items($user, 'skills');
        $skill = [
            'id' => $this->nextItemId($items),
            'name' => (string) Str::of($request->validated('skill_name'))->trim()->replaceMatches('/\s+/', ' '),
            'proficiency_level' => $request->validated('proficiency_level'),
        ];
        $items->push($skill);
        $this->saveItems($user, 'skills', $items);

        return $this->success($skill, 'Skill added.', 201);
    }

    public function updateSkill(SkillUpdateRequest $request, string|int $skill): JsonResponse
    {
        return $this->updateJsonItem(
            $request,
            'skills',
            (int) $skill,
            ['proficiency_level' => $request->validated('proficiency_level')],
            'Skill updated.'
        );
    }

    public function destroySkill(Request $request, string|int $skill): JsonResponse
    {
        return $this->deleteJsonItem($request, 'skills', (int) $skill, 'Skill deleted.');
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = $this->student($request);
        abort_unless(Hash::check((string) $data['current_password'], (string) $user->password), 422);

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
        ])->save();

        $user->tokens()->delete();

        return $this->success([
            'reauthentication_required' => true,
        ], 'Password updated. Please sign in again.');
    }

    private function updateJsonItem(Request $request, string $field, int $id, array $data, string $message): JsonResponse
    {
        $user = $this->student($request);
        $items = $this->items($user, $field);
        $updatedItem = null;

        $items = $items->map(function (array $item) use ($id, $data, &$updatedItem): array {
            if ((int) ($item['id'] ?? 0) !== $id) {
                return $item;
            }

            $updatedItem = array_merge($item, $data);

            return $updatedItem;
        });

        abort_unless($updatedItem !== null, 404);
        $this->saveItems($user, $field, $items);

        return $this->success($updatedItem, $message);
    }

    private function deleteJsonItem(Request $request, string $field, int $id, string $message): JsonResponse
    {
        $user = $this->student($request);
        $items = $this->items($user, $field);
        abort_unless($items->contains(fn (array $item): bool => (int) ($item['id'] ?? 0) === $id), 404);

        $this->saveItems(
            $user,
            $field,
            $items->reject(fn (array $item): bool => (int) ($item['id'] ?? 0) === $id)
        );

        return $this->success(null, $message);
    }

    private function student(Request $request): Student
    {
        $user = $request->user();
        abort_unless($user instanceof Student, 403);

        return $user;
    }

    private function items(Student $user, string $field)
    {
        return collect($user->{$field} ?? [])
            ->values()
            ->map(fn ($item, int $index): array => array_merge(['id' => $index + 1], (array) $item));
    }

    private function nextItemId($items): int
    {
        return ((int) $items->max(fn (array $item): int => (int) ($item['id'] ?? 0))) + 1;
    }

    private function saveItems(Student $user, string $field, $items): void
    {
        $user->forceFill([
            $field => $items->values()->all(),
        ])->save();
    }

    private function profilePayload(Student $user): array
    {
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => ! is_null($user->email_verified_at),
                'profile_image_url' => $user->profile_image_url,
            ],
            'details' => $this->detailsPayload($user),
            'public_profile_path' => $user->public_url ? '/p/'.$user->public_url : null,
            'address' => $this->addressPayload($user),
            'educations' => $this->items($user, 'educations')->sortByDesc('end_year')->values(),
            'experiences' => $this->items($user, 'experiences')->sortByDesc('end_date')->values(),
            'skills' => $this->items($user, 'skills')->sortBy('name')->values(),
        ];
    }

    private function detailsPayload(Student $user): array
    {
        return [
            'gender' => $user->gender,
            'date_of_birth' => $user->date_of_birth?->toDateString(),
            'mobile_number' => $user->mobile_number,
            'father_name' => $user->father_name,
            'father_mobile' => $user->father_mobile,
            'mother_name' => $user->mother_name,
            'mother_mobile' => $user->mother_mobile,
            'bio' => $user->bio,
            'public_url' => $user->public_url,
        ];
    }

    private function addressPayload(Student $user): array
    {
        return [
            'house_number' => $user->house_number,
            'street' => $user->street,
            'city' => $user->city,
            'post_office' => $user->post_office,
            'zip_code' => $user->zip_code,
            'country' => $user->country,
        ];
    }

    private function deleteProfileImage(Student $user): void
    {
        if (is_string($user->profile_image) && $user->profile_image !== '') {
            Storage::disk('public')->delete($user->profile_image);
        }
    }
}
