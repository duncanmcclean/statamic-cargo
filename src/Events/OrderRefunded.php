<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Orders\Order;

class OrderRefunded
{
    public function __construct(public Order $order, public int $amount) {}
}
