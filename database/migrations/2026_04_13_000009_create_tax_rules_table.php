<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->char('country_code', 2);
            $table->string('state_code', 10)->nullable();
            $table->decimal('rate', 5, 2); // e.g. 20.00 for 20%
            $table->enum('applies_to', ['all', 'hosting', 'one_time'])->default('all');
            $table->boolean('is_eu_tax')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rules');
    }
};
