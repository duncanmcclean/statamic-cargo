<?php

namespace DuncanMcClean\Cargo\Cart\Actions;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Products\Product;
use DuncanMcClean\Cargo\Orders\LineItem;
use DuncanMcClean\Cargo\Products\Actions\ValidateStock;
use DuncanMcClean\Cargo\Products\ProductVariant;
use Illuminate\Support\Collection;

class AddToCart
{
    public function handle(
        Cart $cart,
        Product $product,
        ?ProductVariant $variant = null,
        ?int $quantity = 1,
        ?Collection $data = null,
    ): void {
        if (! $data) {
            $data = collect();
        }

        $quantity = $quantity ?? 1;
        $existingLineItem = $this->findExistingLineItem($cart, $product, $variant, $data);

        if ($existingLineItem) {
            $quantity += (int) $existingLineItem->quantity();
        }

        app(ValidateStock::class)->handle(
            product: $product,
            variant: $variant,
            quantity: $quantity
        );

        app(PrerequisiteProductsCheck::class)->handle($cart, $product);

        if ($existingLineItem) {
            $cart->lineItems()->update(
                id: $existingLineItem->id(),
                data: $existingLineItem->data()->merge($data)->merge([
                    'quantity' => $quantity,
                ])->all()
            );
        } else {
            $cart->lineItems()->create($data->merge([
                'product' => $product->id(),
                'variant' => $variant?->key(),
                'quantity' => $quantity,
            ])->all());
        }
    }

    private function findExistingLineItem(Cart $cart, Product $product, ?ProductVariant $variant, Collection $data): ?LineItem
    {
        return $cart->lineItems()
            ->where('product', $product->id())
            ->when($variant, fn ($collection) => $collection->where('variant', $variant?->key()))
            ->when(config('statamic.cargo.carts.unique_metadata', false), function ($collection) use ($data) {
                return $collection->filter(function (LineItem $lineItem) use ($data) {
                    foreach ($data as $key => $value) {
                        if ($lineItem->get($key) !== $value) {
                            return false;
                        }
                    }

                    return true;
                });
            })
            ->first();
    }
}
