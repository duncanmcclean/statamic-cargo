<?php

namespace DuncanMcClean\Cargo\Http\Controllers\Concerns;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Customers\GuestCustomer;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Products\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait HandlePrerequisiteProducts
{
    protected function handlePrerequisiteProducts(Request $request, Cart $cart, Product $product): Cart
    {
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
                        'product' => $prerequisiteProduct->name(),
                    ]),
                ]);
            }
        }

        return $cart;
    }
}
