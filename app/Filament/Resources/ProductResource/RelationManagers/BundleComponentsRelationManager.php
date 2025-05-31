<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class BundleComponentsRelationManager extends RelationManager
{
    protected static string $relationship = 'bundleComponents'; // SESUAI di Product.php

    protected static ?string $title = 'Bundle Components';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
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

    public function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('component.name')
                ->label('Component Product')
                ->searchable(),
            Tables\Columns\TextColumn::make('qty')
                ->label('Qty')
                ->sortable(),
        ]);
    }
}
