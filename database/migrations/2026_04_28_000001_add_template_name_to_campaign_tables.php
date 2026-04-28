<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'campaign_indihome',
            'campaign_mobile',
            'campaign_nomor_cantik',
            'campaign_orbit',
            'campaign_soundbox',
            'campaign_waba_interaktif',
        ];

        foreach ($tables as $tableName) {
            if (! Schema::hasColumn($tableName, 'template_name')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('template_name', 255)->nullable()->after('user_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'campaign_indihome',
            'campaign_mobile',
            'campaign_nomor_cantik',
            'campaign_orbit',
            'campaign_soundbox',
            'campaign_waba_interaktif',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasColumn($tableName, 'template_name')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('template_name');
                });
            }
        }
    }
};
