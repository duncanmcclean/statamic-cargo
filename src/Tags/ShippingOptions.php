<?php

namespace DuncanMcClean\Cargo\Tags;

use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\ShippingMethod;
use Statamic\Facades\Blink;
use Statamic\Tags\Tags;

class ShippingOptions extends Tags
{
    const BLINK_KEY = 'shipping-options-loop';

    public function index()
    {
        $cart = Cart::current();

        if (! Blink::has(self::BLINK_KEY)) {
            $shippingOptions = ShippingMethod::all()
                ->flatMap->options($cart)
                ->filter()
                ->map->toAugmentedArray()
                ->values()
                ->all();

            Blink::put(self::BLINK_KEY, $shippingOptions);
        }

        return Blink::get(self::BLINK_KEY);
    }
}
