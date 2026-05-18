<?php

namespace DuncanMcClean\Cargo\Http\Controllers;

use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\ShippingMethod;
use DuncanMcClean\Cargo\Orders\LineItem;
use Illuminate\Validation\ValidationException;
use Statamic\Exceptions\NotFoundHttpException;

class CartShippingController
{
    public function __invoke()
    {
        throw_if(! Cart::hasCurrentCart(), NotFoundHttpException::class);

        $cart = Cart::current();

        if (! $cart->hasShippingAddress()) {
            throw ValidationException::withMessages([
                'shipping_address' => __('cargo::validation.shipping_address_missing'),
            ]);
        }

        if (! $this->hasPhysicalProducts($cart)) {
            throw ValidationException::withMessages([
                'line_items' => __('cargo::validation.no_physical_products'),
            ]);
        }

        return ShippingMethod::all()
            ->flatMap->options($cart)
            ->filter()
            ->map->toAugmentedArray()
            ->values()
            ->all();
    }

    private function hasPhysicalProducts($cart): bool
    {
        return $cart->lineItems()
            ->filter(fn (LineItem $lineItem) => $lineItem->product()->get('type', 'physical') === 'physical')
            ->isNotEmpty();
    }
}
