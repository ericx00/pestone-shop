<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStock extends TableWidget
{
    protected static ?string $heading = 'Low stock';

    public function table(Table $table): Table
    {
        $threshold = (int) setting('low_stock_threshold', 3);

        return $table
            ->query(fn (): Builder => Product::query()
                ->where('is_active', true)
                ->where('stock_status', '!=', 'on_request')
                ->where('stock_qty', '<=', $threshold)
                ->orderBy('stock_qty'))
            ->paginated([10])
            ->columns([
                TextColumn::make('name')->limit(40)->wrap()
                    ->description(fn (Product $r) => $r->sku),
                TextColumn::make('stock_qty')->label('Qty')->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'danger'),
                TextColumn::make('brand.name')->label('Brand'),
            ])
            ->recordUrl(fn (Product $record) => \App\Filament\Resources\Products\ProductResource::getUrl('edit', ['record' => $record]));
    }
}
