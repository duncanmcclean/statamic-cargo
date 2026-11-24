<?php

namespace DuncanMcClean\Cargo\Orders\TimelineEventTypes;

use DuncanMcClean\Cargo\Orders\TimelineEventType;
use DuncanMcClean\Cargo\Support\Money;

class OrderRefunded extends TimelineEventType
{
    public function message(): string
    {
        $amount = Money::format(
            $this->timelineEvent->metadata('Amount'),
            $this->timelineEvent->order()->site()
        );

        return __('cargo::messages.timeline_events.order_refunded', ['amount' => $amount]);
    }
}
