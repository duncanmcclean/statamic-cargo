<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Orders\Order;
use Statamic\Events\Event;

class OrderShipped extends Event
{
    public function __construct(public Order $order) {}
}
