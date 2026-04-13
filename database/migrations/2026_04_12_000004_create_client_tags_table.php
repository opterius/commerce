<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('color', 7)->nullable();
            $table->timestamps();
        });

        Schema::create('client_tag', function (Blueprint $table) {
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('client_tags')->cascadeOnDelete();
            $table->primary(['client_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_tag');
        Schema::dropIfExists('client_tags');
    }
};
