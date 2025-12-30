<?php

namespace DuncanMcClean\Cargo\Widgets;

use Carbon\Carbon;
use DuncanMcClean\Cargo\Facades\Order;
use Statamic\Facades\Site;
use Statamic\Widgets\VueComponent;
use Statamic\Widgets\Widget;

class TotalSales extends Widget
{
    use Concerns\CalculatesStatistics;

    public function component()
    {
        $days = $this->config('days', 30);

        $totalSales = Order::query()
            ->where('site', Site::selected()->handle())
            ->where('date', '>=', Carbon::now()->subDays($days)->startOfDay())
            ->count();

        $totalSalesLastPeriod = Order::query()
            ->where('site', Site::selected()->handle())
            ->where('date', '<', Carbon::now()->subDays($days)->startOfDay())
            ->where('date', '>=', Carbon::now()->subDays($days * 2)->endOfDay())
            ->count();

        [$trend, $trendDirection] = $this->calculateTrend($totalSales, $totalSalesLastPeriod);

        return VueComponent::render('statistic-widget', [
            'title' => __('Total Sales past :days days', ['days' => $days]),
            'value' => $totalSales,
            'trend' => $trend,
            'trendDirection' => $trendDirection,
        ]);
    }
}