<?php

namespace DuncanMcClean\Cargo\Widgets;

use Carbon\Carbon;
use DuncanMcClean\Cargo\Facades\Order;
use Statamic\Facades\Site;
use Statamic\Widgets\VueComponent;
use Statamic\Widgets\Widget;

class NewCustomers extends Widget
{
    use Concerns\CalculatesStatistics;

    public function component()
    {
        $days = $this->config('days', 30);

        $newCustomers = Order::query()
            ->where('site', Site::selected()->handle())
            ->where('date', '>=', Carbon::now()->subDays($days)->startOfDay())
            ->where('new_customer', true)
            ->count();

        $newCustomersLastPeriod = Order::query()
            ->where('site', Site::selected()->handle())
            ->where('date', '<', Carbon::now()->subDays($days)->startOfDay())
            ->where('date', '>=', Carbon::now()->subDays($days * 2)->endOfDay())
            ->where('new_customer', true)
            ->count();

        [$trend, $trendDirection] = $this->calculateTrend($newCustomers, $newCustomersLastPeriod);

        return VueComponent::render('statistic-widget', [
            'title' => __('New Customers past :days days', ['days' => $days]),
            'value' => $newCustomers,
            'trend' => $trend,
            'trendDirection' => $trendDirection,
        ]);
    }
}
