<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('type')->default('b2c')->after('phone');          // b2c|b2b
            $table->string('company_name')->nullable()->after('type');
            $table->string('kra_pin')->nullable()->after('company_name');
            $table->string('b2b_status')->default('none')->after('kra_pin'); // none|pending|approved|rejected
            $table->timestamp('b2b_approved_at')->nullable()->after('b2b_status');
            $table->boolean('tax_exempt')->default(false)->after('b2b_approved_at');
            $table->boolean('is_admin')->default(false)->after('tax_exempt');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'type', 'company_name', 'kra_pin',
                'b2b_status', 'b2b_approved_at', 'tax_exempt', 'is_admin',
            ]);
        });
    }
};
