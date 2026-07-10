<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Changes `description` from TEXT (≤65 535 bytes) to MEDIUMTEXT (≤16 MB)
     * so that course descriptions up to 10 000 words can be stored safely.
     * Existing data is preserved; this is a non-destructive column-type change.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->mediumText('description')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->text('description')->change();
        });
    }
};
