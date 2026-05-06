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
            if (! Schema::hasColumn('campaign_kam', 'total_read')) {
                $table->unsignedBigInteger('total_read')->nullable()->after('jumlah_blast');
            }
            if (! Schema::hasColumn('campaign_kam', 'total_revenue')) {
                $table->decimal('total_revenue', 18, 2)->nullable()->after('total_read');
            }
            if (! Schema::hasColumn('campaign_kam', 'sisa_saldo')) {
                $table->decimal('sisa_saldo', 18, 2)->nullable()->after('total_revenue');
            }
            if (! Schema::hasColumn('campaign_kam', 'balance_terpakai')) {
                $table->decimal('balance_terpakai', 18, 2)->nullable()->after('sisa_saldo');
            }
            if (! Schema::hasColumn('campaign_kam', 'report_csv_uploaded_at')) {
                $table->timestamp('report_csv_uploaded_at')->nullable()->after('report_csv_file');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_kam', function (Blueprint $table) {
            foreach (['total_read', 'total_revenue', 'sisa_saldo', 'balance_terpakai', 'report_csv_uploaded_at'] as $column) {
                if (Schema::hasColumn('campaign_kam', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
