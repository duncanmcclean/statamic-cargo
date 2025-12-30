<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use DuncanMcClean\Cargo\Cargo;
use DuncanMcClean\Cargo\Orders\TimelineEvent;
use Statamic\Fields\Fieldtype;

class OrderTimeline extends Fieldtype
{
    protected $selectable = false;

    public function preload()
    {
        return [
            'cargoMark' => Cargo::svg('cargo-mark'),
        ];
    }

    public function preProcess($data)
    {
        $order = $this->field->parent();

        return $order->timelineEvents()
            ->map(function (TimelineEvent $timelineEvent, $index) {
                return [
                    'id' => $index,
                    'message' => $timelineEvent->message(),
                    'datetime' => $timelineEvent->datetime(),
                    'timestamp' => $timelineEvent->datetime()->timestamp,
                    'user' => $timelineEvent->user(),
                    'metadata' => $timelineEvent->metadata()->all(),
                ];
            })
            ->groupBy(function ($event) {
                return $event['datetime']->startOfDay()->format('U');
            })
            ->map(fn ($events, $day) => compact('day', 'events'))
            ->reverse()
            ->values();
    }

    public function process($data)
    {
        return null;
    }
}
