<?php

namespace DuncanMcClean\Cargo\Shipping;

use DuncanMcClean\Cargo\Cargo;
use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use Illuminate\Support\Collection;

class FreeShipping extends ShippingMethod
{
    protected static $title = 'Free Shipping';

    public function options(Cart $cart): Collection
    {
        return collect([
            ShippingOption::make($this)
                ->name(__('Free Shipping'))
                ->price(0),
        ]);
    }

    public function logo(): ?string
    {
        return Cargo::svg('cargo-mark');
    }
}
