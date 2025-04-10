<?php

namespace DuncanMcClean\Cargo\Cart\Calculator;

use Closure;
use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Discounts\Discount;
use DuncanMcClean\Cargo\Discounts\DiscountType;
use DuncanMcClean\Cargo\Orders\LineItem;
use DuncanMcClean\Cargo\Facades;

class ApplyDiscounts
{
    public function handle(Cart $cart, Closure $next)
    {
        // todo: remove coupon code when the actual discount has been deleted

        $cart->lineItems()->each(function (LineItem $lineItem) use ($cart) {
            $eligibleDiscounts = Facades\Discount::query()
                ->whereNull('code')
                ->when($cart->get('discount_code'), fn ($query) => $query->orWhere('code', $cart->get('discount_code')))
                ->get()
                ->filter->isValid($cart, $lineItem);

            $discounts = $eligibleDiscounts->map(function (Discount $discount) use ($cart, $lineItem) {
                $amount = (int) $discount->amount();

                if ($discount->type() === DiscountType::Percentage) {
                    $amount = (int) ($amount * $lineItem->total()) / 100;
                    $lineItem->set('discount_amount', $amount);
                }

                if ($discount->type() === DiscountType::Fixed) {
                    $lineItem->set('discount_amount', $amount);
                }

                return [
                    'discount' => $discount->id(),
                    'type' => $discount->type()->value,
                    'amount' => $amount,
                ];
            });

            $lineItem->set('discounts', $discounts->all());
            $lineItem->discountTotal($discounts->sum('amount'));
        });

        $cart->discountTotal($cart->lineItems()->map->discountTotal()->sum());

        return $next($cart);
    }
}
