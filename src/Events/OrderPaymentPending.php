<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Orders\Order;
use Statamic\Events\Event;

class OrderPaymentPending extends Event
{
    public function __construct(public Order $order) {}
}
