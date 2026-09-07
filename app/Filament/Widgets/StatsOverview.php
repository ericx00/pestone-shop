<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $paid = Order::where('payment_status', 'paid');

        $today = (int) (clone $paid)->whereDate('created_at', today())->sum('grand_total');
        $week = (int) (clone $paid)->where('created_at', '>=', now()->subDays(7))->sum('grand_total');
        $month = (int) (clone $paid)->where('created_at', '>=', now()->subDays(30))->sum('grand_total');

        $openOrders = Order::whereIn('status', ['pending', 'awaiting_payment', 'paid', 'processing'])->count();
        $lowStock = Product::where('is_active', true)
            ->where('stock_status', '!=', 'on_request')
            ->where('stock_qty', '<=', (int) setting('low_stock_threshold', 3))
            ->count();
        $inventoryValue = (int) Product::where('stock_status', '!=', 'on_request')
            ->selectRaw('SUM(stock_qty * cost_price) as v')->value('v');

        return [
            Stat::make('Revenue today', 'KES '.number_format($today))
                ->description('Paid orders')
                ->color('success'),
            Stat::make('Revenue (7 days)', 'KES '.number_format($week))
                ->color('success'),
            Stat::make('Revenue (30 days)', 'KES '.number_format($month))
                ->color('success'),
            Stat::make('Open orders', (string) $openOrders)
                ->description('Awaiting payment or fulfilment')
                ->color($openOrders > 0 ? 'warning' : 'gray'),
            Stat::make('Customers', (string) User::where('is_admin', false)->count())
                ->description(User::where('b2b_status', 'pending')->count().' B2B pending'),
            Stat::make('Low stock lines', (string) $lowStock)
                ->description('Inventory value KES '.number_format($inventoryValue))
                ->color($lowStock > 0 ? 'danger' : 'gray'),
        ];
    }
}
