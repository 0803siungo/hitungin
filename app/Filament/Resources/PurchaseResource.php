<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('invoice_number')
                    ->maxLength(191),
                Forms\Components\DatePicker::make('purchase_date')
                    ->default(now())
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->rows(3),
                Forms\Components\HasManyRepeater::make('items')
                    ->relationship('items')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Product'),
                        Forms\Components\TextInput::make('qty')
                            ->numeric()
                            ->default(1)
                            ->reactive()
                            ->required()
                            ->label('Qty')
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $buyPrice = (float) $get('buy_price') ?: 0;
                                $set('subtotal', $state * $buyPrice);
                            }),
                        Forms\Components\TextInput::make('buy_price')
                            ->numeric()
                            ->reactive()
                            ->required()
                            ->label('Buy Price')
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $qty = (int) $get('qty') ?: 0;
                                $set('subtotal', $qty * $state);
                            }),
                        Forms\Components\Placeholder::make('subtotal')
                            ->label('Subtotal')
                            ->content(function ($get) {
                                $qty = (int) $get('qty') ?: 0;
                                $price = (float) $get('buy_price') ?: 0;
                                $subtotal = $qty * $price;

                                return 'Rp ' . number_format($subtotal, 0, ',', '.');
                            }),
                        Forms\Components\Hidden::make('subtotal')
                            ->required(),
                    ])
                    ->label('Purchase Items')
                    ->defaultItems(1)
                    ->createItemButtonLabel('Add Product'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supplier.name')->label('Supplier'),
                Tables\Columns\TextColumn::make('invoice_number'),
                Tables\Columns\TextColumn::make('purchase_date')->date(),
                Tables\Columns\TextColumn::make('items_count')->counts('items')->label('Items'),
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
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
