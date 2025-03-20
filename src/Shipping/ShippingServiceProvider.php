<?php

namespace DuncanMcClean\Cargo\Shipping;

use Statamic\Providers\AddonServiceProvider;

class ShippingServiceProvider extends AddonServiceProvider
{
    protected array $shippingMethods = [
        FreeShipping::class,
    ];

    public function bootAddon()
    {
        foreach ($this->shippingMethods as $shippingMethod) {
            $shippingMethod::register();
        }
    }
}