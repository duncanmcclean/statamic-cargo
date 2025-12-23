<?php

namespace DuncanMcClean\Cargo\Products\Actions;

use DuncanMcClean\Cargo\Contracts\Products\Product;
use DuncanMcClean\Cargo\Orders\LineItem;
use DuncanMcClean\Cargo\Products\ProductVariant;
use Illuminate\Validation\ValidationException;

class ValidateStock
{
    public function handle(
        ?LineItem $lineItem = null,
        ?Product $product = null,
        ?ProductVariant $variant = null,
        ?int $quantity = null
    ): void {
        $product = $product ?? $lineItem?->product();
        $quantity = (int) ($quantity ?? $lineItem?->quantity() ?? 1);

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
            $variant = $variant ?? $lineItem?->variant();

            if ($variant->isStockEnabled() && $quantity > $variant->stock()) {
                throw ValidationException::withMessages([
                    'variant' => __('cargo::validation.variant_out_of_stock'),
                ]);
            }
        }
    }
}
