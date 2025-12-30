<?php

namespace DuncanMcClean\Cargo\Widgets;

use Carbon\Carbon;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Support\Money;
use Statamic\Facades\Site;
use Statamic\Widgets\VueComponent;
use Statamic\Widgets\Widget;

class TotalRevenue extends Widget
{
    use Concerns\CalculatesStatistics;

    public function component()
    {
        $days = $this->config('days', 30);

        $totalRevenue = Order::query()
            ->where('site', Site::selected()->handle())
            ->where('date', '>=', Carbon::now()->subDays($days)->startOfDay())
            ->sum('grand_total');

        $totalRevenueLastPeriod = Order::query()
            ->where('site', Site::selected()->handle())
            ->where('date', '<', Carbon::now()->subDays($days)->startOfDay())
            ->where('date', '>=', Carbon::now()->subDays($days * 2)->endOfDay())
            ->sum('grand_total');

        [$trend, $trendDirection] = $this->calculateTrend($totalRevenue, $totalRevenueLastPeriod);

        return VueComponent::render('statistic-widget', [
            'title' => __('Total Revenue past :days days', ['days' => $days]),
            'value' => Money::format($totalRevenue, Site::selected()),
            'trend' => $trend,
            'trendDirection' => $trendDirection,
        ]);
    }
}
