<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('course_orders')) {
            return;
        }

        Schema::table('course_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('course_orders', 'batch_type')) {
                $table->enum('batch_type', ['online', 'offline'])->nullable()->after('batch_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('course_orders')) {
            return;
        }

        Schema::table('course_orders', function (Blueprint $table) {
            if (Schema::hasColumn('course_orders', 'batch_type')) {
                $table->dropColumn('batch_type');
            }
        });
    }
};
