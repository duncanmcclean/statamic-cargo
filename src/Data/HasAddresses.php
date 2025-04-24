<?php

namespace DuncanMcClean\Cargo\Data;

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
        return new Address(
            name: $this->get('shipping_name'),
            line1: $this->get('shipping_line_1'),
            line2: $this->get('shipping_line_2'),
            city: $this->get('shipping_city'),
            postcode: $this->get('shipping_postcode'),
            country: $this->get('shipping_country'),
            state: $this->get('shipping_state'),
        );
    }

    public function billingAddress(): Address
    {
        return new Address(
            name: $this->get('billing_name'),
            line1: $this->get('billing_line_1'),
            line2: $this->get('billing_line_2'),
            city: $this->get('billing_city'),
            postcode: $this->get('billing_postcode'),
            country: $this->get('billing_country'),
            state: $this->get('billing_state'),
        );
    }

    public function hasShippingAddress(): bool
    {
        return ! empty($this->get('shipping_line_1'));
    }

    public function hasBillingAddress(): bool
    {
        return ! empty($this->get('billing_line_1'));
    }
}
