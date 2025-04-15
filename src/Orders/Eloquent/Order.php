<?php

namespace DuncanMcClean\Cargo\Orders\Eloquent;

use DuncanMcClean\Cargo\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\Cargo\Orders\Order as StacheOrder;
use Illuminate\Support\Str;

class Order extends StacheOrder
{
    protected $model;

    public static function fromModel(OrderModel $model): self
    {
        return (new static)
            ->model($model)
            ->id($model->id)
            ->site($model->site)
            ->orderNumber($model->order_number)
            ->date($model->date)
            ->cart($model->cart)
            ->status($model->status)
            ->customer(
                // Guest customers are stored as JSON strings, so we need to decode them.
                Str::contains($model->customer, '{"')
                    ? json_decode($model->customer, true)
                    : $model->customer
            )
            ->lineItems($model->lineItems->map(function (LineItemModel $model) {
                return collect($model->getAttributes())
                    ->except(['order_id', 'data'])
                    ->merge($model->data)
                    ->all();
            }))
            ->grandTotal($model->grand_total)
            ->subTotal($model->sub_total)
            ->discountTotal($model->discount_total)
            ->taxTotal($model->tax_total)
            ->shippingTotal($model->shipping_total)
            ->data([
                ...$model->data ?? [],
                'updated_at' => $model->updated_at,
            ]);
    }

    public static function makeModelFromContract(OrderContract $source): OrderModel
    {
        $class = app('cargo.orders.eloquent.model');

        $customer = match (true) {
            is_array($source->customer) => json_encode($source->customer),
            is_object($source->customer()) => $source->customer()->getKey(),
            default => $source->customer,
        };

        $attributes = [
            'site' => $source->site()->handle(),
            'date' => $source->date(),
            'cart' => $source->cart(),
            'status' => $source->status()->value,
            'customer' => $customer,
            'grand_total' => $source->grandTotal(),
            'sub_total' => $source->subTotal(),
            'discount_total' => $source->discountTotal(),
            'tax_total' => $source->taxTotal(),
            'shipping_total' => $source->shippingTotal(),
            'data' => $source->data()->except('updated_at')->all(),
            'updated_at' => $source->get('updated_at'),
        ];

        if ($id = $source->id()) {
            $attributes['id'] = $id;
        }

        if ($orderNumber = $source->orderNumber()) {
            $attributes['order_number'] = $orderNumber;
        }

        return $class::findOrNew($source->id())->fill($attributes);
    }

    public function toModel()
    {
        return self::makeModelFromContract($this);
    }

    public function model($model = null)
    {
        if (func_num_args() === 0) {
            return $this->model;
        }

        $this->model = $model;

        if (! is_null($model)) {
            $this->id($model->id);
        }

        return $this;
    }

    public function defaultAugmentedArrayKeys()
    {
        return [];
    }
}
