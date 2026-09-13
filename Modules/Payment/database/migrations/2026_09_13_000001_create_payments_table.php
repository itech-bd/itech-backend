<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_order_id')->constrained('course_orders')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);
            $table->string('payment_method', 50);
            $table->string('transaction_reference', 191)->nullable()->index();
            $table->string('status', 20)->index();
            // Only successful payments hold this slot; NULL permits multiple historical reversals.
            $table->unsignedTinyInteger('success_slot')->nullable();
            $table->unique(['course_order_id', 'success_slot']);
            $table->timestamp('paid_at')->nullable()->index();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reversal_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
