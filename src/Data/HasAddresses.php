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
        return Address::make($this->get('shipping_address', []));
    }

    public function billingAddress(): Address
    {
        return Address::make($this->get('billing_address', []));
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
