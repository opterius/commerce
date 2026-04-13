<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->cascadeOnDelete();
            $table->string('type', 20);
            // registrant | admin | tech | billing

            $table->string('registrar_contact_id')->nullable();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('company')->nullable();
            $table->string('email');
            $table->string('phone', 30);

            $table->string('address_1');
            $table->string('address_2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postcode', 20);
            $table->char('country_code', 2);

            $table->timestamps();

            $table->index(['domain_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_contacts');
    }
};
