<?php

namespace DuncanMcClean\Cargo\Orders\TimelineEventTypes;

use DuncanMcClean\Cargo\Orders\TimelineEventType;

class OrderRefunded extends TimelineEventType
{
    public function message(): string
    {
        $amount = $this->timelineEvent->metadata('amount');
        $formattedAmount = number_format($amount / 100, 2);

        return __('cargo::messages.timeline_events.order_refunded', ['amount' => $formattedAmount]);
    }
}
