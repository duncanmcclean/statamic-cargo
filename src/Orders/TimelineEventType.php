<?php

namespace DuncanMcClean\Cargo\Orders;

use DuncanMcClean\Cargo\Contracts\Orders\Order;
use Statamic\Extend\HasHandle;
use Statamic\Extend\RegistersItself;

abstract class TimelineEventType
{
    use HasHandle, RegistersItself;

    protected TimelineEvent $timelineEvent;

    public function setTimelineEvent(TimelineEvent $timelineEvent): self
    {
        $this->timelineEvent = $timelineEvent;

        return $this;
    }

    abstract public function message(): string;
}
