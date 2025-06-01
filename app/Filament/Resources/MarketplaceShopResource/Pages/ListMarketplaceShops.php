<?php

namespace App\Filament\Resources\MarketplaceShopResource\Pages;

use App\Filament\Resources\MarketplaceShopResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketplaceShops extends ListRecords
{
    protected static string $resource = MarketplaceShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('connectShopee')
                ->label('Hubungkan Shopee')
                ->icon('heroicon-o-link')
                ->color('success')
                ->url(route('shopee.auth.redirect')) // route ini handle proses OAuth
                ->openUrlInNewTab(),
        ];
    }
}
