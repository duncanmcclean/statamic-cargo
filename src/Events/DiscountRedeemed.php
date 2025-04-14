<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Discounts\Discount;
use DuncanMcClean\Cargo\Contracts\Orders\Order;
use Statamic\Events\Event;

class DiscountRedeemed extends Event
{
    public function __construct(
        public Discount $discount,
        public Order $order
    ) {}
}
