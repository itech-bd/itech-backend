<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->switchBatchStudentsForeignKey();
        $this->switchBatchMentorsForeignKey();
        $this->renameCourseOrdersUserIdToStudentId();
    }

    public function down(): void
    {
        $this->dropForeignIfExists('course_orders', 'course_orders_student_id_foreign');
        $this->dropIndexIfExists('course_orders', 'course_orders_student_id_course_id_index');

        if (Schema::hasTable('course_orders') && Schema::hasColumn('course_orders', 'student_id') && ! Schema::hasColumn('course_orders', 'user_id')) {
            DB::statement('ALTER TABLE `course_orders` CHANGE `student_id` `user_id` BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE `course_orders` ADD CONSTRAINT `course_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
            DB::statement('ALTER TABLE `course_orders` ADD INDEX `course_orders_user_id_course_id_index` (`user_id`, `course_id`)');
        }

        $this->dropForeignIfExists('batch_students', 'batch_students_student_id_foreign');
        if (Schema::hasTable('batch_students')) {
            DB::statement('ALTER TABLE `batch_students` ADD CONSTRAINT `batch_students_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
        }
    }

    private function switchBatchStudentsForeignKey(): void
    {
        if (! Schema::hasTable('batch_students') || ! Schema::hasTable('students')) {
            return;
        }

        $this->dropForeignIfExists('batch_students', 'batch_students_student_id_foreign');

        if (! $this->foreignKeyExists('batch_students', 'batch_students_student_id_foreign')) {
            DB::statement('ALTER TABLE `batch_students` ADD CONSTRAINT `batch_students_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE');
        }
    }

    private function switchBatchMentorsForeignKey(): void
    {
        if (! Schema::hasTable('batch_mentors') || ! Schema::hasTable('mentors')) {
            return;
        }

        $this->dropForeignIfExists('batch_mentors', 'batch_mentors_mentor_id_foreign');

        $rows = DB::table('batch_mentors')->orderBy('id')->get();
        $mapped = [];

        foreach ($rows as $row) {
            $mentor = DB::table('mentors')->where('user_id', $row->mentor_id)->first(['id']);
            $mentorId = $mentor?->id;

            if (! $mentorId && DB::table('mentors')->where('id', $row->mentor_id)->exists()) {
                $mentorId = (int) $row->mentor_id;
            }

            if (! $mentorId) {
                throw new RuntimeException("No mentor row found for batch_mentors.mentor_id={$row->mentor_id}");
            }

            $key = ((int) $row->batch_id).':'.((int) $mentorId);
            $mapped[$key] = [
                'batch_id' => (int) $row->batch_id,
                'mentor_id' => (int) $mentorId,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];
        }

        DB::table('batch_mentors')->delete();

        if ($mapped !== []) {
            DB::table('batch_mentors')->insert(array_values($mapped));
        }

        if (! $this->indexExists('batch_mentors', 'batch_mentors_batch_id_mentor_id_unique')) {
            DB::statement('ALTER TABLE `batch_mentors` ADD UNIQUE `batch_mentors_batch_id_mentor_id_unique` (`batch_id`, `mentor_id`)');
        }

        if (! $this->indexExists('batch_mentors', 'batch_mentors_mentor_id_batch_id_index')) {
            DB::statement('ALTER TABLE `batch_mentors` ADD INDEX `batch_mentors_mentor_id_batch_id_index` (`mentor_id`, `batch_id`)');
        }

        if (! $this->foreignKeyExists('batch_mentors', 'batch_mentors_mentor_id_foreign')) {
            DB::statement('ALTER TABLE `batch_mentors` ADD CONSTRAINT `batch_mentors_mentor_id_foreign` FOREIGN KEY (`mentor_id`) REFERENCES `mentors` (`id`) ON DELETE CASCADE');
        }
    }

    private function renameCourseOrdersUserIdToStudentId(): void
    {
        if (! Schema::hasTable('course_orders') || ! Schema::hasTable('students')) {
            return;
        }

        if (Schema::hasColumn('course_orders', 'student_id')) {
            return;
        }

        $this->dropForeignIfExists('course_orders', 'course_orders_user_id_foreign');
        $this->dropIndexIfExists('course_orders', 'course_orders_user_id_course_id_index');

        DB::statement('ALTER TABLE `course_orders` CHANGE `user_id` `student_id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `course_orders` ADD CONSTRAINT `course_orders_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE');
        DB::statement('ALTER TABLE `course_orders` ADD INDEX `course_orders_student_id_course_id_index` (`student_id`, `course_id`)');
    }

    private function dropForeignIfExists(string $table, string $foreign): void
    {
        if ($this->foreignKeyExists($table, $foreign)) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$foreign}`");
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if ($this->indexExists($table, $index)) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
        }
    }

    private function foreignKeyExists(string $table, string $foreign): bool
    {
        return DB::selectOne(
            <<<'SQL'
SELECT CONSTRAINT_NAME
FROM information_schema.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = DATABASE()
  AND TABLE_NAME = ?
  AND CONSTRAINT_NAME = ?
  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
SQL,
            [$table, $foreign],
        ) !== null;
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::selectOne("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]) !== null;
    }
};
