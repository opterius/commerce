<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configurable_option_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Pivot: which products use which option groups
        Schema::create('product_configurable_group', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('configurable_option_group_id')->constrained('configurable_option_groups')->cascadeOnDelete();
            $table->primary(['product_id', 'configurable_option_group_id'], 'product_config_group_pk');
        });

        Schema::create('configurable_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('configurable_option_groups')->cascadeOnDelete();
            $table->string('name');
            $table->string('option_type', 20)->default('dropdown'); // dropdown, radio, checkbox, quantity
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('configurable_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_id')->constrained('configurable_options')->cascadeOnDelete();
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Pricing per option value per cycle per currency
        Schema::create('configurable_option_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_value_id')->constrained('configurable_option_values')->cascadeOnDelete();
            $table->char('currency_code', 3);
            $table->string('billing_cycle', 20);
            $table->bigInteger('price'); // in minor units, can be negative for discounts
            $table->timestamps();

            $table->unique(['option_value_id', 'currency_code', 'billing_cycle'], 'option_pricing_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configurable_option_pricing');
        Schema::dropIfExists('configurable_option_values');
        Schema::dropIfExists('configurable_options');
        Schema::dropIfExists('product_configurable_group');
        Schema::dropIfExists('configurable_option_groups');
    }
};
