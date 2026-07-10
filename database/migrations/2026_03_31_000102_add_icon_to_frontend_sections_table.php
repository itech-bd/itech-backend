<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('frontend_sections', function (Blueprint $table) {
            if (! Schema::hasColumn('frontend_sections', 'icon')) {
                $table->string('icon', 50)->nullable()->after('image_path');
                $table->index('icon');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('frontend_sections', function (Blueprint $table) {
            if (Schema::hasColumn('frontend_sections', 'icon')) {
                $table->dropIndex(['icon']);
                $table->dropColumn('icon');
            }
        });
    }
};
