<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel')->default('b2c');          // b2c|b2b
            $table->string('status')->default('pending');       // pending|awaiting_payment|paid|processing|shipped|completed|cancelled|refunded
            $table->string('payment_status')->default('unpaid'); // unpaid|pending|paid|failed|refunded
            $table->string('payment_method')->nullable();        // mpesa|pesapal|...

            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone');
            $table->string('customer_company')->nullable();

            $table->json('shipping_address')->nullable();
            $table->string('delivery_zone')->nullable();

            $table->unsignedBigInteger('subtotal')->default(0);      // net of VAT
            $table->unsignedBigInteger('discount_total')->default(0);
            $table->unsignedBigInteger('vat_total')->default(0);
            $table->unsignedBigInteger('shipping_total')->default(0);
            $table->unsignedBigInteger('grand_total')->default(0);
            $table->string('currency', 3)->default('KES');

            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('payment_status');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->nullable();
            $table->string('name');
            $table->unsignedBigInteger('unit_price');          // net of VAT
            $table->unsignedBigInteger('cost_price_snapshot')->default(0);
            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('line_total');          // net of VAT
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
