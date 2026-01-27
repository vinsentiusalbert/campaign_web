<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignMobile extends Model
{
    
    protected $table = 'campaign_mobile';

    protected $fillable = [
        'user_id',
        'area',
        'region',
        'branch',
        'campaign_usecase',
        'message_body',
        'kv_message_link',
        'shortmax_user_type',
        'nama_file_whitelist',
        'periode_campaign_start',
        'periode_campaign_end',
        'jumlah_blast',
        'cc',
        'nama_campaign',
        'status'
    ];
}
