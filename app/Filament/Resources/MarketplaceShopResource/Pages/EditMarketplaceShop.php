<?php

namespace App\Filament\Resources\MarketplaceShopResource\Pages;

use App\Filament\Resources\MarketplaceShopResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarketplaceShop extends EditRecord
{
    protected static string $resource = MarketplaceShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
