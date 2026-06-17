<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_kam', function (Blueprint $table) {
            $table->enum('channel', ['SMS', 'WABA'])->default('WABA')->after('sender_name');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_kam', function (Blueprint $table) {
            $table->dropColumn('channel');
        });
    }
};
