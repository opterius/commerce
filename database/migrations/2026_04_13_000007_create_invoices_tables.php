<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->enum('status', ['draft', 'unpaid', 'paid', 'overdue', 'cancelled', 'refunded'])->default('unpaid');
            $table->date('due_date');
            $table->timestamp('paid_date')->nullable();
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('tax')->default(0);
            $table->unsignedBigInteger('total');
            $table->unsignedBigInteger('credit_applied')->default(0);
            $table->char('currency_code', 3);
            $table->text('notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('overdue_notified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->string('description', 500);
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedTinyInteger('quantity')->default(1);
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('services')->nullOnDelete();
        });

        // Now add the deferred FK from orders -> invoices
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
        });
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
