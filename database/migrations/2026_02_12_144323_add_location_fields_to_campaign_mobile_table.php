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
        Schema::table('campaign_mobile', function (Blueprint $table) {
            
            $table->string('campaign_type')
                  ->nullable()
                  ->after('nama_campaign');

            $table->string('longitude_latitude')
                  ->nullable()
                  ->after('campaign_type');

            $table->integer('radius')
                  ->nullable()
                  ->after('longitude_latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_mobile', function (Blueprint $table) {
            
            $table->dropColumn([
                'campaign_type',
                'longitude_latitude',
                'radius'
            ]);

        });
    }
};
