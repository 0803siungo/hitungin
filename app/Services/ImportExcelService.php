<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Returns; // pakai nama model sesuai table returns kamu
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportExcelService
{
    // Import sales (penjualan)
    public function importSales(
        string $file,
        string $marketplace,
        ?string $importedBy = null
    ): array {
        $data = Excel::toArray([], $file);
        $rows = $data[0] ?? [];
        if (count($rows) < 2) {
            return ['success' => 0, 'failed' => 0, 'errors' => ['File kosong atau format tidak sesuai']];
        }
        $header = array_map('trim', $rows[0]);

        $indexSku = array_search('Nomor Referensi SKU', $header);
        $indexQtySales = array_search('Jumlah', $header);
        $indexPrice = array_search('Harga Setelah Diskon', $header);
        $indexOrderNum = array_search('No. Pesanan', $header);
        $indexSoldAt = array_search('Waktu Pesanan Dibuat', $header);
        $indexBuyer = array_search('Username (Pembeli)', $header);

        $success = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach (array_slice($rows, 1) as $row) {
                $sku = $row[$indexSku] ?? null;
                $qty = (int) ($row[$indexQtySales] ?? 0);
                if ($qty < 1)
                    continue;

                $price = (float) ($row[$indexPrice] ? str_replace('.', '', $row[$indexPrice]) : 0);
                $orderNumber = $row[$indexOrderNum] ?? null;
                $soldAt = $row[$indexSoldAt] ?? now();
                $buyer = $row[$indexBuyer] ?? null;

                if (!$sku)
                    continue;

                $product = Product::where('sku', $sku)->lockForUpdate()->first();
                if (!$product) {
                    $failed++;
                    $errors[] = "SKU tidak ditemukan: {$sku}";
                    continue;
                }

                // Insert ke sales
                $sale = Sale::create([
                    'product_id' => $product->id,
                    'sku' => $sku,
                    'marketplace' => $marketplace,
                    'order_number' => $orderNumber,
                    'qty' => $qty,
                    'price' => $price,
                    'buyer_username' => $buyer,
                    'sold_at' => $soldAt,
                    'raw_data' => json_encode($row),
                    'type' => 'sales', // pastikan sudah ada kolom type di sales
                ]);

                // Update stok (pengurangan utk sales)
                $product->decrement('stock', $qty);

                // Insert ke stock_movements
                StockMovement::create([
                    'product_id' => $product->id,
                    'sku' => $sku,
                    'type' => 'out',
                    'source_type' => 'sales',
                    'source_id' => $sale->id,
                    'qty' => -$qty,
                    'note' => $marketplace . '#' . $orderNumber,
                    'meta' => [
                        'buyer' => $buyer,
                        'price' => $price,
                        'imported_by' => $importedBy ?? (Auth::user()->name ?? null),
                    ],
                    'moved_at' => $soldAt ?? now(),
                ]);
                $success++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => $success, 'failed' => $failed, 'errors' => [$e->getMessage()]];
        }

        return ['success' => $success, 'failed' => $failed, 'errors' => $errors];
    }

    // Import return ke table returns
    public function importReturns(
        string $file,
        string $marketplace,
        ?string $importedBy = null
    ): array {
        $data = Excel::toArray([], $file);
        $rows = $data[0] ?? [];
        if (count($rows) < 2) {
            return ['success' => 0, 'failed' => 0, 'errors' => ['File kosong atau format tidak sesuai']];
        }
        $header = array_map('trim', $rows[0]);

        $indexSku = array_search('Nomor Referensi SKU', $header);
        $indexQtyReturn = array_search('Jumlah', $header);
        $indexPrice = array_search('Harga Setelah Diskon', $header);
        $indexOrderNum = array_search('No. Pesanan', $header);
        $indexSoldAt = array_search('Waktu Pesanan Dibuat', $header);
        $indexBuyer = array_search('Username (Pembeli)', $header);

        $success = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach (array_slice($rows, 1) as $row) {
                $sku = $row[$indexSku] ?? null;
                $qty = (int) ($row[$indexQtyReturn] ?? 0);
                if ($qty < 1)
                    continue;

                $price = (float) ($row[$indexPrice] ? str_replace('.', '', $row[$indexPrice]) : 0);
                $orderNumber = $row[$indexOrderNum] ?? null;
                $soldAt = $row[$indexSoldAt] ?? now();
                $buyer = $row[$indexBuyer] ?? null;

                if (!$sku)
                    continue;

                $product = Product::where('sku', $sku)->lockForUpdate()->first();
                if (!$product) {
                    $failed++;
                    $errors[] = "SKU tidak ditemukan: {$sku}";
                    continue;
                }

                $sale = Sale::where('order_number', $orderNumber)->first();

                // Insert ke returns (gunakan model sesuai dengan nama model returns kamu)
                $return = Returns::create([
                    'sale_id' => $sale?->id,
                    'product_id' => $product->id,
                    'sku' => $sku,
                    'qty' => $qty,
                    'price' => $price,
                    'marketplace' => $marketplace,
                    'order_number' => $orderNumber,
                    'buyer_username' => $buyer,
                    'returned_at' => $soldAt,
                    'status' => 'completed',
                    'reason' => null,
                    'note' => null,
                    'raw_data' => json_encode($row),
                ]);

                // Update stok (masuk)
                $product->increment('stock', $qty);

                // Insert ke stock_movements (stok masuk)
                StockMovement::create([
                    'product_id' => $product->id,
                    'sku' => $sku,
                    'type' => 'in',
                    'source_type' => 'return',
                    'source_id' => $return->id,
                    'qty' => $qty,
                    'note' => $marketplace . ' #' . $orderNumber,
                    'meta' => [
                        'buyer' => $buyer,
                        'price' => $price,
                        'imported_by' => $importedBy ?? (Auth::user()->name ?? null),
                    ],
                    'moved_at' => $soldAt ?? now(),
                ]);
                $success++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => $success, 'failed' => $failed, 'errors' => [$e->getMessage()]];
        }

        return ['success' => $success, 'failed' => $failed, 'errors' => $errors];
    }

    // Import product master (SKU, nama, stock, dst) tetap sama
    public function importProducts(
        string $file,
        ?string $importedBy = null
    ): array {
        $data = Excel::toArray([], $file);
        $rows = $data[0] ?? [];
        if (count($rows) < 2) {
            return ['success' => 0, 'failed' => 0, 'errors' => ['File kosong atau format tidak sesuai']];
        }
        $header = array_map('trim', $rows[0]);
        $indexSku = array_search('SKU', $header);
        $indexName = array_search('Nama', $header);
        $indexStock = array_search('Stock', $header);

        $success = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach (array_slice($rows, 1) as $row) {
                $sku = $row[$indexSku] ?? null;
                $name = trim($row[$indexName] ?? '');
                $stock = (int) ($row[$indexStock] ?? 0);

                if (!$sku) {
                    $failed++;
                    $errors[] = "SKU tidak boleh kosong";
                    continue;
                }

                // Cari produk
                $product = Product::where('sku', $sku)->lockForUpdate()->first();

                if (!$product) {
                    // Buat baru jika SKU belum ada
                    $product = Product::create([
                        'sku' => $sku,
                        'name' => $name ?: 'Unknown',
                        'stock' => $stock,
                    ]);
                    $isNew = true;
                    $oldStock = 0;
                } else {
                    $updateData = ['stock' => $stock];
                    if ($name !== '') {
                        $updateData['name'] = $name;
                    }
                    $oldStock = $product->stock;
                    $product->update($updateData);
                    $isNew = false;
                }

                $diffStock = $stock - $oldStock;

                if ($diffStock !== 0) {
                    StockMovement::create([
                        'product_id' => $product->id,
                        'sku' => $sku,
                        'type' => $diffStock > 0 ? 'in' : 'out',
                        'source_type' => 'adjust',
                        'source_id' => null,
                        'qty' => $diffStock,
                        'note' => $isNew ? 'Stock awal (import produk)' : 'Penyesuaian stock upload Excel',
                        'meta' => [
                            'imported_by' => $importedBy ?? (Auth::user()->name ?? null),
                            'from_import_excel' => true,
                            'prev_stock' => $oldStock,
                            'new_stock' => $stock,
                        ],
                        'moved_at' => now(),
                    ]);
                }

                $success++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => $success, 'failed' => $failed, 'errors' => [$e->getMessage()]];
        }

        return ['success' => $success, 'failed' => $failed, 'errors' => $errors];
    }
}
