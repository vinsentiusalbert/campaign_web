<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'campaign_mobile',
            'campaign_indihome',
            'campaign_orbit',
            'campaign_soundbox',
            'campaign_nomor_cantik',
            'campaign_waba_interaktif',
            'campaign_kam',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->text('report_link')->nullable()->after('vendor');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'campaign_mobile',
            'campaign_indihome',
            'campaign_orbit',
            'campaign_soundbox',
            'campaign_nomor_cantik',
            'campaign_waba_interaktif',
            'campaign_kam',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('report_link');
            });
        }
    }
};
