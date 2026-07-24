<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use DuncanMcClean\Cargo\Facades\PaymentGateway;
use DuncanMcClean\Cargo\Query\Scopes\Filters\Fields\PaymentGateways as PaymentGatewaysFilter;
use Statamic\Fields\Fieldtype;

class PaymentGateways extends Fieldtype
{
    protected $selectable = false;

    public function filter()
    {
        return new PaymentGatewaysFilter($this);
    }

    public function preProcessIndex($data)
    {
        return collect($data)->map(function ($item) {
            $paymentGateway = PaymentGateway::find($item);

            return $paymentGateway?->title();
        })->implode(', ');
    }
}
