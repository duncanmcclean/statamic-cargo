<?php

namespace DuncanMcClean\Cargo\Orders\Eloquent;

use DuncanMcClean\Cargo\Contracts\Orders\Order;
use DuncanMcClean\Cargo\Contracts\Orders\OrderRepository as RepositoryContract;
use DuncanMcClean\Cargo\Contracts\Orders\QueryBuilder;
use DuncanMcClean\Cargo\Orders\LineItem;
use DuncanMcClean\Cargo\Stache;
use Illuminate\Support\Carbon;

class OrderRepository extends Stache\Repositories\OrderRepository implements RepositoryContract
{
    public function query(): QueryBuilder
    {
        return app(QueryBuilder::class, ['builder' => app('cargo.orders.eloquent.model')::query()]);
    }

    public function save(Order $order): void
    {
        $model = $order->toModel();

        if (! $order->date()) {
            $model->date = Carbon::now('UTC');
        }

        $model->save();

        $order->lineItems()->each(function (LineItem $lineItem) use ($model) {
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

        $model->lineItems()->whereNotIn('id', $order->lineItems()->map->id)->delete();

        $model = $model->fresh();

        $order->model($model);
        $order->date($model->date);
        $order->orderNumber($model->order_number);
    }

    public function delete(Order $order): void
    {
        $order->model()->delete();
    }

    public static function bindings(): array
    {
        return [
            Order::class => \DuncanMcClean\Cargo\Orders\Eloquent\Order::class,
            QueryBuilder::class => OrderQueryBuilder::class,
        ];
    }
}
