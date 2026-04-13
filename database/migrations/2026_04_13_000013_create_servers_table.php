<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('hostname');
            $table->string('ip_address', 45)->nullable();
            $table->string('api_url');
            $table->string('api_token');
            $table->unsignedInteger('max_accounts')->default(0)->comment('0 = unlimited');
            $table->unsignedInteger('account_count')->default(0);
            $table->string('ns1')->nullable();
            $table->string('ns2')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
