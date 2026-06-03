<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_kam_reports', function (Blueprint $table) {
            $table->string('unique_id')->nullable()->after('campaign_kam_id');
            $table->string('sender_id')->nullable()->after('unique_id');
            $table->date('send_date')->nullable()->after('status');
            $table->string('deliv_report_status')->nullable()->after('send_date');
            $table->date('deliv_report_date')->nullable()->after('deliv_report_status');
            $table->string('deliv_report_time')->nullable()->after('deliv_report_date');
            $table->date('deliv_read_date')->nullable()->after('deliv_report_time');
            $table->string('deliv_read_time')->nullable()->after('deliv_read_date');

            $table->index(['campaign_kam_id', 'send_date']);
            $table->index(['campaign_kam_id', 'deliv_report_status']);
        });
    }

    public function down(): void
    {
        Schema::table('campaign_kam_reports', function (Blueprint $table) {
            $table->dropIndex(['campaign_kam_id', 'send_date']);
            $table->dropIndex(['campaign_kam_id', 'deliv_report_status']);

            $table->dropColumn([
                'unique_id',
                'sender_id',
                'send_date',
                'deliv_report_status',
                'deliv_report_date',
                'deliv_report_time',
                'deliv_read_date',
                'deliv_read_time',
            ]);
        });
    }
};
