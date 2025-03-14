<?php

namespace DuncanMcClean\Cargo\Shipping;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Orders\Order;
use DuncanMcClean\Cargo\Support\Money;
use Illuminate\Support\Collection;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;

abstract class ShippingMethod
{
    use HasHandle, HasTitle, RegistersItself;

    public function logo(): ?string
    {
        return null;
    }

    abstract public function options(Cart $cart): Collection;

    public function fieldtypeDetails(Order $order): array
    {
        return array_filter([
            __('Amount') => Money::format($order->shippingTotal(), $order->site()),
            __('Tracking Number') => $order->get('tracking_number'),
        ]);
    }
}
