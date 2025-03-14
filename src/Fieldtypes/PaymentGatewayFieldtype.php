<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use DuncanMcClean\Cargo\Facades\PaymentGateway;
use Statamic\Fields\Fieldtype;

class PaymentGatewayFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function preProcessIndex($data)
    {
        return collect($data)->map(function ($item) {
            $paymentGateway = PaymentGateway::find($item);

            return $paymentGateway->title();
        })->implode(', ');
    }
}
