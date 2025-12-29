<?php

namespace DuncanMcClean\Cargo\Orders\TimelineEventTypes;

use DuncanMcClean\Cargo\Orders\OrderStatus;
use DuncanMcClean\Cargo\Orders\TimelineEventType;

class OrderStatusChanged extends TimelineEventType
{
    public function message(): string
    {
        $newStatus = $this->timelineEvent->metadata('New Status');
        $newStatusLabel = OrderStatus::label(OrderStatus::from($newStatus));

        return __('cargo::messages.timeline_events.order_status_changed', ['status' => $newStatusLabel]);
    }
}
