<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';

    protected static ?string $navigationLabel = 'Sales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_number')->disabled(),
                Forms\Components\TextInput::make('marketplace')->disabled(),
                Forms\Components\TextInput::make('sku')->disabled(),
                Forms\Components\TextInput::make('qty')->disabled(),
                Forms\Components\TextInput::make('price')->disabled(),
                Forms\Components\TextInput::make('buyer_username')->disabled(),
                Forms\Components\DateTimePicker::make('sold_at')->disabled(),
                Forms\Components\KeyValue::make('raw_data')->disabled(),
                Forms\Components\BelongsToSelect::make('product_id')
                    ->relationship('product', 'name')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->label('Order #')->searchable(),
                Tables\Columns\TextColumn::make('marketplace')->badge()->sortable(),
                Tables\Columns\TextColumn::make('sku')->label('SKU')->searchable(),
                Tables\Columns\TextColumn::make('product.name')->label('Product')->searchable(),
                Tables\Columns\TextColumn::make('qty')->sortable(),
                Tables\Columns\TextColumn::make('price')->money('IDR', true)->sortable(),
                Tables\Columns\TextColumn::make('buyer_username')->label('Buyer')->searchable(),
                Tables\Columns\TextColumn::make('sold_at')->label('Sold At')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Imported At')->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('sold_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('marketplace')
                    ->options([
                        'shopee' => 'Shopee',
                        'tokopedia' => 'Tokopedia',
                        'tiktok' => 'TikTok',
                    ]),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSales::route('/'),
            // 'view' => Pages\ViewSale::route('/{record}'),
            // 'create' => Pages\CreateSale::route('/create'),
            // 'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
