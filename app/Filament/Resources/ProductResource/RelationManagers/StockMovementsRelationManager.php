<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMovements';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('product_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_id')
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn($record) => match ($record->type) {
                        'in' => 'success',
                        'out' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('qty')->label('Qty'),
                Tables\Columns\TextColumn::make('source_type')->label('Sumber'),
                Tables\Columns\TextColumn::make('note')->limit(40),
                Tables\Columns\TextColumn::make('moved_at')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y H:i')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
