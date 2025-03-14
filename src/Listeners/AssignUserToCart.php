<?php

namespace DuncanMcClean\Cargo\Listeners;

use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Orders\LineItem;
use Illuminate\Auth\Events\Login;
use Statamic\Facades\User;

class AssignUserToCart
{
    public function handle(Login $event): void
    {
        $user = User::fromUser($event->user);

        $recentCart = Cart::query()
            ->where('customer', $user->id())
            ->when(Cart::hasCurrentCart(), fn ($query) => $query->where('id', '!=', Cart::current()->id()))
            ->first();

        if (! $recentCart) {
            Cart::current()->customer(User::fromUser($event->user))->save();

            return;
        }

        if (! Cart::hasCurrentCart()) {
            Cart::setCurrent($recentCart);

            return;
        }

        $shouldMerge = config('statamic.cargo.carts.merge_on_login', true);

        if ($shouldMerge) {
            $currentCart = Cart::current();

            $recentCart->merge($currentCart);
            $currentCart->lineItems()->each(function (LineItem $lineItem) use ($recentCart) {
                $recentCart->lineItems()->create($lineItem->fileData());
            });

            Cart::setCurrent($recentCart);
        } else {
            $recentCart->delete();

            Cart::current()->customer(User::fromUser($event->user))->save();
        }
    }
}
