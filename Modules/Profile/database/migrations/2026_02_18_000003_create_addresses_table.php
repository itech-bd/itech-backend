<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('house_number')->nullable();
            $table->string('street')->nullable();
            $table->string('city');
            $table->string('post_office')->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('country')->default('Bangladesh');
            $table->timestamps();

            // One current address per user
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
