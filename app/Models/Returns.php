<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Returns extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'sku',
        'qty',
        'price',
        'marketplace',
        'order_number',
        'buyer_username',
        'reason',
        'status',
        'note',
        'raw_data',
        'returned_at',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'returned_at' => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
