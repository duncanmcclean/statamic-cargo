<?php

namespace DuncanMcClean\Cargo\Orders;

use Statamic\Providers\AddonServiceProvider;

class OrderServiceProvider extends AddonServiceProvider
{
    protected array $timelineEventTypes = [
        TimelineEventTypes\OrderCreated::class,
        TimelineEventTypes\OrderRefunded::class,
        TimelineEventTypes\OrderStatusChanged::class,
        TimelineEventTypes\OrderUpdated::class,
    ];

    public function bootAddon()
    {
        foreach ($this->timelineEventTypes as $timelineEventType) {
            $timelineEventType::register();
        }
    }
}