<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('degree_name');
            $table->string('institute_name');
            $table->string('board_or_university')->nullable();
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->string('result_or_grade')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'start_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('educations');
    }
};
