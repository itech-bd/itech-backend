<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('batches')) {
            return;
        }

        if (Schema::hasColumn('batches', 'live_class_link')) {
            return;
        }

        Schema::table('batches', function (Blueprint $table) {
            $table->string('live_class_link')->nullable()->after('class_time');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('batches')) {
            return;
        }

        if (! Schema::hasColumn('batches', 'live_class_link')) {
            return;
        }

        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn('live_class_link');
        });
    }
};
