<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KamGlobalSaldoHistory extends Model
{
    protected $table = 'kam_global_saldo_histories';

    protected $fillable = [
        'kam_global_saldo_id',
        'amount',
        'balance_after',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function saldo()
    {
        return $this->belongsTo(KamGlobalSaldo::class, 'kam_global_saldo_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
