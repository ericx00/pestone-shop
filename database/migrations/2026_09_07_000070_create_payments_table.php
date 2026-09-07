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
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('gateway');                       // mpesa|pesapal
            $table->string('method')->nullable();            // stk|card|...
            $table->string('status')->default('initiated');  // initiated|pending|success|failed|cancelled
            $table->unsignedBigInteger('amount');
            $table->string('phone')->nullable();

            $table->string('gateway_ref')->nullable()->index();        // receipt / tracking id
            $table->string('merchant_request_id')->nullable();
            $table->string('checkout_request_id')->nullable()->unique();

            $table->json('raw_request')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
