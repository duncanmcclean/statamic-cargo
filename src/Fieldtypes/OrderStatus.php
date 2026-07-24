<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use DuncanMcClean\Cargo\Orders\OrderStatus as OrderStatusEnum;
use Statamic\Fields\Fieldtype;

class OrderStatus extends Fieldtype
{
    protected $selectable = false;

    public function preProcessIndex($data)
    {
        if (! $data) {
            return null;
        }

        if ($data instanceof OrderStatusEnum) {
            $data = $data->value;
        }

        return $data;
    }

    public function preload()
    {
        return [
            'options' => collect(OrderStatusEnum::cases())
                ->when(! $this->field()->parent()?->get('shipping_method'), function ($collection) {
                    return $collection->reject(fn ($status) => in_array($status, [
                        OrderStatusEnum::Shipped,
                        OrderStatusEnum::Delivered,
                    ]));
                })
                ->map(fn ($status) => [
                    'value' => $status,
                    'label' => OrderStatusEnum::label($status),
                ])
                ->values(),
        ];
    }
}
