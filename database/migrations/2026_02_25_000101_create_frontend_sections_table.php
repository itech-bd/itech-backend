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
            'frontend_sections',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('frontend_page_id')
                    ->constrained('frontend_pages')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('section_key', 64);

                $table->string('title_en')->nullable();
                $table->string('title_bn')->nullable();

                $table->longText('content_en')->nullable();
                $table->longText('content_bn')->nullable();

                $table->string('image_path')->nullable();

                $table->string('button_text_en')->nullable();
                $table->string('button_text_bn')->nullable();
                $table->string('button_link')->nullable();

                $table->enum('status', ['active', 'inactive'])->default('active');

                $table->timestamps();

                $table->index(['frontend_page_id', 'status']);
                $table->index(['frontend_page_id', 'section_key']);
                $table->index('section_key');
                $table->index('status');

                $table->unique(['frontend_page_id', 'section_key']);
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
        Schema::dropIfExists('frontend_sections');
    }
};
