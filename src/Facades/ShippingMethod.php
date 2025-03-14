<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Shipping\Manager;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\Cargo\Shipping\Manager
 */
class ShippingMethod extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
