<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BundleDetailRelationManager extends RelationManager
{
    protected static string $relationship = 'bundleDetails';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('component_product_id')
                    ->relationship('component', 'name')
                    ->label('Component Product')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('qty')
                    ->label('Qty')
                    ->numeric()
                    ->default(1)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Bundle Components')
            ->columns([
                Tables\Columns\TextColumn::make('component.name')
                    ->label('Component Product')
                    ->searchable(),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
