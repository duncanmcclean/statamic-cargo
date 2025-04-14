<?php

namespace DuncanMcClean\Cargo\Orders;

use DuncanMcClean\Cargo\Cart\AugmentedCart;

class AugmentedOrder extends AugmentedCart
{
    private $cachedKeys;

    public function keys(): array
    {
        if ($this->cachedKeys) {
            return $this->cachedKeys;
        }

        return $this->cachedKeys = $this->data->data()->keys()
            ->merge($this->data->supplements()->keys())
            ->merge($this->commonKeys())
            ->merge($this->blueprintFields()->keys())
            ->reject(fn ($key) => in_array($key, [
                'receipt',
                'shipping_details',
                'payment_details',
            ]))
            ->unique()->sort()->values()->all();
    }

    private function commonKeys(): array
    {
        return [
            'id',
            'order_number',
            'date',
            'status',
            'is_free',
            'customer',
            'discounts',
            'shipping_method',
            'shipping_option',
            'payment_gateway',
            'tax_breakdown',
            'has_physical_products',
            'has_digital_products',
        ];
    }

    public function status()
    {
        if (! $this->data instanceof Order) {
            return null;
        }

        return $this->data->status()->value;
    }
}
