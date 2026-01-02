<?php

namespace DuncanMcClean\Cargo\Shipping;

use DuncanMcClean\Cargo\Contracts\Purchasable;
use DuncanMcClean\Cargo\Contracts\Taxes\TaxClass;
use DuncanMcClean\Cargo\Facades;
use DuncanMcClean\Cargo\Support\Money;
use Illuminate\Support\Str;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Data\AugmentedData;
use Statamic\Data\HasAugmentedData;
use Statamic\Facades\Site;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class ShippingOption implements Augmentable, Purchasable
{
    use FluentlyGetsAndSets, HasAugmentedData;

    public $name;
    public $handle;
    public $price;
    public $acceptsPaymentOnDelivery = false;
    public $shippingMethod;

    public static function make(ShippingMethod $shippingMethod): self
    {
        return (new self)->shippingMethod($shippingMethod);
    }

    public function name($name = null)
    {
        return $this->fluentlyGetOrSet('name')
            ->setter(function ($name) {
                if (! $this->handle) {
                    $this->handle(Str::snake($name));
                }

                return $name;
            })
            ->args(func_get_args());
    }

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function price($price = null)
    {
        return $this->fluentlyGetOrSet('price')->args(func_get_args());
    }

    public function acceptsPaymentOnDelivery($acceptsPaymentOnDelivery = null)
    {
        return $this->fluentlyGetOrSet('acceptsPaymentOnDelivery')->args(func_get_args());
    }

    public function shippingMethod($shippingMethod = null)
    {
        return $this->fluentlyGetOrSet('shippingMethod')
            ->setter(function ($shippingMethod) {
                if ($shippingMethod instanceof ShippingMethod) {
                    return $shippingMethod->handle();
                }

                return $shippingMethod;
            })
            ->args(func_get_args());
    }

    public function purchasablePrice(): int
    {
        return $this->price;
    }

    public function purchasableTaxClass(): TaxClass
    {
        if (config('statamic.cargo.taxes.shipping_tax_behaviour') === 'highest_tax_rate') {
            throw new \Exception('Highest rate taxes for shipping not yet supported.');
        }

        if (! Facades\TaxClass::find('shipping')) {
            Facades\TaxClass::make()
                ->handle('shipping')
                ->set('title', __('Shipping'))
                ->save();
        }

        return Facades\TaxClass::find('shipping');
    }

    // We're overriding Statamic's AugmentedData class because it calls the price() method on
    // the ShippingOption class before attempting to get the raw value. In order for us to
    // format the price, we need to override the price() method on the AugmentedData class.
    public function newAugmentedInstance(): Augmented
    {
        return new class($this, $this->augmentedArrayData()) extends AugmentedData
        {
            public function price()
            {
                return Money::format($this->data->price(), Site::current());
            }
        };
    }

    public function augmentedArrayData(): array
    {
        return [
            'name' => $this->name(),
            'handle' => $this->handle(),
            'price' => $this->price(),
            'accepts_payment_on_delivery' => $this->acceptsPaymentOnDelivery(),
            'shipping_method' => $this->shippingMethod(),
        ];
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name(),
            'handle' => $this->handle(),
            'price' => $this->price(),
            'accepts_payment_on_delivery' => $this->acceptsPaymentOnDelivery(),
        ];
    }
}
