<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('badge_text')->nullable();          // e.g. "HOT DEAL", "-15%"
            $table->string('badge_color')->default('#E8801A');
            $table->string('type')->default('percent');         // percent|fixed
            $table->decimal('value', 10, 2)->default(0);
            $table->string('scope')->default('products');       // all|category|brand|products
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('offer_product', function (Blueprint $table) {
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['offer_id', 'product_id']);
        });

        Schema::create('category_offer', function (Blueprint $table) {
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['offer_id', 'category_id']);
        });

        Schema::create('brand_offer', function (Blueprint $table) {
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->primary(['offer_id', 'brand_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_offer');
        Schema::dropIfExists('category_offer');
        Schema::dropIfExists('offer_product');
        Schema::dropIfExists('offers');
    }
};
