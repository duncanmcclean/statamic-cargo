<?php

namespace DuncanMcClean\Cargo\Products\Actions;

use DuncanMcClean\Cargo\Contracts\Orders\Order;
use DuncanMcClean\Cargo\Events\ProductNoStockRemaining;
use DuncanMcClean\Cargo\Events\ProductStockLow;
use DuncanMcClean\Cargo\Facades\Product;
use DuncanMcClean\Cargo\Orders\LineItem;
use Illuminate\Support\Arr;

class UpdateStock
{
    public function handle(Order $order): void
    {
        $order->lineItems()->each(function (LineItem $lineItem) {
            if ($lineItem->product()->isStandardProduct() && $lineItem->product()->isStockEnabled()) {
                $product = $lineItem->product();

                // When the Price field isn't localized, we need to update the stock on the origin entry.
                if ($product->hasOrigin() && ! $product->blueprint()->field('stock')?->isLocalizable()) {
                    $product = Product::find($product->origin()->id());
                }

                $product->set('stock', $product->stock() - $lineItem->quantity())->save();

                if ($product->stock() < config('statamic.cargo.products.low_stock_threshold')) {
                    ProductStockLow::dispatch($product);
                }

                if ($product->stock() === 0) {
                    ProductNoStockRemaining::dispatch($product);
                }
            }

            if ($lineItem->product()->isVariantProduct() && $lineItem->variant()->isStockEnabled()) {
                $product = $lineItem->product();

                // When the Product Variants field isn't localized, we need to update the stock on the origin entry.
                if ($product->hasOrigin() && ! $product->blueprint()->field('product_variants')?->isLocalizable()) {
                    $product = Product::find($product->origin()->id());
                }

                $productVariants = $product->productVariants();

                $productVariants['options'] = collect(Arr::get($productVariants, 'options'))->map(function ($variant) use ($lineItem) {
                    if (isset($variant['stock']) && $variant['key'] === $lineItem->variant()->key()) {
                        $variant['stock'] -= $lineItem->quantity();
                    }

                    return $variant;
                })->all();

                $product->set('product_variants', $productVariants)->save();

                if ($product->stock() < config('statamic.cargo.products.low_stock_threshold')) {
                    ProductStockLow::dispatch($product->variant($lineItem->variant()->key()));
                }

                if ($product->stock() === 0) {
                    ProductNoStockRemaining::dispatch($product->variant($lineItem->variant()->key()));
                }
            }
        });
    }
}