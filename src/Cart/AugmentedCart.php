<?php

namespace DuncanMcClean\Cargo\Cart;

use DuncanMcClean\Cargo\Orders\LineItem;
use Statamic\Data\AbstractAugmented;

class AugmentedCart extends AbstractAugmented
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
                'order_number',
                'date',
                'status',
            ]))
            ->unique()->sort()->values()->all();
    }

    private function commonKeys(): array
    {
        return [
            'id',
            'is_free',
            'customer',
            'coupon',
            'shipping_method',
            'shipping_option',
            'payment_gateway',
            'tax_breakdown',
            'has_physical_products',
            'has_digital_products',
        ];
    }

    public function coupon()
    {
        if (! $this->data->coupon()) {
            return null;
        }

        return $this->data->coupon()->toShallowAugmentedArray();
    }

    public function shippingMethod()
    {
        if (! $this->data->shippingMethod()) {
            return null;
        }

        return [
            'name' => $this->data->shippingMethod()->title(),
            'handle' => $this->data->shippingMethod()->handle(),
        ];
    }

    public function shippingOption()
    {
        if (! $this->data->shippingOption()) {
            return null;
        }

        return $this->data->shippingOption()->toAugmentedArray();
    }

    public function paymentGateway()
    {
        if (! $this->data->paymentGateway()) {
            return null;
        }

        return [
            'title' => $this->data->paymentGateway()->title(),
            'handle' => $this->data->paymentGateway()->handle(),
        ];
    }

    public function hasPhysicalProducts(): bool
    {
        return $this->data->lineItems()
            ->filter(fn (LineItem $lineItem) => $lineItem->product()->get('type', 'physical') === 'physical')
            ->isNotEmpty();
    }

    public function hasDigitalProducts(): bool
    {
        return $this->data->lineItems()
            ->filter(fn (LineItem $lineItem) => $lineItem->product()->get('type', 'physical') === 'digital')
            ->isNotEmpty();
    }
}
