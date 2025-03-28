<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Shipping\Manager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static \DuncanMcClean\Cargo\Shipping\ShippingMethod find(string $handle)
 *
 * @see \DuncanMcClean\Cargo\Shipping\Manager
 */
class ShippingMethod extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
