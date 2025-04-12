<?php

namespace DuncanMcClean\Cargo\Cart\Calculator;

use Closure;
use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Discounts\Discount;
use DuncanMcClean\Cargo\Discounts\DiscountCalculation;
use DuncanMcClean\Cargo\Facades;
use DuncanMcClean\Cargo\Orders\LineItem;

class ApplyDiscounts
{
    public function handle(Cart $cart, Closure $next)
    {
        if ($cart->has('discount_code') && ! Facades\Discount::findByDiscountCode($cart->get('discount_code'))) {
            $cart->remove('discount_code');
        }

        $cart->lineItems()->each(function (LineItem $lineItem) use ($cart) {
            $eligibleDiscounts = Facades\Discount::query()
                ->whereNull('discount_code')
                ->when($cart->get('discount_code'), fn ($query) => $query->orWhere('discount_code', $cart->get('discount_code')))
                ->get()
                ->filter(fn (Discount $discount) => $discount->discountType()->isValidForLineItem($cart, $lineItem));

            $discounts = $eligibleDiscounts->map(function (Discount $discount) use ($cart, $lineItem) {
                return (array) DiscountCalculation::make(
                    discount: $discount->id(),
                    description: $discount->get('discount_code') ?? $discount->name(),
                    amount: $discount->discountType()->calculate($cart, $lineItem),
                );
            });

            if ($discounts->isEmpty()) {
                return;
            }

            $lineItem->set('discounts', $discounts->all());

            $discountTotal = $discounts->sum('amount');

            if ($discountTotal > $lineItem->total()) {
                $discountTotal = $lineItem->total();
            }

            $lineItem->discountTotal($discountTotal);
        });

        $cart->discountTotal($cart->lineItems()->map->discountTotal()->sum());

        return $next($cart);
    }
}
