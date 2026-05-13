<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignMobile extends Model
{
    
    protected $table = 'campaign_mobile';

    protected $fillable = [
        'user_id',
        'template_name',
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
        'vendor',
        'nama_campaign',
        'status',
        'status_testing'
    ];
}





