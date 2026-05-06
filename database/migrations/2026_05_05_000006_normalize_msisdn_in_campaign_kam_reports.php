<?php

use App\Models\CampaignKamReport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('campaign_kam_reports')) {
            return;
        }

        DB::table('campaign_kam_reports')
            ->select('id', 'msisdn')
            ->orderBy('id')
            ->chunk(500, function ($rows) {
                foreach ($rows as $row) {
                    $normalized = CampaignKamReport::normalizeMsisdnValue($row->msisdn);

                    if ($normalized !== $row->msisdn) {
                        DB::table('campaign_kam_reports')
                            ->where('id', $row->id)
                            ->update(['msisdn' => $normalized]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
