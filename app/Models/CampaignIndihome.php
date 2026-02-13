<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignIndihome extends Model
{
    protected $table = 'campaign_indihome';

    protected $fillable = [
        'user_id',
        'area',
        'region',
        'branch',
        'campaign_usecase',
        'message_body',
        'kv_message_link',
        'campaign_type',
        'nama_file_whitelist',
        'longitude_latitude',
        'radius',
        'periode_campaign_start',
        'periode_campaign_end',
        'jumlah_blast',
        'nama_template',
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
        'status_testing'
    ];

    protected $casts = [
        'periode_campaign_start' => 'datetime',
        'periode_campaign_end'   => 'datetime',
    ];

    // Relasi user (opsional)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
