<?php

namespace DuncanMcClean\Cargo\Http\Controllers;

use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\ShippingMethod;

class CartShippingController
{
    public function __invoke()
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
