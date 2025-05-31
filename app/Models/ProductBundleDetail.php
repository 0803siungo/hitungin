<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBundleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'bundle_product_id',
        'component_product_id',
        'qty',
    ];

    public function bundle()
    {
        return $this->belongsTo(Product::class, 'bundle_product_id');
    }

    public function component()
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }
}
