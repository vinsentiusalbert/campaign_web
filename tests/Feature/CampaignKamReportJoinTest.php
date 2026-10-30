<?php

namespace Tests\Feature;

use App\Models\CampaignKam;
use App\Models\CampaignKamReport;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;
use PHPUnit\Framework\TestCase;

class CampaignKamReportJoinTest extends TestCase
{
    public function test_reports_join_on_campaign_id_instead_of_unique_id_or_legacy_id(): void
    {
        $database = new Capsule;
        $database->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $database->setAsGlobal();
        $database->bootEloquent();

        Capsule::schema()->create('campaign_kam', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id_report')->nullable()->unique();
            $table->timestamp('report_csv_uploaded_at')->nullable();
            $table->string('channel')->nullable();
            $table->timestamps();
        });
        Capsule::schema()->create('campaign_kam_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_kam_id');
            $table->string('unique_id');
            $table->string('campaign_id');
            $table->string('status')->nullable();
            $table->timestamps();
        });

        $emptyCampaign = CampaignKam::create([]);
        $campaign = CampaignKam::create(['unique_id_report' => 'USER-REPORT-001']);
        $report = CampaignKamReport::create([
            'campaign_kam_id' => $emptyCampaign->id,
            'unique_id' => 'MESSAGE-001',
            'campaign_id' => 'USER-REPORT-001',
        ]);

        $this->assertNull($emptyCampaign->fresh()->unique_id_report);
        $this->assertCount(0, $emptyCampaign->reports);
        $this->assertSame($report->id, $campaign->reports->sole()->id);
        $this->assertSame($campaign->id, $report->campaign->id);

        $this->assertTrue((bool) CampaignKam::withExists('reports')->find($campaign->id)->reports_exists);
        $this->assertFalse((bool) CampaignKam::withExists('reports')->find($emptyCampaign->id)->reports_exists);

        $lastUpdateSql = (new \ReflectionClass(\App\Http\Controllers\CampaignKamDashboardController::class))
            ->getConstant('REPORT_LAST_UPDATE');
        $campaignQuery = CampaignKam::select('campaign_kam.*');
        $joinUpdates = new \ReflectionMethod(\App\Http\Controllers\CampaignKamDashboardController::class, 'joinReportUpdates');
        $joinUpdates->invoke(new \App\Http\Controllers\CampaignKamDashboardController, $campaignQuery);
        $externalReportCampaign = $campaignQuery
            ->selectRaw($lastUpdateSql . ' as report_last_updated_at')
            ->find($campaign->id);
        $this->assertNull($externalReportCampaign->report_csv_uploaded_at);
        $this->assertSame($report->updated_at->format('Y-m-d H:i:s'), $externalReportCampaign->report_last_updated_at);

        foreach (['succeeded', 'SUCCEEDED', 'failed', 'FAILED', 'pending'] as $index => $status) {
            CampaignKamReport::create(['campaign_kam_id' => $emptyCampaign->id, 'unique_id' => 'MESSAGE-'.($index + 2), 'campaign_id' => 'USER-REPORT-001', 'status' => $status]);
        }
        $sms = CampaignKam::create(['unique_id_report' => 'SMS-REPORT', 'channel' => 'sms']);
        CampaignKamReport::create(['campaign_kam_id' => $sms->id, 'unique_id' => 'MESSAGE-SMS', 'campaign_id' => 'SMS-REPORT', 'status' => 'succeeded']);
        CampaignKamReport::create(['campaign_kam_id' => $sms->id, 'unique_id' => 'USER-REPORT-001', 'campaign_id' => 'UNMATCHED', 'status' => 'succeeded']);

        $statistics = new \ReflectionMethod(\App\Http\Controllers\CampaignKamDashboardController::class, 'reportStatistics');
        $query = CampaignKamReport::query()->join('campaign_kam', 'campaign_kam.unique_id_report', '=', 'campaign_kam_reports.campaign_id');
        $stats = $statistics->invoke(new \App\Http\Controllers\CampaignKamDashboardController, clone $query)->keyBy('channel_key');
        $this->assertSame(7, (int) $stats->sum('total_rows'));
        $this->assertSame(3, (int) $stats->sum('succeeded_rows'));
        $this->assertSame(2, (int) $stats->sum('failed_rows'));
        $this->assertSame(6, (int) $stats['WABA']->total_rows);
        $this->assertSame(1, (int) $stats['SMS']->succeeded_rows);
        $empty = $statistics->invoke(new \App\Http\Controllers\CampaignKamDashboardController, $query->where('campaign_kam.id', -1));
        $this->assertCount(0, $empty);
    }
}
