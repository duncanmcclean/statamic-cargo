<?php

namespace DuncanMcClean\Cargo\Cart;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Customers\GuestCustomer;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Contracts\Products\Product;
use Illuminate\Validation\ValidationException;

class HandlePrerequisiteProducts
{
    public static function handle(
        Cart $cart,
        Product $product
    ): void {
        if ($prerequisiteProduct = $product->prerequisite_product) {
            if (! $cart->customer() || $cart->customer() instanceof GuestCustomer) {
                throw ValidationException::withMessages([
                    'product' => __('cargo::validation.prerequisite_product_logged_out'),
                ]);
            }

            $hasPurchasedPrerequisiteProduct = collect($cart->customer()->getComputed('orders'))
                ->map(fn ($id) => Order::find($id))
                ->filter(function ($order) use ($prerequisiteProduct) {
                    return $order->lineItems()
                        ->where('product', $prerequisiteProduct->id())
                        ->count() > 0;
                })
                ->count() > 0;

            if (! $hasPurchasedPrerequisiteProduct) {
                throw ValidationException::withMessages([
                    'product' => __('cargo::validation.prerequisite_product', [
                        'product' => $prerequisiteProduct->get('title'),
                    ]),
                ]);
            }
        }
    }
}
