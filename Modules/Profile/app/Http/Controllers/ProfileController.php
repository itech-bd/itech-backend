<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Support\Accounts;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Mentors\Models\Mentor;
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
        $user = $request->user();
        abort_unless($user, 403);

        if ($this->isFlatAccount($user)) {
            return view('profile.edit', $this->flatProfileViewData($user));
        }

        $user = $user->load([
            'profile',
            'address',
            'educations',
            'experiences',
            'skills',
        ]);

        return view('profile.edit', [
            'user' => $user,
            'isFlatAccount' => false,
            'profile' => $user->profile,
            'address' => $user->address,
            'educations' => $user->educations,
            'experiences' => $user->experiences,
            'skills' => $user->skills,
        ]);
    }

    /**
     * Update extended profile details.
     */
    public function updateDetails(ProfileDetailsRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($this->isFlatAccount($user)) {
            $user->forceFill($request->validated())->save();

            return Redirect::route('profile.edit')->with('status', 'profile-details-updated');
        }

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            $request->validated()
        );

        return Redirect::route('profile.edit')->with('status', 'profile-details-updated');
    }

    /**
     * Update public profile URL.
     */
    public function updatePublicUrl(PublicUrlUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($this->isFlatAccount($user)) {
            $user->forceFill(['public_url' => $request->validated('public_url')])->save();

            return Redirect::route('profile.edit')->with('status', 'public-url-updated');
        }

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['public_url' => $request->validated('public_url')]
        );

        return Redirect::route('profile.edit')->with('status', 'public-url-updated');
    }

    /**
     * Create/update current address.
     */
    public function updateAddress(AddressUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($this->isFlatAccount($user)) {
            $user->forceFill($request->validated())->save();

            return Redirect::route('profile.edit')->with('status', 'address-updated');
        }

        Address::updateOrCreate(
            ['user_id' => $user->id],
            $request->validated()
        );

        return Redirect::route('profile.edit')->with('status', 'address-updated');
    }

    public function storeEducation(EducationRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($this->isFlatAccount($user)) {
            $this->appendFlatItem($user, 'educations', $request->validated());

            return Redirect::route('profile.edit')->with('status', 'education-added');
        }

        $user->educations()->create($request->validated());

        return Redirect::route('profile.edit')->with('status', 'education-added');
    }

    public function updateEducation(EducationRequest $request, string|int $education): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($this->isFlatAccount($user)) {
            $this->updateFlatItem($user, 'educations', $education, $request->validated());

            return Redirect::route('profile.edit')->with('status', 'education-updated');
        }

        $educationModel = Education::query()->findOrFail($education);
        abort_unless($educationModel->user_id === $user->id, 403);

        $educationModel->update($request->validated());

        return Redirect::route('profile.edit')->with('status', 'education-updated');
    }

    public function destroyEducation(Request $request, string|int $education): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($this->isFlatAccount($user)) {
            $this->removeFlatItem($user, 'educations', $education);

            return Redirect::route('profile.edit')->with('status', 'education-deleted');
        }

        $educationModel = Education::query()->findOrFail($education);
        abort_unless($educationModel->user_id === $user->id, 403);

        $educationModel->delete();

        return Redirect::route('profile.edit')->with('status', 'education-deleted');
    }

    public function storeExperience(ExperienceRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($this->isFlatAccount($user)) {
            $this->appendFlatItem($user, 'experiences', $request->validated());

            return Redirect::route('profile.edit')->with('status', 'experience-added');
        }

        $user->experiences()->create($request->validated());

        return Redirect::route('profile.edit')->with('status', 'experience-added');
    }

    public function updateExperience(ExperienceRequest $request, string|int $experience): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($this->isFlatAccount($user)) {
            $this->updateFlatItem($user, 'experiences', $experience, $request->validated());

            return Redirect::route('profile.edit')->with('status', 'experience-updated');
        }

        $experienceModel = Experience::query()->findOrFail($experience);
        abort_unless($experienceModel->user_id === $user->id, 403);

        $experienceModel->update($request->validated());

        return Redirect::route('profile.edit')->with('status', 'experience-updated');
    }

    public function destroyExperience(Request $request, string|int $experience): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($this->isFlatAccount($user)) {
            $this->removeFlatItem($user, 'experiences', $experience);

            return Redirect::route('profile.edit')->with('status', 'experience-deleted');
        }

        $experienceModel = Experience::query()->findOrFail($experience);
        abort_unless($experienceModel->user_id === $user->id, 403);

        $experienceModel->delete();

        return Redirect::route('profile.edit')->with('status', 'experience-deleted');
    }

    public function storeSkill(SkillStoreRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $skillName = (string) Str::of($request->validated('skill_name'))
            ->trim()
            ->replaceMatches('/\s+/', ' ');

        if ($this->isFlatAccount($user)) {
            $this->upsertFlatSkill(
                $user,
                $skillName,
                (string) $request->validated('proficiency_level')
            );

            return Redirect::route('profile.edit')->with('status', 'skill-added');
        }

        $skill = Skill::firstOrCreate(['name' => $skillName]);

        $user->skills()->syncWithoutDetaching([
            $skill->id => ['proficiency_level' => $request->validated('proficiency_level')],
        ]);

        return Redirect::route('profile.edit')->with('status', 'skill-added');
    }

    public function updateSkill(SkillUpdateRequest $request, string|int $skill): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($this->isFlatAccount($user)) {
            $this->updateFlatItem(
                $user,
                'skills',
                $skill,
                ['proficiency_level' => $request->validated('proficiency_level')]
            );

            return Redirect::route('profile.edit')->with('status', 'skill-updated');
        }

        $skillModel = Skill::query()->findOrFail($skill);
        abort_unless($user->skills()->whereKey($skillModel->id)->exists(), 403);

        $user->skills()->updateExistingPivot(
            $skillModel->id,
            ['proficiency_level' => $request->validated('proficiency_level')]
        );

        return Redirect::route('profile.edit')->with('status', 'skill-updated');
    }

    public function destroySkill(Request $request, string|int $skill): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($this->isFlatAccount($user)) {
            $this->removeFlatItem($user, 'skills', $skill);

            return Redirect::route('profile.edit')->with('status', 'skill-deleted');
        }

        $skillModel = Skill::query()->findOrFail($skill);
        $user->skills()->detach($skillModel->id);

        return Redirect::route('profile.edit')->with('status', 'skill-deleted');
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

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

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the stored profile image for an account.
     */
    private function deleteProfileImage(Authenticatable $user): void
    {
        $path = $user->profile_image ?? null;
        if (! is_string($path) || $path === '') {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    /**
     * Delete the authenticated account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('mentor')) {
            abort(403);
        }

        $request->validateWithBag(
            'userDeletion',
            ['password' => ['required', 'current_password']]
        );

        Accounts::logoutAllGuards();

        $this->deleteProfileImage($user);
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function isFlatAccount(mixed $user): bool
    {
        return $user instanceof Student || $user instanceof Mentor;
    }

    private function flatProfileViewData(Student|Mentor $user): array
    {
        return [
            'user' => $user,
            'isFlatAccount' => true,
            'profile' => $this->flatProfileObject($user),
            'address' => $this->flatAddressObject($user),
            'educations' => collect($this->normalizedFlatItems($user, 'educations'))
                ->map(fn (array $item) => (object) $item),
            'experiences' => collect($this->normalizedFlatItems($user, 'experiences'))
                ->map(fn (array $item) => $this->flatExperienceObject($item)),
            'skills' => collect($this->normalizedFlatItems($user, 'skills'))
                ->map(fn (array $item) => $this->flatSkillObject($item)),
        ];
    }

    private function flatProfileObject(Student|Mentor $user): object
    {
        return (object) [
            'gender' => $user->gender,
            'date_of_birth' => $user->date_of_birth,
            'mobile_number' => $user->mobile_number,
            'father_name' => $user->father_name,
            'father_mobile' => $user->father_mobile,
            'mother_name' => $user->mother_name,
            'mother_mobile' => $user->mother_mobile,
            'bio' => $user->bio,
            'public_url' => $user->public_url,
        ];
    }

    private function flatAddressObject(Student|Mentor $user): object
    {
        return (object) [
            'house_number' => $user->house_number,
            'street' => $user->street,
            'city' => $user->city,
            'post_office' => $user->post_office,
            'zip_code' => $user->zip_code,
            'country' => $user->country,
        ];
    }

    private function flatSkillObject(array $item): object
    {
        $level = $item['proficiency_level'] ?? null;

        return (object) [
            'id' => $item['id'],
            '_key' => $item['id'],
            'name' => $item['name'] ?? '',
            'proficiency_level' => $level,
            'pivot' => (object) ['proficiency_level' => $level],
        ];
    }

    private function flatExperienceObject(array $item): object
    {
        $item['start_date'] = $this->dateOrNull($item['start_date'] ?? null);
        $item['end_date'] = $this->dateOrNull($item['end_date'] ?? null);

        return (object) $item;
    }

    private function dateOrNull(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    private function appendFlatItem(Student|Mentor $user, string $field, array $data): void
    {
        $items = $this->normalizedFlatItems($user, $field);
        $items[] = ['id' => (string) Str::uuid(), ...$data];

        $this->saveFlatItems($user, $field, $items);
    }

    private function updateFlatItem(Student|Mentor $user, string $field, string|int $key, array $data): void
    {
        $items = $this->normalizedFlatItems($user, $field);
        $updated = false;

        foreach ($items as $index => $item) {
            if ((string) $item['id'] !== (string) $key) {
                continue;
            }

            $items[$index] = array_merge($item, $data, ['id' => $item['id']]);
            $updated = true;
            break;
        }

        abort_unless($updated, 404);

        $this->saveFlatItems($user, $field, $items);
    }

    private function removeFlatItem(Student|Mentor $user, string $field, string|int $key): void
    {
        $items = collect($this->normalizedFlatItems($user, $field))
            ->reject(fn (array $item): bool => (string) $item['id'] === (string) $key)
            ->values()
            ->all();

        $this->saveFlatItems($user, $field, $items);
    }

    private function upsertFlatSkill(Student|Mentor $user, string $name, string $level): void
    {
        $items = $this->normalizedFlatItems($user, 'skills');
        $updated = false;

        foreach ($items as $index => $item) {
            if (Str::lower((string) ($item['name'] ?? '')) !== Str::lower($name)) {
                continue;
            }

            $items[$index] = array_merge($item, [
                'name' => $name,
                'proficiency_level' => $level,
            ]);
            $updated = true;
            break;
        }

        if (! $updated) {
            $items[] = [
                'id' => (string) Str::uuid(),
                'name' => $name,
                'proficiency_level' => $level,
            ];
        }

        $this->saveFlatItems($user, 'skills', $items);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizedFlatItems(Student|Mentor $user, string $field): array
    {
        $items = $user->{$field};
        if (! is_array($items)) {
            return [];
        }

        return collect($items)
            ->map(function (mixed $item, int $index): array {
                $row = (array) $item;
                $id = Arr::get($row, 'id', Arr::get($row, '_key', $index));

                unset($row['_key']);

                return ['id' => (string) $id, ...$row];
            })
            ->values()
            ->all();
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function saveFlatItems(Student|Mentor $user, string $field, array $items): void
    {
        $user->forceFill([$field => array_values($items)])->save();
    }
}
