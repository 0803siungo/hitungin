<?php

namespace App\Filament\Pages;

use App\Models\StockMovement;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithFileUploads;
use App\Filament\Resources\SaleResource;
use App\Models\Product;
use App\Models\Sale;

class ImportSales extends Page
{
    use WithFileUploads;
    protected static string $resource = SaleResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static string $view = 'filament.pages.import-sales';

    public $data = [
        'marketplace' => null,
        'type' => null,
        'file' => [],
    ];

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('marketplace')
                ->label('Marketplace')
                ->statePath('data.marketplace')
                ->required()
                ->options([
                    'shopee' => 'Shopee',
                    'tokopedia' => 'Tokopedia',
                    'tiktok' => 'TikTok',
                    // Tambah lainnya sesuai kebutuhan
                ]),
            Forms\Components\Select::make('type')
                ->label('Status Transaksi')
                ->statePath('data.type')
                ->required()
                ->options([
                    'sales' => 'Sales',
                    'return' => 'Return',
                    // Tambah lainnya jika ada
                ]),
            Forms\Components\FileUpload::make('file')
                ->label('Excel File')
                ->statePath('data.file')
                ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                ->disk('local')
                ->required(),
        ]);
    }

    public function submit()
    {
        $this->validate([
            'data.marketplace' => 'required',
            'data.type' => 'required',
            'data.file' => 'required',
        ]);

        $path = $this->data['file']; // file path dari FileUpload
        foreach ($this->data['file'] as $file) {
            $data = Excel::toArray([], $file);
            $rows = $data[0];

            $header = array_map('trim', $rows[0]);

            $indexSku = array_search('Nomor Referensi SKU', $header);
            $indexQty = array_search('Jumlah', $header);
            $indexPrice = array_search('Harga Setelah Diskon', $header);
            $indexOrderNum = array_search('No. Pesanan', $header);
            $indexSoldAt = array_search('Waktu Pesanan Dibuat', $header);
            $indexBuyer = array_search('Username (Pembeli)', $header);

            foreach (array_slice($rows, 1) as $row) {
                $sku = $row[$indexSku];
                $qty = (int) $row[$indexQty];
                $price = (float) str_replace('.', '', $row[$indexPrice]);
                $orderNumber = $row[$indexOrderNum];
                $soldAt = $row[$indexSoldAt];
                $buyer = $row[$indexBuyer] ?? null;

                $product = Product::where('sku', $sku)->first();
                if (!$product) {
                    // Bisa collect error/warning untuk tampilkan ke user
                    continue;
                }

                $sale = Sale::create([
                    'product_id' => $product->id,
                    'sku' => $sku,
                    'marketplace' => $this->data['marketplace'],
                    'order_number' => $orderNumber,
                    'qty' => $qty,
                    'price' => $price,
                    'buyer_username' => $buyer,
                    'sold_at' => $soldAt,
                    'raw_data' => json_encode($row),
                ]);

                $product->decrement('stock', $qty);

                StockMovement::create([
                    'product_id' => $product->id,
                    'sku' => $sku,
                    'type' => 'out',
                    'source_type' => 'sales',
                    'source_id' => $sale->id,
                    'qty' => -$qty,
                    'note' => 'Import sales ' . $this->data['marketplace'] . ' #' . $orderNumber,
                    'meta' => [
                        'buyer' => $buyer,
                        'price' => $price,
                        'imported_by' => auth()->user()->name ?? null,
                    ],
                    'moved_at' => $soldAt ?? now(),
                ]);
            }
            break;
        }

        // Proses parsing dan preview/import Excel
        // ...
        Notification::make()
            ->title('File uploaded!')
            ->success()
            ->send();
    }
}
