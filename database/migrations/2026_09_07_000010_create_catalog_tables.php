<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('source_sheet')->nullable();
            $table->string('image_path')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->unsignedBigInteger('cost_price')->default(0);   // KES, supplier cost
            $table->unsignedBigInteger('price')->default(0);        // KES, retail (B2C, VAT-inclusive)
            $table->unsignedBigInteger('b2b_price')->nullable();    // KES, ex-VAT for approved B2B
            $table->boolean('price_locked')->default(false);        // true once an admin edits price
            $table->integer('stock_qty')->default(0);
            $table->string('stock_status')->default('on_request');  // in_stock|low|out|on_request
            $table->string('availability_label')->nullable();
            $table->unsignedInteger('weight_grams')->nullable();
            $table->json('images')->nullable();
            $table->json('specs')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('source_sheet')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'category_id']);
            $table->index(['is_active', 'brand_id']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('brands');
    }
};
