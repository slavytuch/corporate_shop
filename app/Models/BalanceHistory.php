<?php

namespace App\Models;

use App\Slavytuch\Shop\User\Enums\TransferType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'value',
        'balance_id',
        'from',
        'to',
        'reason'
    ];

    protected $casts = [
        'type' => TransferType::class
    ];

    protected function balance()
    {
        return $this->belongsTo(Balance::class);
    }
}
