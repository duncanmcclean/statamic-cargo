<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use Statamic\Fields\Fieldtype;

class PaymentDetailsFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function preProcess($data)
    {
        $order = $this->field->parent();

        if (! $order->get('payment_gateway')) {
            return ['has_payment_gateway' => false];
        }

        if (! $order->paymentGateway()) {
            return [
                'has_payment_gateway' => true,
                'invalid' => true,
                'title' => $order->get('payment_gateway'),
                'handle' => $order->get('payment_gateway'),
                'logo' => null,
                'details' => [],
            ];
        }

        return [
            'has_payment_gateway' => true,
            'title' => $order->paymentGateway()->title(),
            'handle' => $order->paymentGateway()->handle(),
            'logo' => $order->paymentGateway()->logo(),
            'details' => $order->paymentGateway()->fieldtypeDetails($order),
        ];
    }

    public function process($data): null
    {
        return null;
    }
}
