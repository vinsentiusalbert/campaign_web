<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'campaign_mobile',
        'campaign_indihome',
        'campaign_orbit',
        'campaign_soundbox',
        'campaign_nomor_cantik',
        'campaign_waba_interaktif',
        'campaign_kam',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasColumn($tableName, 'vendor')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('vendor')->nullable()->after('user_id');
                });
            }

            DB::table($tableName)
                ->leftJoin('users', 'users.id', '=', $tableName . '.user_id')
                ->whereNull($tableName . '.vendor')
                ->update([$tableName . '.vendor' => DB::raw('users.vendor')]);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasColumn($tableName, 'vendor')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('vendor');
                });
            }
        }
    }
};
