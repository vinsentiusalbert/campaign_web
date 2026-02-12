<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignNomorCantik extends Model
{
    
    protected $table = 'campaign_nomor_cantik';

    protected $fillable = [
        'user_id',
        'area',
        'region',
        'branch',
        'campaign_usecase',
        'message_body',
        'kv_message_link',
        'shortmax_user_type',
        'campaign_type',
        'nama_file_whitelist',
        'longitude_latitude',
        'radius',
        'periode_campaign_start',
        'periode_campaign_end',
        'jumlah_blast',
        'cc',
        'nama_campaign',
        'status'
    ];
}

