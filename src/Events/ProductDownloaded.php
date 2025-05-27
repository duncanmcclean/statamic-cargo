<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Orders\Order;
use DuncanMcClean\Cargo\Orders\LineItem;
use Statamic\Events\Event;

class ProductDownloaded extends Event
{
    public function __construct(public Order $order, public LineItem $lineItem) {}
}