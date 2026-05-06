<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignKamReport extends Model
{
    protected $table = 'campaign_kam_reports';

    protected $fillable = [
        'campaign_kam_id',
        'campaign_id',
        'created_date',
        'created_time',
        'sender_name',
        'template_name',
        'category',
        'msisdn',
        'status',
        'vendor_ref_id',
        'sent_date',
        'sent_time',
        'note',
    ];

    protected $casts = [
        'created_date' => 'date',
        'sent_date' => 'date',
    ];

    public function campaign()
    {
        return $this->belongsTo(CampaignKam::class, 'campaign_kam_id');
    }

    public function getMsisdnAttribute($value): ?string
    {
        return self::normalizeMsisdnValue($value);
    }

    public static function normalizeMsisdnValue($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^[0-9]+$/', $value)) {
            return $value;
        }

        if (preg_match('/^([0-9]+(?:\.[0-9]+)?)E\+?([0-9]+)$/i', $value, $matches)) {
            $mantissa = $matches[1];
            $exponent = (int) $matches[2];

            $parts = explode('.', $mantissa);
            $whole = $parts[0];
            $fraction = $parts[1] ?? '';
            $digits = $whole . $fraction;
            $zeroCount = $exponent - strlen($fraction);

            if ($zeroCount >= 0) {
                return ltrim($digits . str_repeat('0', $zeroCount), '0') ?: '0';
            }
        }

        $digitsOnly = preg_replace('/\D+/', '', $value);

        return $digitsOnly !== '' ? $digitsOnly : $value;
    }
}
