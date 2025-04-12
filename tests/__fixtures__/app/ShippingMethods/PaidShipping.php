<?php

namespace Tests\Fixtures\ShippingMethods;

use DuncanMcClean\Cargo\Contracts\Cart\Cart as CartContract;
use DuncanMcClean\Cargo\Shipping\ShippingMethod;
use DuncanMcClean\Cargo\Shipping\ShippingOption;
use Illuminate\Support\Collection;

class PaidShipping extends ShippingMethod
{
    public function options(CartContract $cart): Collection
    {
        return collect([
            ShippingOption::make($this)
                ->name('The Only Option')
                ->price(500),
        ]);
    }
}
