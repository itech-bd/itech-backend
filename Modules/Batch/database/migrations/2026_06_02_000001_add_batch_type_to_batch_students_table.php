<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('batch_students')) {
            return;
        }

        Schema::table('batch_students', function (Blueprint $table) {
            if (! Schema::hasColumn('batch_students', 'batch_type')) {
                $table->enum('batch_type', ['online', 'offline'])->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('batch_students')) {
            return;
        }

        Schema::table('batch_students', function (Blueprint $table) {
            if (Schema::hasColumn('batch_students', 'batch_type')) {
                $table->dropColumn('batch_type');
            }
        });
    }
};
