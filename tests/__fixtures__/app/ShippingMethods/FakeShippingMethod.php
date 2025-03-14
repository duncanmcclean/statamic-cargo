<?php

namespace Tests\Fixtures\ShippingMethods;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Shipping\ShippingMethod;
use DuncanMcClean\Cargo\Shipping\ShippingOption;
use Illuminate\Support\Collection;

class FakeShippingMethod extends ShippingMethod
{
    public function options(Cart $cart): Collection
    {
        return collect([
            ShippingOption::make($this)
                ->name('In-Store Pickup')
                ->price(0),

            ShippingOption::make($this)
                ->name('Standard Shipping')
                ->price(500),

            ShippingOption::make($this)
                ->name('Express Shipping')
                ->price(1000),
        ]);
    }
}
