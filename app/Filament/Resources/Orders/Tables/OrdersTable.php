<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('Order')->searchable()->weight('bold')
                    ->description(fn (Order $r) => $r->placed_at?->format('d M Y, H:i')),
                TextColumn::make('customer_name')->searchable()
                    ->description(fn (Order $r) => $r->customer_phone),
                TextColumn::make('channel')->badge()
                    ->colors(['info' => 'b2b', 'gray' => 'b2c']),
                TextColumn::make('items_count')->counts('items')->label('Items'),
                TextColumn::make('grand_total')->money('KES')->sortable(),
                TextColumn::make('margin')->label('Margin')
                    ->state(fn (Order $r) => 'KES '.number_format($r->marginTotal()))
                    ->toggleable(),
                TextColumn::make('payment_status')->badge()->colors([
                    'success' => 'paid', 'warning' => 'pending', 'danger' => 'failed', 'gray' => 'unpaid',
                ]),
                TextColumn::make('status')->badge()->colors([
                    'gray' => 'pending',
                    'warning' => fn ($state) => in_array($state, ['awaiting_payment', 'processing']),
                    'success' => fn ($state) => in_array($state, ['paid', 'shipped', 'completed']),
                    'danger' => fn ($state) => in_array($state, ['cancelled', 'refunded']),
                ])->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),
                TextColumn::make('created_at')->dateTime('d M H:i')->sortable()->label('Placed'),
            ])
            ->filters([
                SelectFilter::make('status')->options(collect(Order::STATUSES)
                    ->mapWithKeys(fn ($s) => [$s => ucfirst(str_replace('_', ' ', $s))])),
                SelectFilter::make('payment_status')->options([
                    'unpaid' => 'Unpaid', 'pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed',
                ]),
                SelectFilter::make('channel')->options(['b2c' => 'B2C', 'b2b' => 'B2B']),
                Filter::make('paid_only')->label('Paid orders')
                    ->query(fn (Builder $q) => $q->where('payment_status', 'paid')),
            ])
            ->recordActions([EditAction::make()])
            ->defaultSort('id', 'desc');
    }
}
