<?php

namespace DuncanMcClean\Cargo\Discounts\Actions;

use DuncanMcClean\Cargo\Contracts\Orders\Order;
use DuncanMcClean\Cargo\Events\DiscountRedeemed;
use DuncanMcClean\Cargo\Facades\Discount;

class UpdateDiscounts
{
    public function handle(Order $order): void
    {
        collect($order->get('discount_breakdown'))->each(function ($discount) use ($order) {
            $discount = Discount::find($discount['discount']);
            $discount->set('redemptions_count', $discount->get('redemptions_count', 0) + 1)->saveQuietly();

            DiscountRedeemed::dispatch($discount, $order);
        });
    }
}