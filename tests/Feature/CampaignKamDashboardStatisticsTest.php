<?php

namespace Tests\Feature;

use App\Http\Controllers\CampaignKamDashboardController;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class CampaignKamDashboardStatisticsTest extends TestCase
{
    public function test_statistics_filters_and_export_preserve_report_values(): void
    {
        $previousContainer = Container::getInstance();
        $previousFacadeApp = Facade::getFacadeApplication();
        $db = new Capsule;
        $db->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $db->setAsGlobal();
        $db->bootEloquent();
        $container = $db->getContainer();
        $container->instance('db', $db->getDatabaseManager());
        $auth = \Mockery::mock(\Illuminate\Contracts\Auth\Factory::class);
        $auth->shouldReceive('user')->andReturn((object) ['role' => 'Admin']);
        $container->instance(\Illuminate\Contracts\Auth\Factory::class, $auth);
        $container->instance(\Illuminate\Contracts\View\Factory::class, new class {
            public function make($view, $data, $mergeData = []) {
                $result = \Mockery::mock(\Illuminate\Contracts\View\View::class);
                $result->shouldReceive('getData')->andReturn($data);
                return $result;
            }
        });
        $responses = \Mockery::mock(\Illuminate\Contracts\Routing\ResponseFactory::class);
        $responses->shouldReceive('streamDownload')->andReturnUsing(fn ($callback, $name, $headers) => new \Symfony\Component\HttpFoundation\StreamedResponse($callback, 200, $headers));
        $container->instance(\Illuminate\Contracts\Routing\ResponseFactory::class, $responses);
        Container::setInstance($container);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($container);

        try {
            Capsule::schema()->create('campaign_kam', function (Blueprint $table) {
                $table->id();
                foreach (['campaign_unique_id', 'unique_id_report', 'template_name', 'sender_name', 'channel'] as $column) {
                    $table->string($column)->nullable();
                }
                $table->integer('total_read')->default(0);
                $table->decimal('total_revenue')->default(0);
                $table->timestamp('report_csv_uploaded_at')->nullable();
            });
            Capsule::schema()->create('campaign_kam_reports', function (Blueprint $table) {
                $table->id();
                $table->string('unique_id');
                $table->string('campaign_id');
                $table->string('status')->nullable();
                $table->timestamps();
            });
            Capsule::schema()->create('kam_global_saldos', function (Blueprint $table) {
                $table->id(); $table->string('name'); $table->decimal('balance'); $table->timestamps();
            });
            Capsule::schema()->create('kam_global_saldo_histories', function (Blueprint $table) {
                $table->id(); $table->unsignedBigInteger('kam_global_saldo_id'); $table->timestamps();
            });
            Capsule::table('kam_global_saldos')->insert(['id' => 1, 'name' => 'Global KAM', 'balance' => 10000]);
            Capsule::table('campaign_kam')->insert([
                ['id' => 1, 'campaign_unique_id' => 'A', 'unique_id_report' => 'A', 'channel' => null, 'total_read' => 4, 'total_revenue' => 100, 'report_csv_uploaded_at' => null],
                ['id' => 2, 'campaign_unique_id' => 'B', 'unique_id_report' => 'B', 'channel' => 'sms', 'total_read' => 2, 'total_revenue' => 200, 'report_csv_uploaded_at' => null],
                ['id' => 3, 'campaign_unique_id' => 'C', 'unique_id_report' => null, 'channel' => 'MMS', 'total_read' => 1, 'total_revenue' => 300, 'report_csv_uploaded_at' => '2026-10-01 12:00:00'],
            ]);
            foreach ([['A', 'succeeded', '2026-10-01'], ['A', 'SUCCEEDED', '2026-10-02'], ['A', 'failed', '2026-10-02'], ['A', null, '2026-10-02'], ['B', 'succeeded', '2026-10-03'], ['UNMATCHED', 'succeeded', '2026-10-03']] as [$id, $status, $date]) {
                Capsule::table('campaign_kam_reports')->insert(['unique_id' => 'MESSAGE-'.$id, 'campaign_id' => $id, 'status' => $status, 'created_at' => $date.' 00:00:00', 'updated_at' => null]);
            }
            $controller = new CampaignKamDashboardController;
            foreach ([
                [[], [3, 1, 5, 7, 600, 890]],
                [['campaign_id' => [1]], [2, 1, 4, 4, 100, 790]],
                [['campaign_id' => [1, 2]], [3, 1, 5, 6, 300, 890]],
                [['update_from' => '2026-10-02', 'update_to' => '2026-10-02'], [2, 1, 4, 4, 100, 790]],
                [['update_to' => '2026-10-01'], [0, 0, 0, 1, 300, 0]],
                [['update_from' => '2099-01-01'], [0, 0, 0, 0, 0, 0]],
                [['campaign_id' => [-1]], [3, 1, 5, 7, 600, 890]],
            ] as [$input, $expected]) {
                $data = $controller->index(new Request($input))->getData();
                foreach (array_combine(['totalDelivered', 'failedReportCount', 'tableRowCount', 'totalRead', 'totalRevenue', 'balanceTerpakai'], $expected) as $key => $value) {
                    $this->assertEquals($value, $data[$key], $key.' '.json_encode($input));
                }
                $this->assertEquals(10000 - $expected[5], $data['sisaSaldo']);
                $detail = (new \ReflectionMethod($controller, 'detailReportQuery'))->invoke($controller, new Request($input));
                $this->assertStringNotContainsString('MAX(', $detail->toSql());
                $this->assertSame($expected[2], $detail->count());
                $this->assertSame($expected[2], (clone $detail)->orderByDesc('report_csv_uploaded_at')->get()->count());
            }
            $this->assertTrue($controller->index(new Request(['campaign_id' => [3]]))->getData()['showMissingUploadWarning']);
            $response = $controller->downloadCsv(new Request(['campaign_id' => [1]]));
            ob_start();
            $response->sendContent();
            $csv = ob_get_clean();
            $lines = array_map('str_getcsv', explode("\n", trim($csv)));
            $this->assertCount(5, $lines);
            $this->assertSame('MESSAGE-A', $lines[1][2]);
            $this->assertSame('A', $lines[1][4]);
            $this->assertSame('02-10-2026 00:00', $lines[1][1]);
        } finally {
            Facade::clearResolvedInstances();
            Facade::setFacadeApplication($previousFacadeApp);
            Container::setInstance($previousContainer);
        }
    }
}
