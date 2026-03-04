<?php

namespace App\Filament\Widgets;

use App\Models\Income;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class WeeklyRevenueChart extends ChartWidget
{
    protected string $heading = 'Weekly Revenue';
    
    protected int|string|array $columnSpan = 2;

    protected function getData(): array
    {
        $data = [];
        $labels = [];
        
        // Get last 7 days of data
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('M j');
            
            // Get revenue from both orders and income
            $orderRevenue = Order::whereDate('created_at', $date)
                ->where('status', '!=', 'cancelled')
                ->sum('total');
                
            $incomeRevenue = Income::whereDate('date', $date)
                ->sum('amount');
            
            // Use the higher of the two (to avoid double counting)
            $data[] = max($orderRevenue, $incomeRevenue);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.5)',
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "$" + value.toFixed(2); }',
                    ],
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return "Revenue: $" + context.parsed.y.toFixed(2); }',
                    ],
                ],
            ],
        ];
    }
}