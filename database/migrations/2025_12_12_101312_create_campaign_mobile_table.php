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
        Schema::create('campaign_mobile', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('area')->nullable();
            $table->string('region')->nullable();
            $table->string('branch')->nullable();

            $table->string('campaign_usecase')->nullable();

            $table->longText('message_body')->nullable();

            $table->text('kv_message_link')->nullable(); // Link GDrive KV Message
            $table->string('shortmax_user_type')->nullable();

            $table->text('nama_file_whitelist')->nullable();

            $table->date('periode_campaign_start')->nullable();
            $table->date('periode_campaign_end')->nullable();

            $table->integer('jumlah_blast')->nullable();

            $table->text('cc')->nullable();

            $table->string('nama_campaign')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_mobile');
    }
};
