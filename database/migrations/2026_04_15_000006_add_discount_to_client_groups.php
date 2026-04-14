<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_groups', function (Blueprint $table) {
            // Discount in basis points (0-10000). 1000 = 10%, 10000 = 100%.
            $table->unsignedSmallInteger('discount_percent')->default(0)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('client_groups', function (Blueprint $table) {
            $table->dropColumn('discount_percent');
        });
    }
};
