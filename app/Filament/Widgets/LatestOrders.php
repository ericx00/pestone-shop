<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestOrders extends TableWidget
{
    protected static ?string $heading = 'Latest orders';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Order::query()->latest()->limit(10))
            ->paginated(false)
            ->columns([
                TextColumn::make('number')->label('Order')->weight('bold'),
                TextColumn::make('customer_name')->label('Customer')
                    ->description(fn (Order $r) => $r->customer_phone),
                TextColumn::make('grand_total')->money('KES'),
                TextColumn::make('payment_status')->badge()->colors([
                    'success' => 'paid', 'warning' => 'pending', 'danger' => 'failed', 'gray' => 'unpaid',
                ]),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),
                TextColumn::make('created_at')->since()->label('When'),
            ])
            ->recordUrl(fn (Order $record) => \App\Filament\Resources\Orders\OrderResource::getUrl('edit', ['record' => $record]));
    }
}
