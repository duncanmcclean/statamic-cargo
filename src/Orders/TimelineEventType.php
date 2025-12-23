<?php

namespace DuncanMcClean\Cargo\Orders;

use Illuminate\Support\Str;

abstract class TimelineEventType
{
    public function __construct(protected TimelineEvent $event, protected Order $order)
    {
    }

    public static function make(TimelineEvent $event, Order $order): static
    {
        return new static($event, $order);
    }

    public static function handle(): string
    {
        return Str::snake(class_basename(static::class));
    }

    abstract public function message(): string;
}
