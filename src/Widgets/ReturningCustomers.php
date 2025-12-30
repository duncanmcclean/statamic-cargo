<?php

namespace DuncanMcClean\Cargo\Widgets;

use Carbon\Carbon;
use DuncanMcClean\Cargo\Facades\Order;
use Statamic\Facades\Site;
use Statamic\Widgets\VueComponent;
use Statamic\Widgets\Widget;

class ReturningCustomers extends Widget
{
    use Concerns\CalculatesStatistics;

    public function component()
    {
        $days = $this->config('days', 30);

        $returningCustomers = Order::query()
            ->where('site', Site::selected()->handle())
            ->where('date', '>=', Carbon::now()->subDays($days)->startOfDay())
            ->whereNull('new_customer')
            ->count();

        $returningCustomersLastPeriod = Order::query()
            ->where('site', Site::selected()->handle())
            ->where('date', '<', Carbon::now()->subDays($days)->startOfDay())
            ->where('date', '>=', Carbon::now()->subDays($days * 2)->endOfDay())
            ->whereNull('new_customer')
            ->count();

        [$trend, $trendDirection] = $this->calculateTrend($returningCustomers, $returningCustomersLastPeriod);

        return VueComponent::render('statistic-widget', [
            'title' => __('Returning Customers past :days days', ['days' => $days]),
            'value' => $returningCustomers,
            'trend' => $trend,
            'trendDirection' => $trendDirection,
        ]);
    }
}
