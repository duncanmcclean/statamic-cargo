<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Support\Arr;

class ShippingDetailsFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function preProcess($data)
    {
        $order = $this->field->parent();

        if (! $order->get('shipping_option')) {
            return ['has_shipping_option' => false];
        }

        if (! $order->shippingOption()) {
            return [
                'has_shipping_option' => true,
                'invalid' => true,
                'name' => Arr::get($order->get('shipping_option'), 'name'),
                'handle' => Arr::get($order->get('shipping_option'), 'handle'),
                'details' => [],
                'shipping_method' => [
                    'name' => $order->get('shipping_method'),
                    'handle' => $order->get('shipping_method'),
                    'logo' => null,
                ],
            ];
        }

        return [
            'has_shipping_option' => true,
            'name' => $order->shippingOption()->name(),
            'handle' => $order->shippingOption()->handle(),
            'details' => $order->shippingMethod()->fieldtypeDetails($order),
            'shipping_method' => [
                'name' => $order->shippingMethod()->title(),
                'handle' => $order->shippingMethod()->handle(),
                'logo' => $order->shippingMethod()->logo(),
            ],
        ];
    }

    public function process($data): null
    {
        return null;
    }
}
