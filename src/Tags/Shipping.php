<?php

namespace DuncanMcClean\Cargo\Tags;

use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\ShippingMethod;
use Statamic\Tags\Tags;

class Shipping extends Tags
{
    public function options()
    {
        $cart = Cart::current();

        return ShippingMethod::all()
            ->flatMap->options($cart)
            ->filter()
            ->map->toAugmentedArray()
            ->values()
            ->all();
    }
}
