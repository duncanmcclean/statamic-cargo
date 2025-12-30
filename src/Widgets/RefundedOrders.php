<?php

namespace DuncanMcClean\Cargo\Widgets;

use Carbon\Carbon;
use DuncanMcClean\Cargo\Facades\Order;
use Statamic\Facades\Site;
use Statamic\Widgets\VueComponent;
use Statamic\Widgets\Widget;

class RefundedOrders extends Widget
{
    use Concerns\CalculatesStatistics;

    public function component()
    {
        $days = $this->config('days', 30);

        $refundedOrders = Order::query()
            ->where('site', Site::selected()->handle())
            ->where('date', '>=', Carbon::now()->subDays($days)->startOfDay())
            ->whereNotNull('amount_refunded')
            ->count();

        $refundedOrdersLastPeriod = Order::query()
            ->where('site', Site::selected()->handle())
            ->where('date', '<', Carbon::now()->subDays($days)->startOfDay())
            ->where('date', '>=', Carbon::now()->subDays($days * 2)->endOfDay())
            ->whereNotNull('amount_refunded')
            ->count();

        [$trend, $trendDirection] = $this->calculateTrend($refundedOrders, $refundedOrdersLastPeriod);

        return VueComponent::render('statistic-widget', [
            'title' => __('Refunded Orders past :days days', ['days' => $days]),
            'value' => $refundedOrders,
            'trend' => $trend,
            'trendDirection' => $trendDirection === 'down' ? 'up' : 'down',
        ]);
    }
}
