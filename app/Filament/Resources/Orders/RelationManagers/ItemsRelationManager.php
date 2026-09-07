<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Order items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->wrap()->limit(60),
                TextColumn::make('sku')->label('SKU'),
                TextColumn::make('qty'),
                TextColumn::make('unit_price')->label('Unit (net)')->money('KES'),
                TextColumn::make('cost_price_snapshot')->label('Cost')->money('KES')->toggleable(),
                TextColumn::make('line_total')->label('Line total')->money('KES'),
            ])
            ->paginated(false);
    }
}
