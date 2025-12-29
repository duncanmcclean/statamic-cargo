<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use Statamic\Fields\Fieldtype;

class OrderTimeline extends Fieldtype
{
    public function preProcess($data)
    {
        $order = $this->field->parent();

        $events = collect([
            [
                'id' => 1, // todo: don't need to store this, but we do need to generate it
                'title' => __('Order Created'),
                'user' => $order->customer(),
                'date' => $order->date(),
            ],
            [
                'id' => 2,
                'title' => __('Order Status Updated'),
                'user' => \Statamic\Facades\User::current(),
                'date' => now()->subDay()->setTime(9, 14, 25),
                'metadata' => [
                    'Original Status' => 'Pending',
                    'New Status' => 'Processing',
                ],
            ],
            [
                'id' => 3,
                'title' => __('Order Updated'),
                'user' => \Statamic\Facades\User::current(),
                'date' => now()->subDay()->setTime(12, 18, 10),
            ],
            [
                'id' => 4,
                'title' => __('Order Refunded'),
                'user' => \Statamic\Facades\User::current(),
                'date' => now(),
                'metadata' => [
                    'Amount' => '£13.00',
                ],
            ],
        ]);

        return $events
            ->groupBy(function ($event) {
                return $event['date']->clone()->startOfDay()->format('U');
            })
            ->map(function ($events, $day) {
                return compact('day', 'events');
            })
            ->reverse()
            ->values();
    }

    public function process($data)
    {
        return null;
    }
}