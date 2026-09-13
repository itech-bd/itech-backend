<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            Schema::table('course_orders', fn (Blueprint $table) => $table->renameColumn('student_id', 'user_id'));
            Schema::table('course_orders', fn (Blueprint $table) => $table->foreign('user_id', 'course_orders_user_id_foreign')->references('id')->on('users')->cascadeOnDelete());
            Schema::table('course_orders', fn (Blueprint $table) => $table->index(['user_id', 'course_id'], 'course_orders_user_id_course_id_index'));
        }

        $this->dropForeignIfExists('batch_students', 'batch_students_student_id_foreign');
        if (Schema::hasTable('batch_students')) {
            Schema::table('batch_students', fn (Blueprint $table) => $table->foreign('student_id', 'batch_students_student_id_foreign')->references('id')->on('users')->cascadeOnDelete());
        }
    }

    private function switchBatchStudentsForeignKey(): void
    {
        if (! Schema::hasTable('batch_students') || ! Schema::hasTable('students')) {
            return;
        }

        $this->dropForeignIfExists('batch_students', 'batch_students_student_id_foreign');

        if (! $this->foreignKeyExists('batch_students', 'batch_students_student_id_foreign')) {
            Schema::table('batch_students', fn (Blueprint $table) => $table->foreign('student_id', 'batch_students_student_id_foreign')->references('id')->on('students')->cascadeOnDelete());
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
            Schema::table('batch_mentors', fn (Blueprint $table) => $table->unique(['batch_id', 'mentor_id'], 'batch_mentors_batch_id_mentor_id_unique'));
        }

        if (! $this->indexExists('batch_mentors', 'batch_mentors_mentor_id_batch_id_index')) {
            Schema::table('batch_mentors', fn (Blueprint $table) => $table->index(['mentor_id', 'batch_id'], 'batch_mentors_mentor_id_batch_id_index'));
        }

        if (! $this->foreignKeyExists('batch_mentors', 'batch_mentors_mentor_id_foreign')) {
            Schema::table('batch_mentors', fn (Blueprint $table) => $table->foreign('mentor_id', 'batch_mentors_mentor_id_foreign')->references('id')->on('mentors')->cascadeOnDelete());
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

        Schema::table('course_orders', fn (Blueprint $table) => $table->renameColumn('user_id', 'student_id'));
        Schema::table('course_orders', fn (Blueprint $table) => $table->foreign('student_id', 'course_orders_student_id_foreign')->references('id')->on('students')->cascadeOnDelete());
        Schema::table('course_orders', fn (Blueprint $table) => $table->index(['student_id', 'course_id'], 'course_orders_student_id_course_id_index'));
    }

    private function dropForeignIfExists(string $table, string $foreign): void
    {
        if ($this->foreignKeyExists($table, $foreign)) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropForeign($foreign));
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if ($this->indexExists($table, $index)) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($index));
        }
    }

    private function foreignKeyExists(string $table, string $foreign): bool
    {
        return collect(Schema::getForeignKeys($table))->contains('name', $foreign);
    }

    private function indexExists(string $table, string $index): bool
    {
        return Schema::hasIndex($table, $index);
    }
};
