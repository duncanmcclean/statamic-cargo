<?php

namespace Tests\Fixtures\ShippingMethods;

use DuncanMcClean\Cargo\Contracts\Cart\Cart as CartContract;
use DuncanMcClean\Cargo\Shipping\ShippingMethod;
use DuncanMcClean\Cargo\Shipping\ShippingOption;
use Illuminate\Support\Collection;

class DynamicShipping extends ShippingMethod
{
    public function options(CartContract $cart): Collection
    {
        // Price depends on the current cart contents (100 per line item).
        $price = 100 * max(1, $cart->lineItems()->count());

        return collect([
            ShippingOption::make($this)
                ->name('Dynamic Option')
                ->handle('dynamic_option')
                ->price($price),
        ]);
    }
}
