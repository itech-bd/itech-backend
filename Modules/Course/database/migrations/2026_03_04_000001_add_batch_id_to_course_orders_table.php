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
            if (! Schema::hasColumn('course_orders', 'batch_id')) {
                $table->foreignId('batch_id')
                    ->nullable()
                    ->after('course_id')
                    ->constrained('batches')
                    ->nullOnDelete();

                $table->index(['batch_id']);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('course_orders')) {
            return;
        }

        Schema::table('course_orders', function (Blueprint $table) {
            if (Schema::hasColumn('course_orders', 'batch_id')) {
                $table->dropForeign(['batch_id']);
                $table->dropIndex(['batch_id']);
                $table->dropColumn('batch_id');
            }
        });
    }
};
