<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('gateway', 50); // stripe, manual
            $table->string('transaction_id', 255)->nullable();
            $table->unsignedBigInteger('amount');
            $table->char('currency_code', 3);
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('method', 50)->nullable(); // card, bank_transfer, cash, check, other
            $table->json('gateway_response')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->unsignedBigInteger('refund_amount')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->char('currency_code', 3);
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('created_by_staff_id')->nullable();
            $table->timestamps();

            $table->foreign('created_by_staff_id')->references('id')->on('staff')->nullOnDelete();
        });

        Schema::create('client_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->bigInteger('amount'); // signed — negative means deduction
            $table->char('currency_code', 3);
            $table->string('description', 500);
            $table->enum('type', ['credit', 'debit']);
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_credits');
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('payments');
    }
};
