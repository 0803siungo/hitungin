<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceShop extends Model
{
    protected $fillable = [
        'platform',
        'shop_id',
        'shop_name',
        'username',
        'region',
        'access_token',
        'refresh_token',
        'token_expired_at',
    ];

    protected $dates = ['token_expired_at'];

    // Relasi ke sales
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
