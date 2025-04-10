<?php

namespace DuncanMcClean\Cargo\Http\Controllers\Concerns;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Facades\Discount;
use DuncanMcClean\Cargo\Orders\LineItem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait RedeemsCoupons
{
    public function redeemCoupon(Request $request, Cart $cart): Cart
    {
        if ($request->has('coupon')) {
            if (empty($request->coupon)) {
                $cart->coupon(null);

                return $cart;
            }

            $coupon = Discount::findByCode($request->coupon);

            if (! $coupon) {
                throw ValidationException::withMessages([
                    'coupon' => __('cargo::validation.invalid_coupon_code'),
                ]);
            }

            $isValid = $cart->lineItems()->every(fn (LineItem $lineItem) => $coupon->isValid($cart, $lineItem));

            if (! $isValid) {
                throw ValidationException::withMessages([
                    'coupon' => __('cargo::validation.invalid_coupon_code'),
                ]);
            }

            $cart->coupon($coupon);
        }

        return $cart;
    }
}
