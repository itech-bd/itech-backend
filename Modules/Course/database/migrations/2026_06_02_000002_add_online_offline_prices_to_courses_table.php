<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('online_old_price', 10, 2)->nullable()->after('discount_price');
            $table->decimal('online_discount_price', 10, 2)->nullable()->after('online_old_price');
            $table->decimal('offline_old_price', 10, 2)->nullable()->after('online_discount_price');
            $table->decimal('offline_discount_price', 10, 2)->nullable()->after('offline_old_price');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'online_old_price',
                'online_discount_price',
                'offline_old_price',
                'offline_discount_price',
            ]);
        });
    }
};
