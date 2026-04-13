<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provisioning_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('action', 50);
            $table->string('status', 20)->default('pending'); // pending, success, failed
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->text('error')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamps();

            $table->index(['service_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provisioning_log');
    }
};
