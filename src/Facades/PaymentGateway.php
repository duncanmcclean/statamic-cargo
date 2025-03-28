<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Payments\Gateways\Manager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static \DuncanMcClean\Cargo\Payments\Gateways\PaymentGateway find(string $handle)
 *
 * @see \DuncanMcClean\Cargo\Payments\Gateways\Manager
 */
class PaymentGateway extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
