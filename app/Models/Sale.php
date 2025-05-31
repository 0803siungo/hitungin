<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'marketplace',
        'order_number',
        'qty',
        'price',
        'buyer_username',
        'sold_at',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'sold_at' => 'datetime',
    ];

    // Relasi ke produk
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
