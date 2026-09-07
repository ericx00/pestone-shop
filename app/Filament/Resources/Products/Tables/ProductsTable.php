<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->limit(48)->wrap()
                    ->description(fn (Product $r) => $r->sku),
                TextColumn::make('brand.name')->label('Brand')->sortable()->toggleable(),
                TextColumn::make('category.name')->label('Category')->sortable()->toggleable()->limit(24),
                TextColumn::make('cost_price')->label('Cost')->money('KES')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price')->label('Retail')->money('KES')->sortable(),
                TextColumn::make('margin')->label('Margin')
                    ->state(fn (Product $r) => $r->price > 0
                        ? round(($r->price / 1.16 - $r->cost_price) / max(1, $r->price / 1.16) * 100, 1).'%'
                        : '—')
                    ->color(fn (Product $r) => ($r->price / 1.16) <= $r->cost_price ? 'danger' : 'success'),
                IconColumn::make('price_locked')->label('Locked')->boolean()->toggleable(),
                TextColumn::make('stock_qty')->label('Stock')->badge()
                    ->color(fn ($state) => $state > 3 ? 'success' : ($state > 0 ? 'warning' : 'gray'))->sortable(),
                IconColumn::make('is_active')->label('Active')->boolean(),
                IconColumn::make('is_featured')->label('Featured')->boolean()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('brand_id')->label('Brand')->relationship('brand', 'name')->searchable()->preload(),
                SelectFilter::make('category_id')->label('Category')->relationship('category', 'name')->searchable()->preload(),
                SelectFilter::make('stock_status')->options([
                    'in_stock' => 'In stock', 'low' => 'Low', 'out' => 'Out', 'on_request' => 'On request',
                ]),
                TernaryFilter::make('is_active')->label('Active'),
                TernaryFilter::make('is_featured')->label('Featured'),
                Filter::make('negative_margin')->label('Losing money (retail ≤ cost)')
                    ->query(fn ($q) => $q->whereRaw('price / 1.16 <= cost_price')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('applyMarkup')
                        ->label('Apply markup %')
                        ->icon('heroicon-o-calculator')
                        ->schema([
                            TextInput::make('markup')->numeric()->required()->default(10)->suffix('% on cost'),
                            TextInput::make('round_to')->numeric()->default(10)->label('Round to nearest'),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $vat = 1 + (float) setting('vat_rate', 16) / 100;
                            $round = max(1, (int) ($data['round_to'] ?? 10));
                            foreach ($records as $product) {
                                $new = round($product->cost_price * (1 + $data['markup'] / 100) * $vat / $round) * $round;
                                $product->forceFill(['price' => (int) $new, 'price_locked' => true])->save();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('activate')->label('Activate')->icon('heroicon-o-eye')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')->label('Deactivate')->icon('heroicon-o-eye-slash')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('feature')->label('Feature on home')->icon('heroicon-o-star')
                        ->action(fn (Collection $records) => $records->each->update(['is_featured' => true]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
