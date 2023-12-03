<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'active',
        'name',
        'description',
        'picture'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function prices()
    {
        return $this->belongsToMany(PriceType::class)->withPivot('price');
    }
}
