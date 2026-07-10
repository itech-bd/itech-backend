<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
            $table->unique('slug');
        });

        DB::table('courses')
            ->select(['id', 'title'])
            ->orderBy('id')
            ->get()
            ->each(function (object $course): void {
                DB::table('courses')
                    ->where('id', $course->id)
                    ->update([
                        'slug' => $this->makeUniqueSlug((string) $course->title, (int) $course->id),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropUnique('courses_slug_unique');
            $table->dropColumn('slug');
        });
    }

    private function makeUniqueSlug(string $value, int $courseId): string
    {
        $baseSlug = $this->normalizeSlug($value);
        $slug = $baseSlug;
        $suffix = 2;

        while (DB::table('courses')
            ->where('slug', $slug)
            ->where('id', '!=', $courseId)
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function normalizeSlug(string $value): string
    {
        $normalized = str_replace('&', ' and ', trim($value));
        $slug = Str::slug($normalized);

        return $slug !== '' ? $slug : 'course';
    }
};