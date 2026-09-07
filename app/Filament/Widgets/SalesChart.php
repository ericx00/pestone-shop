<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Sales — last 30 days (paid orders)';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();

        $rows = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d, SUM(grand_total) as total, COUNT(*) as orders')
            ->groupBy('d')->pluck('total', 'd');

        $labels = [];
        $values = [];
        for ($i = 0; $i < 30; $i++) {
            $day = $start->copy()->addDays($i);
            $labels[] = $day->format('d M');
            $values[] = (int) ($rows[$day->format('Y-m-d')] ?? 0);
        }

        return [
            'datasets' => [[
                'label' => 'Revenue (KES)',
                'data' => $values,
                'borderColor' => '#1a9bd8',
                'backgroundColor' => 'rgba(26,155,216,0.15)',
                'fill' => true,
                'tension' => 0.3,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
