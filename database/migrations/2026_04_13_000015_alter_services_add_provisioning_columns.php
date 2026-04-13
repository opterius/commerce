<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('server_id')->nullable()->after('order_item_id')->constrained()->nullOnDelete();
            $table->string('panel_account_id')->nullable()->after('server_id');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['server_id']);
            $table->dropColumn(['server_id', 'panel_account_id']);
        });
    }
};
