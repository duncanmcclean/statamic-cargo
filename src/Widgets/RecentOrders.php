<?php

namespace DuncanMcClean\Cargo\Widgets;

use DuncanMcClean\Cargo\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\Cargo\Facades\Order;
use Illuminate\Support\Carbon;
use Statamic\Facades\User;
use Statamic\Widgets\VueComponent;
use Statamic\Widgets\Widget;

class RecentOrders extends Widget
{
    public function component()
    {
        if (! User::current()->can('index', OrderContract::class)) {
            return;
        }

        $columns = Order::blueprint()
            ->columns()
            ->only($this->config('fields', []))
            ->map(fn ($column) => $column->sortable(false)->visible(true))
            ->values();

        return VueComponent::render('recent-orders-widget', [
            'title' => $this->config('title', __('Recent Orders')),
            'additionalColumns' => $columns,
            'initialPerPage' => $this->config('limit', 5),
            'listingUrl' => cp_route('cargo.orders.index'),
            'ordersSince' => Carbon::now()->subDays($this->config('days', 30))->startOfDay()->toIso8601String(),
        ]);
    }
}
