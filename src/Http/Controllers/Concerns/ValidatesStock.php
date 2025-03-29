<?php

namespace DuncanMcClean\Cargo\Http\Controllers\Concerns;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Facades\Product;
use DuncanMcClean\Cargo\Orders\LineItem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait ValidatesStock
{
    protected function validateStock(Request $request, Cart $cart, ?LineItem $lineItem = null): void
    {
        $product = Product::find($request->product ?? $lineItem->product);
        $quantity = (int) ($request->quantity ?? $lineItem?->quantity() ?? 1);

        if (
            $product->isStandardProduct()
            && $product->isStockEnabled()
            && $quantity > $product->stock()
        ) {
            throw ValidationException::withMessages([
                'product' => __('cargo::validation.product_out_of_stock'),
            ]);
        }

        if ($product->isVariantProduct()) {
            $variant = $product->variant($request->variant ?? $lineItem->variant);

            if ($variant->isStockEnabled() && $quantity > $variant->stock()) {
                throw ValidationException::withMessages([
                    'variant' => __('cargo::validation.variant_out_of_stock'),
                ]);
            }
        }
    }
}
