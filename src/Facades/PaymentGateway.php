<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Payments\Gateways\Manager;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\Cargo\Payments\Gateways\Manager
 */
class PaymentGateway extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
