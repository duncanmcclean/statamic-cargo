<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Orders\Order;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use Statamic\Events\Event;

class OrderStatusUpdated extends Event
{
    public function __construct(
        public Order $order,
        public OrderStatus $originalStatus,
        public OrderStatus $updatedStatus
    ) {}
}
