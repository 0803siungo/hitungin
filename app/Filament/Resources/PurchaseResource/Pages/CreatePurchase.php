<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Product;
use App\Models\StockMovement;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function afterCreate(): void
    {
        $purchase = $this->record;

        foreach ($purchase->items as $item) {
            $product = Product::find($item->product_id);

            // Asumsi setiap Purchase punya relasi items: product_id, sku, qty
            StockMovement::create([
                'product_id' => $item->product_id,
                'type' => 'in',
                'source_type' => 'purchase',
                'source_id' => $purchase->id,
                'qty' => $item->qty,
                'note' => 'Pembelian dari Supplier: ' . ($purchase->supplier->name ?? ''),
                'meta' => [
                    'invoice' => $purchase->invoice_number ?? null,
                    'supplier' => $purchase->supplier->name ?? null,
                ],
                'moved_at' => $purchase->purchased_date ?? now(),
            ]);

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
