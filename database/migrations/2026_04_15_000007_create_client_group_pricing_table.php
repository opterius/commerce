<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_group_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_group_id')->constrained('client_groups')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('currency_code', 3);
            $table->string('billing_cycle', 30);
            $table->unsignedInteger('price')->default(0);      // cents
            $table->unsignedInteger('setup_fee')->default(0);  // cents
            $table->timestamps();

            $table->unique(['client_group_id', 'product_id', 'currency_code', 'billing_cycle'], 'cgp_unique');
            $table->index(['client_group_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_group_pricing');
    }
};
