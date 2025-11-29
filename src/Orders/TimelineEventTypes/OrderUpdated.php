<?php

namespace DuncanMcClean\Cargo\Orders\TimelineEventTypes;

use DuncanMcClean\Cargo\Orders\TimelineEventType;

class OrderUpdated extends TimelineEventType
{
    public function message(): string
    {
        return 'Order was updated';
    }
}
