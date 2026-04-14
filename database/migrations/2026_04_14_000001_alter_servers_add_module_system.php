<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            if (! Schema::hasColumn('servers', 'type')) {
                $table->string('type')->default('opterius')->after('server_group_id');
            }
            if (! Schema::hasColumn('servers', 'credentials')) {
                $table->json('credentials')->nullable()->after('ip_address');
            }
            if (! Schema::hasColumn('servers', 'ns3')) {
                $table->string('ns3')->nullable()->after('ns2');
            }
            if (! Schema::hasColumn('servers', 'ns4')) {
                $table->string('ns4')->nullable()->after('ns3');
            }
        });

        // Migrate existing api_url / api_token into credentials JSON
        if (Schema::hasColumn('servers', 'api_url')) {
            foreach (DB::table('servers')->orderBy('id')->cursor() as $server) {
                // Only migrate if credentials is still empty
                $existing = json_decode($server->credentials ?? 'null', true);
                if (empty($existing)) {
                    DB::table('servers')->where('id', $server->id)->update([
                        'credentials' => json_encode([
                            'api_url'   => $server->api_url   ?? '',
                            'api_token' => $server->api_token ?? '',
                        ]),
                    ]);
                }
            }

            Schema::table('servers', function (Blueprint $table) {
                $table->dropColumn(['api_url', 'api_token']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            if (! Schema::hasColumn('servers', 'api_url')) {
                $table->string('api_url')->default('');
            }
            if (! Schema::hasColumn('servers', 'api_token')) {
                $table->string('api_token')->default('');
            }
        });

        foreach (DB::table('servers')->orderBy('id')->cursor() as $server) {
            $creds = json_decode($server->credentials ?? '{}', true) ?? [];
            DB::table('servers')->where('id', $server->id)->update([
                'api_url'   => $creds['api_url']   ?? '',
                'api_token' => $creds['api_token']  ?? '',
            ]);
        }

        Schema::table('servers', function (Blueprint $table) {
            foreach (['type', 'credentials', 'ns3', 'ns4'] as $col) {
                if (Schema::hasColumn('servers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
