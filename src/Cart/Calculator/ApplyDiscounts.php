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
        if ($cart->get('discount_code') && ! Facades\Discount::findByDiscountCode($cart->get('discount_code'))) {
            $cart->remove('discount_code');
        }

        $discounts = Facades\Discount::query()
            ->whereNull('discount_code')
            ->when($cart->get('discount_code'), fn ($query) => $query->orWhere('discount_code', $cart->get('discount_code')))
            ->get()
            ->map(function (Discount $discount) use ($cart) {
                $lineItems = $cart->lineItems()
                    ->filter(fn (LineItem $lineItem) => $discount->discountType()->isValidForLineItem($cart, $lineItem))
                    ->map(function (LineItem $lineItem) use ($cart, $discount) {
                        $amount = $discount->discountType()->calculate($cart, $lineItem);
                        $originalDiscountTotal = $lineItem->discountTotal();

                        $lineItem->discountTotal($originalDiscountTotal + $amount);

                        if ($lineItem->discountTotal() > $lineItem->total()) {
                            $lineItem->discountTotal($lineItem->total());
                            $amount = $lineItem->total() - $originalDiscountTotal;
                        }

                        return $amount;
                    });

                if ($lineItems->isEmpty()) {
                    return;
                }

                return (array) DiscountCalculation::make(
                    discount: $discount->handle(),
                    description: $discount->get('discount_code') ?? $discount->title(),
                    amount: $lineItems->sum(),
                );
            })
            ->filter();

        if ($discounts->isEmpty()) {
            return $next($cart);
        }

        $cart->set('discount_breakdown', $discounts->all());
        $cart->discountTotal($discounts->sum('amount'));

        return $next($cart);
    }
}
