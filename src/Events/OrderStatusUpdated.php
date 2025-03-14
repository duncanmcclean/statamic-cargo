<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Orders\Order;
use DuncanMcClean\Cargo\Orders\OrderStatus;

class OrderStatusUpdated
{
    public function __construct(
        public Order $order,
        public ?OrderStatus $oldStatus,
        public OrderStatus $newStatus
    ) {}
}
