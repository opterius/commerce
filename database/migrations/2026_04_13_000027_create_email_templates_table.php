<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('mailable', 60);     // e.g. invoice.generated
            $table->string('locale', 10)->default('en');
            $table->string('subject');
            $table->text('body');               // HTML with {variable} placeholders
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['mailable', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
