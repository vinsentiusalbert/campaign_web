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
        Schema::table('campaign_kam', function (Blueprint $table) {
            if (! Schema::hasColumn('campaign_kam', 'report_csv_file')) {
                $table->string('report_csv_file')->nullable()->after('nama_file_whitelist');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_kam', function (Blueprint $table) {
            if (Schema::hasColumn('campaign_kam', 'report_csv_file')) {
                $table->dropColumn('report_csv_file');
            }
        });
    }
};
