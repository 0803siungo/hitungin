<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnResource\Pages;
use App\Filament\Resources\ReturnResource\RelationManagers;
use App\Models\Returns;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReturnResource extends Resource
{
    protected static ?string $model = Returns::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_number')->label('Order #')->disabled(),
                Forms\Components\TextInput::make('marketplace')->disabled(),
                Forms\Components\TextInput::make('sku')->label('SKU')->disabled(),
                Forms\Components\TextInput::make('qty')->disabled(),
                Forms\Components\TextInput::make('price')->money('IDR', true)->disabled(),
                Forms\Components\TextInput::make('buyer_username')->label('Buyer')->disabled(),
                Forms\Components\DateTimePicker::make('returned_at')->label('Returned At')->disabled(),
                Forms\Components\Textarea::make('reason')->disabled(),
                Forms\Components\Textarea::make('note')->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'completed' => 'Completed',
                        'pending' => 'Pending',
                        'rejected' => 'Rejected',
                    ])
                    ->disabled(),
                Forms\Components\KeyValue::make('raw_data')->disabled(),
                Forms\Components\BelongsToSelect::make('product_id')
                    ->relationship('product', 'name')
                    ->disabled(),
                Forms\Components\BelongsToSelect::make('sale_id')
                    ->relationship('sale', 'order_number')
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
                Tables\Columns\TextColumn::make('returned_at')->label('Returned At')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Imported At')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListReturns::route('/'),
            // 'create' => Pages\CreateReturn::route('/create'),
            // 'edit' => Pages\EditReturn::route('/{record}/edit'),
        ];
    }
}
