<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        $sources = [
            ['table' => 'users', 'column' => 'vendor'],
            ['table' => 'campaign_mobile', 'column' => 'vendor'],
            ['table' => 'campaign_indihome', 'column' => 'vendor'],
            ['table' => 'campaign_orbit', 'column' => 'vendor'],
            ['table' => 'campaign_soundbox', 'column' => 'vendor'],
            ['table' => 'campaign_nomor_cantik', 'column' => 'vendor'],
            ['table' => 'campaign_waba_interaktif', 'column' => 'vendor'],
            ['table' => 'campaign_kam', 'column' => 'vendor'],
        ];

        foreach ($sources as $source) {
            if (! Schema::hasTable($source['table']) || ! Schema::hasColumn($source['table'], $source['column'])) {
                continue;
            }

            $names = DB::table($source['table'])
                ->whereNotNull($source['column'])
                ->where($source['column'], '!=', '')
                ->distinct()
                ->pluck($source['column']);

            foreach ($names as $name) {
                DB::table('vendors')->updateOrInsert(
                    ['name' => $name],
                    ['name' => $name, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
