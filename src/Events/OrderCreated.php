<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Orders\Order;

class OrderCreated
{
    public function __construct(public Order $order) {}
}
