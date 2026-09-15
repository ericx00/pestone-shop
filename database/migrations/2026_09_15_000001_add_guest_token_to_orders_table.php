<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('guest_token', 64)->nullable()->after('user_id');
        });

        // Backfill any existing orders so old links/emails keep working.
        \App\Models\Order::whereNull('guest_token')->cursor()->each(
            fn ($order) => $order->forceFill(['guest_token' => Str::random(48)])->save()
        );
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('guest_token');
        });
    }
};
