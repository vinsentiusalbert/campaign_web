<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_kam', function (Blueprint $table) {
            if (! Schema::hasColumn('campaign_kam', 'text_button')) {
                $table->string('text_button')->nullable()->after('message_body');
            }

            if (! Schema::hasColumn('campaign_kam', 'link_button')) {
                $table->string('link_button')->nullable()->after('text_button');
            }
        });
    }

    public function down(): void
    {
        Schema::table('campaign_kam', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('campaign_kam', 'link_button')) {
                $columns[] = 'link_button';
            }

            if (Schema::hasColumn('campaign_kam', 'text_button')) {
                $columns[] = 'text_button';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
