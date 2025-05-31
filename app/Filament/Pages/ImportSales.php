<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Notifications\Notification;
use Livewire\WithFileUploads;
use App\Filament\Resources\SaleResource;
use App\Services\ImportExcelService;

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
            Forms\Components\Select::make('type')
                ->label('Status Transaksi')
                ->statePath('data.type')
                ->required()
                ->options([
                    'sales' => 'Sales',
                    'return' => 'Return',
                    'sku' => 'SKU',
                    // Tambah lainnya jika ada
                ]),

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
        // Validasi
        $this->validate([
            'data.marketplace' => 'required',
            'data.type' => 'required',
            'data.file' => 'required',
        ]);
        // Proses import data dari file excel

        $type = $this->data['type'];
        $importer = new ImportExcelService();

        foreach ($this->data['file'] as $file) {
            // dd($file->getRealPath());
            if ($type == "sku") {
                $result = $importer->importProducts(
                    $file->getRealPath(),
                    auth()->user()->name ?? null,
                );
            } elseif ($type == "return") {
                $result = $importer->importReturns(
                    $file->getRealPath(),
                    $this->data['marketplace'],
                    auth()->user()->name ?? null,
                );
            } else {
                $result = $importer->importSales(
                    $file->getRealPath(),
                    $this->data['marketplace'],
                    auth()->user()->name ?? null,
                );
            }
            break;
        }

        if ($result['failed'] > 0) {
            Notification::make()
                ->title('Import gagal!')
                ->body('Berhasil: ' . $result['success'] . ', Gagal: ' . $result['failed'] . ', Error: ' . implode(', ', $result['errors']))
                ->danger()
                ->send();
        } else {
            Notification::make()
                ->title('Import selesai!')
                ->body('Total: ' . $result['success'])
                ->success()
                ->send();
        }
    }
}
