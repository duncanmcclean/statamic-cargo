<?php

namespace DuncanMcClean\Cargo\Orders\TimelineEventTypes;

use DuncanMcClean\Cargo\Orders\OrderStatus;
use DuncanMcClean\Cargo\Orders\TimelineEventType;

class OrderStatusChanged extends TimelineEventType
{
    public function message(): string
    {
        $newStatus = $this->timelineEvent->metadata('new');
        $originalStatus = $this->timelineEvent->metadata('original');

        $newStatusLabel = OrderStatus::label(OrderStatus::from($newStatus));

        if ($originalStatus) {
            $originalStatusLabel = OrderStatus::label(OrderStatus::from($originalStatus));

            return __('cargo::messages.timeline_events.order_status_changed_from_to', [
                'original' => $originalStatusLabel,
                'new' => $newStatusLabel,
            ]);
        }

        return __('cargo::messages.timeline_events.order_status_changed_to', ['status' => $newStatusLabel]);
    }
}
