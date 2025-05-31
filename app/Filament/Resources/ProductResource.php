<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Products';
    protected static ?string $pluralLabel = 'Products';
    protected static ?string $modelLabel = 'Product';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sku')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Toggle::make('is_bundle')
                    ->label('Is this a bundle?'),
                Forms\Components\TextInput::make('stock')
                    ->numeric()
                    ->visible(fn($get) => !$get('is_bundle')),
                Forms\Components\TextInput::make('price')
                    ->numeric(),
                Forms\Components\Textarea::make('description')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\BooleanColumn::make('is_bundle')->label('Bundle?'),
                Tables\Columns\BadgeColumn::make('bundle_status')
                    ->label('Bundle Status')
                    ->getStateUsing(function ($record) {
                        if (!$record->is_bundle) {
                            return null;
                        }
                        return $record->bundleComponents()->count() > 0 ? 'Complete' : 'Missing';
                    })
                    ->colors([
                        'danger' => 'Missing',
                        'success' => 'Complete',
                    ])
                    ->icons([
                        'heroicon-o-x-circle' => 'Missing',
                        'heroicon-o-check-circle' => 'Complete',
                    ]),
                Tables\Columns\TextColumn::make('calculated_stock')
                    ->label('Stock')
                    ->sortable(),
                Tables\Columns\TextColumn::make('calculated_estimated_buy_price')
                    ->label('Modal / Est. Price')
                    ->formatStateUsing(fn($state) => $state ? 'Rp ' . number_format($state, 0, ',', '.') : '-')
                    ->money('IDR')
                    ->sortable(),
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
                // ProductResource\RelationManagers\BundleComponentsRelationManager::class,
            ProductResource\RelationManagers\StockMovementsRelationManager::class,
            ProductResource\RelationManagers\BundleDetailRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
