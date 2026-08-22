<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createStudentsTable();
        $this->addMentorAccountColumns();
        $this->backfillStudents();
        $this->backfillMentors();
    }

    public function down(): void
    {
        Schema::dropIfExists('students');

        if (! Schema::hasTable('mentors')) {
            return;
        }

        Schema::table('mentors', function (Blueprint $table): void {
            if (Schema::hasColumn('mentors', 'email')) {
                $table->dropUnique('mentors_email_unique');
            }

            if (Schema::hasColumn('mentors', 'public_url')) {
                $table->dropUnique('mentors_public_url_unique');
            }
        });

        Schema::table('mentors', function (Blueprint $table): void {
            $columns = array_values(array_filter([
                Schema::hasColumn('mentors', 'email') ? 'email' : null,
                Schema::hasColumn('mentors', 'email_verified_at') ? 'email_verified_at' : null,
                Schema::hasColumn('mentors', 'profile_image') ? 'profile_image' : null,
                Schema::hasColumn('mentors', 'password') ? 'password' : null,
                Schema::hasColumn('mentors', 'must_change_password') ? 'must_change_password' : null,
                Schema::hasColumn('mentors', 'remember_token') ? 'remember_token' : null,
                Schema::hasColumn('mentors', 'gender') ? 'gender' : null,
                Schema::hasColumn('mentors', 'date_of_birth') ? 'date_of_birth' : null,
                Schema::hasColumn('mentors', 'mobile_number') ? 'mobile_number' : null,
                Schema::hasColumn('mentors', 'father_name') ? 'father_name' : null,
                Schema::hasColumn('mentors', 'father_mobile') ? 'father_mobile' : null,
                Schema::hasColumn('mentors', 'mother_name') ? 'mother_name' : null,
                Schema::hasColumn('mentors', 'mother_mobile') ? 'mother_mobile' : null,
                Schema::hasColumn('mentors', 'public_url') ? 'public_url' : null,
                Schema::hasColumn('mentors', 'house_number') ? 'house_number' : null,
                Schema::hasColumn('mentors', 'street') ? 'street' : null,
                Schema::hasColumn('mentors', 'city') ? 'city' : null,
                Schema::hasColumn('mentors', 'post_office') ? 'post_office' : null,
                Schema::hasColumn('mentors', 'zip_code') ? 'zip_code' : null,
                Schema::hasColumn('mentors', 'country') ? 'country' : null,
                Schema::hasColumn('mentors', 'skills') ? 'skills' : null,
                Schema::hasColumn('mentors', 'educations') ? 'educations' : null,
                Schema::hasColumn('mentors', 'experiences') ? 'experiences' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    private function createStudentsTable(): void
    {
        if (Schema::hasTable('students')) {
            return;
        }

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('legacy_user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('password');
            $table->boolean('must_change_password')->default(false);
            $table->rememberToken();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('mobile_number', 25)->nullable();
            $table->string('father_name')->nullable();
            $table->string('father_mobile', 25)->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_mobile', 25)->nullable();
            $table->text('bio')->nullable();
            $table->string('public_url', 50)->nullable()->unique();
            $table->string('house_number')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('post_office')->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('country')->nullable();
            $table->json('skills')->nullable();
            $table->json('educations')->nullable();
            $table->json('experiences')->nullable();
            $table->timestamps();
        });
    }

    private function addMentorAccountColumns(): void
    {
        if (! Schema::hasTable('mentors')) {
            return;
        }

        Schema::table('mentors', function (Blueprint $table): void {
            if (! Schema::hasColumn('mentors', 'email')) {
                $table->string('email')->nullable()->unique();
            }

            if (! Schema::hasColumn('mentors', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable();
            }

            if (! Schema::hasColumn('mentors', 'profile_image')) {
                $table->string('profile_image')->nullable();
            }

            if (! Schema::hasColumn('mentors', 'password')) {
                $table->string('password')->nullable();
            }

            if (! Schema::hasColumn('mentors', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false);
            }

            if (! Schema::hasColumn('mentors', 'remember_token')) {
                $table->rememberToken();
            }

            if (! Schema::hasColumn('mentors', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable();
            }

            if (! Schema::hasColumn('mentors', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable();
            }

            if (! Schema::hasColumn('mentors', 'mobile_number')) {
                $table->string('mobile_number', 25)->nullable();
            }

            if (! Schema::hasColumn('mentors', 'father_name')) {
                $table->string('father_name')->nullable();
            }

            if (! Schema::hasColumn('mentors', 'father_mobile')) {
                $table->string('father_mobile', 25)->nullable();
            }

            if (! Schema::hasColumn('mentors', 'mother_name')) {
                $table->string('mother_name')->nullable();
            }

            if (! Schema::hasColumn('mentors', 'mother_mobile')) {
                $table->string('mother_mobile', 25)->nullable();
            }

            if (! Schema::hasColumn('mentors', 'public_url')) {
                $table->string('public_url', 50)->nullable()->unique();
            }

            if (! Schema::hasColumn('mentors', 'house_number')) {
                $table->string('house_number')->nullable();
            }

            if (! Schema::hasColumn('mentors', 'street')) {
                $table->string('street')->nullable();
            }

            if (! Schema::hasColumn('mentors', 'city')) {
                $table->string('city')->nullable();
            }

            if (! Schema::hasColumn('mentors', 'post_office')) {
                $table->string('post_office')->nullable();
            }

            if (! Schema::hasColumn('mentors', 'zip_code')) {
                $table->string('zip_code', 20)->nullable();
            }

            if (! Schema::hasColumn('mentors', 'country')) {
                $table->string('country')->nullable();
            }

            if (! Schema::hasColumn('mentors', 'skills')) {
                $table->json('skills')->nullable();
            }

            if (! Schema::hasColumn('mentors', 'educations')) {
                $table->json('educations')->nullable();
            }

            if (! Schema::hasColumn('mentors', 'experiences')) {
                $table->json('experiences')->nullable();
            }
        });
    }

    private function backfillStudents(): void
    {
        if (! Schema::hasTable('students') || ! $this->canReadRoleData()) {
            return;
        }

        $users = DB::table('users')
            ->join('model_has_roles', function ($join): void {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', \App\Models\User::class);
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'student')
            ->select('users.*')
            ->distinct()
            ->orderBy('users.id')
            ->get();

        $this->backfillAccountRows('students', $users);
    }

    private function backfillMentors(): void
    {
        if (! Schema::hasTable('mentors')) {
            return;
        }

        $mentors = DB::table('mentors')
            ->leftJoin('users', 'users.id', '=', 'mentors.user_id')
            ->select([
                'mentors.id',
                'mentors.user_id',
                'mentors.name as mentor_name',
                'mentors.updated_at as mentor_updated_at',
                'users.name as user_name',
                'users.email',
                'users.email_verified_at',
                'users.profile_image',
                'users.password',
                'users.must_change_password',
                'users.remember_token',
            ])
            ->orderBy('mentors.id')
            ->get();

        if ($mentors->isEmpty()) {
            return;
        }

        $userIds = $mentors->pluck('user_id')->filter()->values();
        $profiles = $this->profilesByUser($userIds);
        $addresses = $this->addressesByUser($userIds);
        $skills = $this->skillsByUser($userIds);
        $educations = $this->educationsByUser($userIds);
        $experiences = $this->experiencesByUser($userIds);

        foreach ($mentors as $mentor) {
            $profile = $profiles->get($mentor->user_id);
            $address = $addresses->get($mentor->user_id);

            $data = array_merge(
                [
                    'name' => $mentor->mentor_name ?: $mentor->user_name,
                    'email' => $mentor->email,
                    'email_verified_at' => $mentor->email_verified_at,
                    'profile_image' => $mentor->profile_image,
                    'password' => $mentor->password,
                    'must_change_password' => (bool) ($mentor->must_change_password ?? false),
                    'remember_token' => $mentor->remember_token,
                    'updated_at' => $mentor->mentor_updated_at ?? now(),
                ],
                $this->profilePayload($profile),
                $this->addressPayload($address),
                [
                    'skills' => $this->jsonOrNull($skills->get($mentor->user_id, [])),
                    'educations' => $this->jsonOrNull($educations->get($mentor->user_id, [])),
                    'experiences' => $this->jsonOrNull($experiences->get($mentor->user_id, [])),
                ],
            );

            DB::table('mentors')
                ->where('id', $mentor->id)
                ->update($data);
        }
    }

    private function backfillAccountRows(string $table, $users): void
    {
        if ($users->isEmpty()) {
            return;
        }

        $userIds = $users->pluck('id')->values();
        $profiles = $this->profilesByUser($userIds);
        $addresses = $this->addressesByUser($userIds);
        $skills = $this->skillsByUser($userIds);
        $educations = $this->educationsByUser($userIds);
        $experiences = $this->experiencesByUser($userIds);

        foreach ($users as $user) {
            $profile = $profiles->get($user->id);
            $address = $addresses->get($user->id);

            $data = array_merge(
                [
                    'id' => (int) $user->id,
                    'legacy_user_id' => (int) $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'profile_image' => $user->profile_image,
                    'password' => $user->password,
                    'must_change_password' => (bool) ($user->must_change_password ?? false),
                    'remember_token' => $user->remember_token,
                    'created_at' => $user->created_at ?? now(),
                    'updated_at' => $user->updated_at ?? now(),
                ],
                $this->profilePayload($profile),
                $this->addressPayload($address),
                [
                    'skills' => $this->jsonOrNull($skills->get($user->id, [])),
                    'educations' => $this->jsonOrNull($educations->get($user->id, [])),
                    'experiences' => $this->jsonOrNull($experiences->get($user->id, [])),
                ],
            );

            if (DB::table($table)->where('id', $user->id)->exists()) {
                DB::table($table)->where('id', $user->id)->update(collect($data)->except('id')->all());
                continue;
            }

            DB::table($table)->insert($data);
        }
    }

    private function canReadRoleData(): bool
    {
        return Schema::hasTable('users')
            && Schema::hasTable('roles')
            && Schema::hasTable('model_has_roles');
    }

    private function profilesByUser($userIds)
    {
        if (! Schema::hasTable('user_profiles') || $userIds->isEmpty()) {
            return collect();
        }

        return DB::table('user_profiles')
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');
    }

    private function addressesByUser($userIds)
    {
        if (! Schema::hasTable('addresses') || $userIds->isEmpty()) {
            return collect();
        }

        return DB::table('addresses')
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');
    }

    private function skillsByUser($userIds)
    {
        if (! Schema::hasTable('skills') || ! Schema::hasTable('user_skills') || $userIds->isEmpty()) {
            return collect();
        }

        return DB::table('user_skills')
            ->join('skills', 'skills.id', '=', 'user_skills.skill_id')
            ->whereIn('user_skills.user_id', $userIds)
            ->orderBy('skills.name')
            ->get([
                'user_skills.user_id',
                'skills.name',
                'user_skills.proficiency_level',
            ])
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->map(fn ($row) => [
                'name' => $row->name,
                'proficiency_level' => $row->proficiency_level,
            ])->values()->all());
    }

    private function educationsByUser($userIds)
    {
        if (! Schema::hasTable('educations') || $userIds->isEmpty()) {
            return collect();
        }

        return DB::table('educations')
            ->whereIn('user_id', $userIds)
            ->orderByDesc('start_year')
            ->get([
                'user_id',
                'degree_name',
                'institute_name',
                'board_or_university',
                'start_year',
                'end_year',
                'result_or_grade',
            ])
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->map(fn ($row) => [
                'degree_name' => $row->degree_name,
                'institute_name' => $row->institute_name,
                'board_or_university' => $row->board_or_university,
                'start_year' => $row->start_year,
                'end_year' => $row->end_year,
                'result_or_grade' => $row->result_or_grade,
            ])->values()->all());
    }

    private function experiencesByUser($userIds)
    {
        if (! Schema::hasTable('experiences') || $userIds->isEmpty()) {
            return collect();
        }

        return DB::table('experiences')
            ->whereIn('user_id', $userIds)
            ->orderByDesc('start_date')
            ->get([
                'user_id',
                'company_name',
                'job_title',
                'start_date',
                'end_date',
                'description',
            ])
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->map(fn ($row) => [
                'company_name' => $row->company_name,
                'job_title' => $row->job_title,
                'start_date' => $row->start_date,
                'end_date' => $row->end_date,
                'description' => $row->description,
            ])->values()->all());
    }

    private function profilePayload($profile): array
    {
        return [
            'gender' => $profile->gender ?? null,
            'date_of_birth' => $profile->date_of_birth ?? null,
            'mobile_number' => $profile->mobile_number ?? null,
            'father_name' => $profile->father_name ?? null,
            'father_mobile' => $profile->father_mobile ?? null,
            'mother_name' => $profile->mother_name ?? null,
            'mother_mobile' => $profile->mother_mobile ?? null,
            'bio' => $profile->bio ?? null,
            'public_url' => $profile->public_url ?? null,
        ];
    }

    private function addressPayload($address): array
    {
        return [
            'house_number' => $address->house_number ?? null,
            'street' => $address->street ?? null,
            'city' => $address->city ?? null,
            'post_office' => $address->post_office ?? null,
            'zip_code' => $address->zip_code ?? null,
            'country' => $address->country ?? null,
        ];
    }

    private function jsonOrNull($items): ?string
    {
        $values = collect($items)
            ->filter(fn ($item) => collect((array) $item)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty())
            ->values();

        return $values->isEmpty() ? null : json_encode($values->all());
    }
};
