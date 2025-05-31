<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'is_bundle',
        'stock',
        'price',
        'estimated_buy_price',
        'description',
    ];

    public function bundleComponents()
    {
        return $this->hasMany(ProductBundleDetail::class, 'bundle_product_id');
    }

    public function bundleDetails()
    {
        return $this->hasMany(ProductBundleDetail::class, 'bundle_product_id');
    }

    public function bundledIn()
    {
        return $this->hasMany(ProductBundleDetail::class, 'component_product_id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getCalculatedEstimatedBuyPriceAttribute(): ?float
    {
        if (!$this->is_bundle) {
            return $this->estimated_buy_price;
        }

        return $this->bundleComponents->sum(function ($component) {
            return optional($component->component)->estimated_buy_price * $component->qty;
        });
    }

    public function getCalculatedStockAttribute(): int
    {
        if (!$this->is_bundle) {
            return $this->stock ?? 0;
        }

        if ($this->bundleComponents->isEmpty()) {
            return 0;
        }

        return $this->bundleComponents->map(function ($component) {
            $componentProduct = $component->component;
            if (!$componentProduct || $componentProduct->stock <= 0 || $component->qty <= 0) {
                return 0;
            }
            return intdiv($componentProduct->stock, $component->qty);
        })->min();
    }

}
