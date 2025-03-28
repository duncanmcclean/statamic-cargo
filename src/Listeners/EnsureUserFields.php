<?php

namespace DuncanMcClean\Cargo\Listeners;

use Statamic\Events\UserBlueprintFound;

class EnsureUserFields
{
    public function handle(UserBlueprintFound $event)
    {
        if (! $event->blueprint->hasField('orders')) {
            $event->blueprint->ensureField('orders', [
                'type' => 'orders',
                'display' => 'Orders',
                'listable' => false,
                'visibility' => 'computed',
            ]);
        }
    }
}
