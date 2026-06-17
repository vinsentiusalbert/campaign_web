<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('campaign_kam_reports') || ! Schema::hasColumn('campaign_kam_reports', 'channel')) {
            return;
        }

        Schema::table('campaign_kam_reports', function (Blueprint $table) {
            $table->dropColumn('channel');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('campaign_kam_reports') || Schema::hasColumn('campaign_kam_reports', 'channel')) {
            return;
        }

        Schema::table('campaign_kam_reports', function (Blueprint $table) {
            $table->enum('channel', ['SMS', 'WABA'])->default('WABA')->after('category');
        });
    }
};
