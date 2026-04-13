<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 50)->default('client');
            $table->string('name');
            $table->string('field_type', 20); // text, textarea, dropdown, checkbox, date
            $table->text('description')->nullable();
            $table->json('options')->nullable();
            $table->boolean('required')->default(false);
            $table->boolean('show_on_order')->default(false);
            $table->boolean('show_on_invoice')->default(false);
            $table->boolean('admin_only')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_fields');
    }
};
