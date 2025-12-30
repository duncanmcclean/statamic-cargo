<?php

namespace DuncanMcClean\Cargo\Subscribers;

use DuncanMcClean\Cargo\Events\OrderCreated;
use DuncanMcClean\Cargo\Events\OrderRefunded;
use DuncanMcClean\Cargo\Events\OrderSaved;
use DuncanMcClean\Cargo\Events\OrderStatusUpdated;
use DuncanMcClean\Cargo\Orders\TimelineEventTypes;
use Statamic\Events\Subscriber;

class RecordTimelineEvents extends Subscriber
{
    protected $listeners = [
        OrderCreated::class => 'handleOrderCreated',
        OrderSaved::class => 'handleOrderSaved',
        OrderStatusUpdated::class => 'handleOrderStatusUpdated',
        OrderRefunded::class => 'handleOrderRefunded',
    ];

    public function handleOrderCreated(OrderCreated $event): void
    {
        $event->order->appendTimelineEvent(TimelineEventTypes\OrderCreated::class);
    }

    public function handleOrderSaved(OrderSaved $event): void
    {
        if (empty($event->order->getOriginal())) {
            return;
        }

        $updatedAttributes = collect($event->order->getCurrentDirtyStateAttributes())
            ->filter(fn ($value, $key) => $event->order->isDirty($key))
            ->map(fn ($value, $key) => is_array($value) || is_object($value) ? json_encode($value) : $value)
            ->except('status');

        if ($updatedAttributes->isNotEmpty()) {
            $event->order->appendTimelineEvent(TimelineEventTypes\OrderUpdated::class, $updatedAttributes->toArray());
        }
    }

    public function handleOrderStatusUpdated(OrderStatusUpdated $event): void
    {
        $event->order->appendTimelineEvent(TimelineEventTypes\OrderStatusChanged::class, [
            'Original Status' => $event->originalStatus?->name,
            'New Status' => $event->updatedStatus->name,
        ]);
    }

    public function handleOrderRefunded(OrderRefunded $event): void
    {
        $event->order->appendTimelineEvent(TimelineEventTypes\OrderRefunded::class, [
            'Amount' => $event->amount,
        ]);
    }
}
