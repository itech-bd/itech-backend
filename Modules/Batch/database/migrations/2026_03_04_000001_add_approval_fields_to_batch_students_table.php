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
            if (! Schema::hasColumn('batch_students', 'status')) {
                $table->enum('status', ['pending', 'approved'])->default('approved')->after('student_id');
                $table->timestamp('approved_at')->nullable()->after('status');
                $table->foreignId('approved_by')->nullable()->after('approved_at')
                    ->constrained('users')
                    ->nullOnDelete();

                $table->index(['batch_id', 'status']);
                $table->index(['student_id', 'status']);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('batch_students')) {
            return;
        }

        Schema::table('batch_students', function (Blueprint $table) {
            if (Schema::hasColumn('batch_students', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }

            if (Schema::hasColumn('batch_students', 'status')) {
                $table->dropIndex(['batch_id', 'status']);
                $table->dropIndex(['student_id', 'status']);

                $table->dropColumn(['status', 'approved_at']);
            }
        });
    }
};
