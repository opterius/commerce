<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();

            $table->string('domain_name');
            $table->string('tld', 30);

            $table->string('status', 20)->default('pending');
            // pending | active | expired | transferred_away | cancelled | fraud | redemption

            $table->string('registrar_module', 50)->default('resellerclub');
            $table->string('registrar_order_id')->nullable();

            $table->date('registration_date')->nullable();
            $table->date('expiry_date')->nullable();

            $table->boolean('auto_renew')->default(true);
            $table->boolean('whois_privacy')->default(false);
            $table->boolean('is_locked')->default(true);

            $table->string('epp_code')->nullable();

            $table->string('ns1')->nullable();
            $table->string('ns2')->nullable();
            $table->string('ns3')->nullable();
            $table->string('ns4')->nullable();

            $table->string('billing_cycle', 10)->default('1year');
            // 1year | 2year | 3year | 5year | 10year

            $table->unsignedBigInteger('amount')->default(0)->comment('Cents');
            $table->char('currency_code', 3)->default('USD');

            $table->date('next_due_date')->nullable();
            $table->date('last_due_date')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['status', 'next_due_date']);
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
