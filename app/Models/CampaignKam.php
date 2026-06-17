<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignKam extends Model
{
    protected $table = 'campaign_kam';

    protected $fillable = [
        'user_id',
        'template_name',
        'sender_name',
        'channel',
        'campaign_unique_id',
        'campaign_usecase',
        'message_body',
        'text_button',
        'link_button',
        'kv_message_link',
        'campaign_type',
        'nama_file_whitelist',
        'report_csv_file',
        'longitude_latitude',
        'radius',
        'periode_campaign_start',
        'periode_campaign_end',
        'jumlah_blast',
        'total_read',
        'total_revenue',
        'sisa_saldo',
        'balance_terpakai',
        'nama_template',
        'vendor',
        'carousel_product_1',
        'kv_product_1',
        'carousel_product_2',
        'kv_product_2',
        'carousel_product_3',
        'kv_product_3',
        'carousel_product_4',
        'kv_product_4',
        'carousel_product_5',
        'kv_product_5',
        'cc',
        'status',
        'status_testing',
        'report_csv_uploaded_at',
    ];

    protected $casts = [
        'periode_campaign_start' => 'datetime',
        'periode_campaign_end'   => 'datetime',
        'report_csv_uploaded_at' => 'datetime',
    ];

    // Relasi user (opsional)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reports()
    {
        return $this->hasMany(CampaignKamReport::class, 'campaign_kam_id');
    }
}



