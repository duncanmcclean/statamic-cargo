<?php

namespace DuncanMcClean\Cargo\Discounts\Types;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Orders\LineItem;

class FreeShipping extends DiscountType
{
    protected static $title = 'Free Shipping';

    public function calculate(Cart $cart, LineItem $lineItem): int
    {
        return 0;
    }

    public function calculateShipping(Cart $cart): int
    {
        return $cart->shippingTotal();
    }
}
