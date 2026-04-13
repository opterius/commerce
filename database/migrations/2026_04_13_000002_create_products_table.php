<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_group_id')->constrained('product_groups')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('type', 30)->default('hosting'); // hosting, other
            $table->string('status', 20)->default('active'); // active, hidden, retired
            $table->string('provisioning_module')->nullable(); // opterius_panel, null for manual
            $table->boolean('stock_control')->default(false);
            $table->unsignedInteger('qty_in_stock')->nullable();
            $table->boolean('require_domain')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('welcome_email_template')->nullable();
            $table->timestamps();
        });

        Schema::create('product_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->char('currency_code', 3);
            $table->string('billing_cycle', 20); // monthly, quarterly, semi_annual, annual, biennial, one_time
            $table->unsignedBigInteger('price'); // in minor units (cents)
            $table->unsignedBigInteger('setup_fee')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'currency_code', 'billing_cycle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_pricing');
        Schema::dropIfExists('products');
    }
};
