<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_upgrade_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->unsignedBigInteger('from_product_id')->nullable();
            $table->string('from_billing_cycle', 20);
            $table->bigInteger('from_amount');
            $table->unsignedBigInteger('to_product_id')->nullable();
            $table->string('to_billing_cycle', 20);
            $table->bigInteger('to_amount');
            $table->bigInteger('proration_charge')->default(0);
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('from_product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('to_product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
            $table->foreign('processed_by')->references('id')->on('staff')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_upgrade_requests');
    }
};
