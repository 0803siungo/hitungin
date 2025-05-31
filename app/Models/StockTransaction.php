<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    protected $fillable = [
        'product_id',
        'transaction_date',
        'transaction_type',
        'reference_id',
        'reference_type',
        'qty',
        'notes',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
