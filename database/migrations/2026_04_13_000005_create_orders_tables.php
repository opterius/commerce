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
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->unsignedBigInteger('promo_code_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable(); // FK added after invoices table
            $table->enum('status', ['pending', 'active', 'fraud', 'cancelled'])->default('pending');
            $table->char('currency_code', 3);
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('total');
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('promo_code_id')->references('id')->on('promo_codes')->nullOnDelete();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('billing_cycle', 20);
            $table->unsignedTinyInteger('qty')->default(1);
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('setup_fee')->default(0);
            $table->string('domain')->nullable();
            $table->json('config_options')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
