<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('server_group_id')->nullable()->after('provisioning_module')->constrained()->nullOnDelete();
            $table->string('provisioning_package')->nullable()->after('server_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['server_group_id']);
            $table->dropColumn(['server_group_id', 'provisioning_package']);
        });
    }
};
