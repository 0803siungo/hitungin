<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Product;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function afterCreate(): void
    {
        $purchase = $this->record;

        foreach ($purchase->items as $item) {
            $product = Product::find($item->product_id);

            if ($product) {
                // Update stock
                $product->stock += $item->qty;

                // Update estimated_buy_price (Average Cost sederhana)
                if ($product->estimated_buy_price) {
                    $totalQty = $product->stock;
                    $oldValue = $product->estimated_buy_price * ($totalQty - $item->qty);
                    $newValue = $item->buy_price * $item->qty;
                    $averagePrice = ($oldValue + $newValue) / $totalQty;

                    $product->estimated_buy_price = $averagePrice;
                } else {
                    $product->estimated_buy_price = $item->buy_price;
                }

                $product->save();
            }
        }
    }
}
