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

    abstract public function options(Cart $cart): Collection;

    public function logo(): ?string
    {
        return null;
    }

    public function fieldtypeDetails(Order $order): array
    {
        return array_filter([
            __('Amount') => Money::format($order->shippingTotal(), $order->site()),
            __('Tracking Number') => $order->get('tracking_number'),
        ]);
    }

    public function config(): Collection
    {
        return collect(config("statamic.cargo.shipping.methods.{$this->handle()}"));
    }
}
