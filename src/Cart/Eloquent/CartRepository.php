<?php

namespace DuncanMcClean\Cargo\Cart\Eloquent;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Cart\CartRepository as RepositoryContract;
use DuncanMcClean\Cargo\Contracts\Cart\QueryBuilder;
use DuncanMcClean\Cargo\Orders\LineItem;
use DuncanMcClean\Cargo\Stache;

class CartRepository extends Stache\Repositories\CartRepository implements RepositoryContract
{
    public function query(): QueryBuilder
    {
        return app(QueryBuilder::class, ['builder' => app('cargo.carts.eloquent.model')::query()]);
    }

    public function save(Cart $cart): void
    {
        $model = $cart->toModel();
        $model->save();

        $cart->lineItems()->each(function (LineItem $lineItem) use ($model) {
            $model->lineItems()->updateOrCreate(
                ['id' => $lineItem->id],
                [
                    'product' => $lineItem->product,
                    'variant' => $lineItem->variant,
                    'quantity' => $lineItem->quantity,
                    'unit_price' => $lineItem->unitPrice ?? 0,
                    'sub_total' => $lineItem->subTotal ?? 0,
                    'tax_total' => $lineItem->taxTotal ?? 0,
                    'total' => $lineItem->total ?? 0,
                    'data' => $lineItem->data()->filter()->all(),
                ]
            );
        });

        $model->lineItems()->whereNotIn('id', $cart->lineItems()->map->id)->delete();

        $model = $model->fresh();

        $cart->model($model);

        $this->persistCart($cart);
    }

    public function delete(Cart $cart): void
    {
        $cart->model()->delete();
    }

    public static function bindings(): array
    {
        return [
            Cart::class => \DuncanMcClean\Cargo\Cart\Eloquent\Cart::class,
            QueryBuilder::class => CartQueryBuilder::class,
        ];
    }
}
