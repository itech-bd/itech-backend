<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mentors', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        $existingSlugs = [];

        DB::table('mentors')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->get()
            ->each(function ($mentor) use (&$existingSlugs): void {
                $baseSlug = Str::slug((string) $mentor->name);

                if ($baseSlug === '') {
                    $baseSlug = 'mentor';
                }

                $slug = $baseSlug;
                $suffix = 2;

                while (in_array($slug, $existingSlugs, true) || DB::table('mentors')->where('slug', $slug)->where('id', '!=', $mentor->id)->exists()) {
                    $slug = $baseSlug.'-'.$suffix;
                    $suffix++;
                }

                DB::table('mentors')->where('id', $mentor->id)->update(['slug' => $slug]);
                $existingSlugs[] = $slug;
            });

        Schema::table('mentors', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('mentors', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};