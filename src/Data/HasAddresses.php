<?php

namespace DuncanMcClean\Cargo\Data;

use Illuminate\Support\Arr;

trait HasAddresses
{
    public function taxableAddress(): ?Address
    {
        if ($this->hasShippingAddress()) {
            return $this->shippingAddress();
        }

        if ($this->hasBillingAddress()) {
            return $this->billingAddress();
        }

        return null;
    }

    public function shippingAddress(): Address
    {
        $address = $this->get('shipping_address', []);

        return new Address(
            name: Arr::get($address, 'name'),
            line1: Arr::get($address, 'line_1'),
            line2: Arr::get($address, 'line_2'),
            city: Arr::get($address, 'city'),
            postcode: Arr::get($address, 'postcode'),
            country: Arr::get($address, 'country'),
            state: Arr::get($address, 'state'),
        );
    }

    public function billingAddress(): Address
    {
        $address = $this->get('billing_address', []);

        return new Address(
            name: Arr::get($address, 'name'),
            line1: Arr::get($address, 'line_1'),
            line2: Arr::get($address, 'line_2'),
            city: Arr::get($address, 'city'),
            postcode: Arr::get($address, 'postcode'),
            country: Arr::get($address, 'country'),
            state: Arr::get($address, 'state'),
        );
    }

    public function hasShippingAddress(): bool
    {
        return ! empty($this->get('shipping_address'));
    }

    public function hasBillingAddress(): bool
    {
        return ! empty($this->get('billing_address'));
    }
}
