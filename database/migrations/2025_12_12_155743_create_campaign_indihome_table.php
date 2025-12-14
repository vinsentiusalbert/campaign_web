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
        Schema::create('campaign_indihome', function (Blueprint $table) {
            $table->id();

            // User pembuat
            $table->integer('user_id');

            // Data utama
            $table->string('area')->nullable();
            $table->string('region')->nullable();
            $table->string('branch')->nullable();

            $table->string('campaign_usecase')->nullable();
            $table->longText('message_body')->nullable();

            $table->text('kv_message_link')->nullable();

            $table->string('campaign_type')->nullable();
            $table->string('nama_file_whitelist')->nullable();

            $table->string('longitude_latitude')->nullable();
            $table->string('radius')->nullable();

            $table->timestamps('periode_campaign_start')->nullable();
            $table->timestamps('periode_campaign_end')->nullable();
            $table->integer('jumlah_blast')->nullable();

            // Carousel Product 1–5
            $table->string('carousel_product_1')->nullable();
            $table->text('kv_product_1')->nullable();
            $table->string('carousel_product_2')->nullable();
            $table->text('kv_product_2')->nullable();
            $table->string('carousel_product_3')->nullable();
            $table->text('kv_product_3')->nullable();
            $table->string('carousel_product_4')->nullable();
            $table->text('kv_product_4')->nullable();
            $table->string('carousel_product_5')->nullable();
            $table->text('kv_product_5')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_indihome');
    }
};
