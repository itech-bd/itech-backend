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
        Schema::create(
            'frontend_settings',
            function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->text('value_en')->nullable();
                $table->text('value_bn')->nullable();
                $table->timestamps();

                $table->index('key');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('frontend_settings');
    }
};
