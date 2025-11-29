<?php

namespace DuncanMcClean\Cargo\Orders\TimelineEventTypes;

use DuncanMcClean\Cargo\Orders\TimelineEventType;

class OrderCreated extends TimelineEventType
{
    public function message(): string
    {
        return 'Order was created';
    }
}
