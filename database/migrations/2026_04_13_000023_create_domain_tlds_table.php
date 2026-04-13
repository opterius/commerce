<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_tlds', function (Blueprint $table) {
            $table->id();
            $table->string('tld', 30)->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->unsignedBigInteger('register_price')->default(0)->comment('Cents per year');
            $table->unsignedBigInteger('renew_price')->default(0)->comment('Cents per year');
            $table->unsignedBigInteger('transfer_price')->default(0)->comment('Cents one-time');

            $table->unsignedTinyInteger('min_years')->default(1);
            $table->unsignedTinyInteger('max_years')->default(10);

            $table->boolean('epp_required')->default(false);
            $table->boolean('whois_privacy_available')->default(false);

            $table->unsignedSmallInteger('grace_period_days')->default(0);
            $table->unsignedSmallInteger('redemption_period_days')->default(0);

            $table->char('currency_code', 3)->default('USD');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_tlds');
    }
};
