<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketplaceShopResource\Pages;
use App\Filament\Resources\MarketplaceShopResource\RelationManagers;
use App\Models\MarketplaceShop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MarketplaceShopResource extends Resource
{
    protected static ?string $model = MarketplaceShop::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('platform')->default('shopee')->required(),
                Forms\Components\TextInput::make('shop_id'),
                Forms\Components\TextInput::make('shop_name')->required(),
                Forms\Components\TextInput::make('username'),
                Forms\Components\TextInput::make('region'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('platform'),
                Tables\Columns\TextColumn::make('shop_id'),
                Tables\Columns\TextColumn::make('shop_name'),
                Tables\Columns\TextColumn::make('username'),
                Tables\Columns\TextColumn::make('region'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketplaceShops::route('/'),
            'create' => Pages\CreateMarketplaceShop::route('/create'),
            'edit' => Pages\EditMarketplaceShop::route('/{record}/edit'),
        ];
    }
}
