<?php

namespace DuncanMcClean\Cargo\Cart\Calculator;

use Closure;
use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Discounts\DiscountType;
use DuncanMcClean\Cargo\Orders\LineItem;

class ApplyDiscounts
{
    public function handle(Cart $cart, Closure $next)
    {
        if ($coupon = $cart->coupon()) {
            $cart->lineItems()->each(function (LineItem $lineItem) use ($cart, $coupon) {
                if (! $coupon->isValid($cart, $lineItem)) {
                    $lineItem->remove('discount_amount');

                    return;
                }

                $amount = (int) $coupon->amount();

                if ($coupon->type() === DiscountType::Percentage) {
                    $lineItem->set('discount_amount', (int) ($amount * $lineItem->total()) / 100);
                }

                if ($coupon->type() === DiscountType::Fixed) {
                    $lineItem->set('discount_amount', $amount);
                }
            });

            $cart->discountTotal($cart->lineItems()->sum('discount_amount'));

            if ($cart->discountTotal() === 0) {
                $cart->coupon(null);
            }
        }

        return $next($cart);
    }
}
