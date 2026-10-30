<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('campaign_kam_reports', 'campaign_kam_reports_campaign_id_index')) {
            Schema::table('campaign_kam_reports', function (Blueprint $table) {
                $table->index('campaign_id', 'campaign_kam_reports_campaign_id_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('campaign_kam_reports', function (Blueprint $table) {
            $table->dropIndex('campaign_kam_reports_campaign_id_index');
        });
    }
};
