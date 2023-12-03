<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'price_type_id',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function priceType()
    {
        return $this->belongsTo(PriceType::class, 'price_type_id');
    }

    public function history()
    {
        return $this->hasMany(BalanceHistory::class);
    }
}
