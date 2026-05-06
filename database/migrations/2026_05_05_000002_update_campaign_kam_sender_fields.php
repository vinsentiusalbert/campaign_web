<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('campaign_kam')) {
            return;
        }

        Schema::table('campaign_kam', function (Blueprint $table) {
            if (! Schema::hasColumn('campaign_kam', 'sender_name')) {
                $table->string('sender_name')->nullable()->after('template_name');
            }

            if (! Schema::hasColumn('campaign_kam', 'campaign_unique_id')) {
                $table->string('campaign_unique_id')->nullable()->after('sender_name');
            }
        });

        foreach (['area', 'region', 'branch'] as $column) {
            if (Schema::hasColumn('campaign_kam', $column)) {
                Schema::table('campaign_kam', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }

        $existingCodes = DB::table('campaign_kam')
            ->select('id', 'sender_name', 'campaign_unique_id')
            ->get();

        foreach ($existingCodes as $row) {
            if (! empty($row->campaign_unique_id)) {
                continue;
            }

            $prefix = match ($row->sender_name) {
                'Halo Care' => 'HC',
                'Indihome Care' => 'IC',
                'PT KAM Via Telkomsel' => 'PKVT',
                default => 'KAM',
            };

            DB::table('campaign_kam')
                ->where('id', $row->id)
                ->update([
                    'campaign_unique_id' => sprintf('%s-%04d', $prefix, $row->id),
                ]);
        }

        if (! $this->hasUniqueIndex('campaign_kam', 'campaign_kam_campaign_unique_id_unique')) {
            Schema::table('campaign_kam', function (Blueprint $table) {
                $table->unique('campaign_unique_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('campaign_kam')) {
            return;
        }

        if ($this->hasUniqueIndex('campaign_kam', 'campaign_kam_campaign_unique_id_unique')) {
            Schema::table('campaign_kam', function (Blueprint $table) {
                $table->dropUnique('campaign_kam_campaign_unique_id_unique');
            });
        }

        Schema::table('campaign_kam', function (Blueprint $table) {
            if (! Schema::hasColumn('campaign_kam', 'area')) {
                $table->string('area')->nullable()->after('template_name');
            }
            if (! Schema::hasColumn('campaign_kam', 'region')) {
                $table->string('region')->nullable()->after('area');
            }
            if (! Schema::hasColumn('campaign_kam', 'branch')) {
                $table->string('branch')->nullable()->after('region');
            }
            if (Schema::hasColumn('campaign_kam', 'sender_name')) {
                $table->dropColumn('sender_name');
            }
            if (Schema::hasColumn('campaign_kam', 'campaign_unique_id')) {
                $table->dropColumn('campaign_unique_id');
            }
        });
    }

    private function hasUniqueIndex(string $table, string $indexName): bool
    {
        $databaseName = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $databaseName)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }
};
