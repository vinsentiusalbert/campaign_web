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
        Schema::create('campaign_kam_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_kam_id')->constrained('campaign_kam')->cascadeOnDelete();
            $table->string('campaign_id')->nullable();
            $table->date('created_date')->nullable();
            $table->string('created_time')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('template_name')->nullable();
            $table->string('category')->nullable();
            $table->string('msisdn')->nullable();
            $table->string('status')->nullable();
            $table->text('vendor_ref_id')->nullable();
            $table->date('sent_date')->nullable();
            $table->string('sent_time')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['campaign_kam_id', 'created_date']);
            $table->index(['campaign_kam_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_kam_reports');
    }
};
