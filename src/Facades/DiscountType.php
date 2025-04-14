<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Discounts\Types\Manager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static \DuncanMcClean\Cargo\Discounts\Types\DiscountType find(string $handle)
 *
 * @see \DuncanMcClean\Cargo\Discounts\Types\Manager
 */
class DiscountType extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
