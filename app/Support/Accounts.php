<?php

namespace App\Support;

use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\Mentors\Models\Mentor;

class Accounts
{
    public static function findByEmail(string $email): ?array
    {
        $email = strtolower(trim($email));

        foreach ([
            'student' => Student::class,
            'mentor' => Mentor::class,
            'web' => User::class,
        ] as $guard => $modelClass) {
            $account = $modelClass::query()->where('email', $email)->first();

            if ($account) {
                return ['guard' => $guard, 'account' => $account];
            }
        }

        return null;
    }

    public static function findForVerification(string|int $id, string $hash): ?array
    {
        foreach ([
            'student' => Student::class,
            'mentor' => Mentor::class,
            'web' => User::class,
        ] as $guard => $modelClass) {
            $account = $modelClass::query()->find($id);

            if ($account && hash_equals($hash, sha1($account->getEmailForVerification()))) {
                return ['guard' => $guard, 'account' => $account];
            }
        }

        return null;
    }

    public static function guardFor(Authenticatable $account): string
    {
        return match (true) {
            $account instanceof Student => 'student',
            $account instanceof Mentor => 'mentor',
            default => 'web',
        };
    }

    public static function brokerFor(Authenticatable $account): string
    {
        return match (true) {
            $account instanceof Student => 'students',
            $account instanceof Mentor => 'mentors',
            default => 'users',
        };
    }

    public static function typeFor(Authenticatable $account): string
    {
        return match (true) {
            $account instanceof Student => 'student',
            $account instanceof Mentor => 'mentor',
            default => 'admin',
        };
    }

    public static function emailUniqueRules(?Model $ignore = null): array
    {
        return [
            static::emailRuleFor('students', $ignore),
            static::emailRuleFor('mentors', $ignore),
            static::emailRuleFor('users', $ignore),
        ];
    }

    public static function publicUrlUniqueRules(?Model $ignore = null): array
    {
        return [
            static::publicUrlRuleFor('students', $ignore),
            static::publicUrlRuleFor('mentors', $ignore),
            static::legacyPublicUrlRule($ignore),
        ];
    }

    public static function logoutAllGuards(): void
    {
        foreach (['web', 'student', 'mentor'] as $guard) {
            Auth::guard($guard)->logout();
        }
    }

    private static function emailRuleFor(string $table, ?Model $ignore): mixed
    {
        $rule = Rule::unique($table, 'email');

        if (
            ($table === 'students' && $ignore instanceof Student)
            || ($table === 'mentors' && $ignore instanceof Mentor)
            || ($table === 'users' && $ignore instanceof User)
        ) {
            $rule->ignore($ignore->getKey());
        }

        return $rule;
    }

    private static function publicUrlRuleFor(string $table, ?Model $ignore): mixed
    {
        $rule = Rule::unique($table, 'public_url');

        if (
            ($table === 'students' && $ignore instanceof Student)
            || ($table === 'mentors' && $ignore instanceof Mentor)
        ) {
            $rule->ignore($ignore->getKey());
        }

        return $rule;
    }

    private static function legacyPublicUrlRule(?Model $ignore): mixed
    {
        $rule = Rule::unique('user_profiles', 'public_url');

        if ($ignore instanceof User) {
            $rule->ignore($ignore->getKey(), 'user_id');
        }

        return $rule;
    }
}
