<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KamGlobalSaldo extends Model
{
    protected $table = 'kam_global_saldos';

    protected $fillable = [
        'name',
        'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function histories()
    {
        return $this->hasMany(KamGlobalSaldoHistory::class, 'kam_global_saldo_id');
    }
}
