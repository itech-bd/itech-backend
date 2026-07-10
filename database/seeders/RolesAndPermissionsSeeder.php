<?php

// phpcs:disable

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seed roles and permissions.
 *
 * @category Database
 * @package  Database\Seeders
 * @author   Edu App
 * @license  UNLICENSED
 * @link     https://localhost
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'addMentor',
            'readMentor',
            'editMentor',
            'deleteMentor',
            'addReview',
            'readReview',
            'editReview',
            'deleteReview',
            'addCourse',
            'readCourse',
            'editCourse',
            'deleteCourse',
            'addBatch',
            'readBatch',
            'editBatch',
            'deleteBatch',
            'assignMentorsToBatch',
            'assignStudentsToBatch',
            'addClassSchedule',
            'readClassSchedule',
            'editClassSchedule',
            'deleteClassSchedule',
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $adminRoleAttributes = ['name' => 'admin', 'guard_name' => 'web'];
        $studentRoleAttributes = ['name' => 'student', 'guard_name' => 'web'];
        $mentorRoleAttributes = ['name' => 'mentor', 'guard_name' => 'web'];

        $adminRole = Role::query()->firstOrCreate($adminRoleAttributes);
        $studentRole = Role::query()->firstOrCreate($studentRoleAttributes);

        // teacher -> mentor (rename/merge)
        $roleQuery = Role::query()->where('guard_name', 'web');
        $mentorRole = (clone $roleQuery)->where('name', 'mentor')->first();
        $teacherRole = (clone $roleQuery)->where('name', 'teacher')->first();

        if (! $mentorRole && $teacherRole) {
            $teacherRole->name = 'mentor';
            $teacherRole->save();
            $mentorRole = $teacherRole;
        }

        if (! $mentorRole) {
            $mentorRole = Role::query()->firstOrCreate($mentorRoleAttributes);
        }

        if ($teacherRole && $mentorRole && $teacherRole->id !== $mentorRole->id) {
            // Merge teacher -> mentor
            $mentorRole->givePermissionTo($teacherRole->permissions);

            foreach ($teacherRole->users as $user) {
                $user->assignRole($mentorRole);
                $user->removeRole($teacherRole);
            }

            $teacherRole->delete();
        }

        // Admin has all permissions
        $adminRole->syncPermissions(Permission::all());

        $studentPermissions = [
            'readMentor',
            'readCourse',
            'readBatch',
            'readClassSchedule',
        ];

        $mentorPermissions = [
            'readMentor',
            'editMentor',
            'readCourse',
            'readBatch',
            'readClassSchedule',
            'addClassSchedule',
            'editClassSchedule',
        ];

        $studentRole->syncPermissions($studentPermissions);
        $mentorRole->syncPermissions($mentorPermissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

// phpcs:enable
