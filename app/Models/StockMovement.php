<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'source_type',
        'source_id',
        'qty',
        'note',
        'meta',
        'moved_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'moved_at' => 'datetime',
    ];

    // Relasi ke Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Polymorphic relasi ke sumber (sales, purchases, dsb) -- opsional, manual
    public function source()
    {
        return $this->morphTo(__FUNCTION__, 'source_type', 'source_id');
    }
}
