<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('type', 20); // percent, fixed
            $table->unsignedBigInteger('value'); // percent (e.g. 2000 = 20.00%) or fixed amount in cents
            $table->boolean('recurring')->default(false); // applies every cycle or first only
            $table->string('applies_to', 20)->default('all'); // all, specific
            $table->unsignedInteger('max_uses')->nullable(); // null = unlimited
            $table->unsignedInteger('uses')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Pivot: promo code -> specific products
        Schema::create('promo_code_product', function (Blueprint $table) {
            $table->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['promo_code_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_code_product');
        Schema::dropIfExists('promo_codes');
    }
};
