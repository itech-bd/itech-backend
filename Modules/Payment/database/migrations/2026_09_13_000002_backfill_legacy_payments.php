<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('course_orders')->where('status', 'paid')->orderBy('id')->chunkById(200, function ($orders) {
            foreach ($orders as $candidate) {
                DB::transaction(function () use ($candidate) {
                    $order = DB::table('course_orders')->where('id', $candidate->id)->lockForUpdate()->first();
                    if ($order->status !== 'paid' || DB::table('payments')->where('course_order_id', $order->id)->exists()) {
                        return;
                    }
                    DB::table('payments')->insert([
                        'course_order_id' => $order->id,
                        'amount' => $order->amount,
                        'currency' => $order->currency,
                        'payment_method' => 'legacy',
                        'status' => 'successful',
                        'success_slot' => 1,
                        'paid_at' => $order->updated_at ?? $order->created_at,
                        'note' => 'Imported from paid invoice; paid_at is an estimated historical timestamp.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });
            }
        });
    }

    public function down(): void
    {
        // Financial history is deliberately retained when rolling back this backfill.
    }
};
