<?php

namespace DuncanMcClean\Cargo\Cart\Calculator;

use Closure;
use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Discounts\Discount;
use DuncanMcClean\Cargo\Discounts\DiscountType;
use DuncanMcClean\Cargo\Facades;
use DuncanMcClean\Cargo\Orders\LineItem;

class ApplyDiscounts
{
    public function handle(Cart $cart, Closure $next)
    {
        // todo: remove discount_code when the actual discount does not exist

        $cart->lineItems()->each(function (LineItem $lineItem) use ($cart) {
            $eligibleDiscounts = Facades\Discount::query()
                ->whereNull('discount_code')
                ->when($cart->get('discount_code'), fn ($query) => $query->orWhere('discount_code', $cart->get('discount_code')))
                ->get()
                ->filter->isValid($cart, $lineItem);

            $discounts = $eligibleDiscounts->map(function (Discount $discount) use ($lineItem) {
                // TODO: Extract this discount calculation logic
                $amount = (int) $discount->amount();

                if ($discount->type() === DiscountType::Percentage) {
                    $amount = (int) ($amount * $lineItem->total()) / 100;
                }

                return [
                    'discount' => $discount->id(),
                    'description' => $discount->get('discount_code') ?? $discount->name(),
                    'amount' => $amount,
                ];
            });

            $lineItem->set('discounts', $discounts->all());
            $lineItem->discountTotal($discounts->sum('amount')); // todo: ensure discount total can't be more than line item total

            if ($discounts->isEmpty()) {
                $lineItem->remove('discounts');
            }
        });

        $cart->discountTotal($cart->lineItems()->map->discountTotal()->sum());

        return $next($cart);
    }
}
